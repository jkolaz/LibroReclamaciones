<?php
namespace ComplaintsBook\Services;

use ComplaintsBook\Includes\ComplaintsBook;
use Dompdf\Dompdf;
use Dompdf\Options;
use function EasyWPSMTP\Vendor\GuzzleHttp\Psr7\str;

class ComplaintsBookService
{
    const SEPARATOR_CORRELATIVE = ':::';

    const REQUIRED_DATA = [
        'cbo-business-name',
        'txt-name',
        'txt-lastname',
        'txt-phone',
        'txt-document-number',
        'txt-address',
        'txt-email',
        'cbo-type-service',
        'txt-amount',
        'txt-description',
        'cbo-type-claim',
        'txt-detail',
        'txt-request',
        'rd-privacy-policy'
    ];

    const REQUIRED_DATA_MSG = [
        'cbo-business-name' => 'Debe seleccionar una Razón Social.',
        'txt-name' => 'El campo nombre es obligatorio.',
        'txt-lastname' => 'El campo apellido es obligatorio.',
        'txt-phone' => 'El campo celular es obligatorio.',
        'txt-document-number' => 'El campo DNI/CE es obligatorio.',
        'txt-address' => 'El campo dirección es obligatorio.',
        'txt-email' => 'EL campo email es obligatorio.',
        'cbo-type-service' => 'Debe seleccionar el tipo de Servicio.',
        'txt-amount' => 'El campo monto es obligatorio.',
        'txt-description' => 'El campo descripción es obligatorio.',
        'cbo-type-claim' => 'Debe seleccionar el tipo de reclamo.',
        'txt-detail' => 'El campo detalle es obligatorio.',
        'txt-request' => 'El campo petición es obligatorio.',
        'rd-privacy-policy' => 'Debe de aceptar las politicas de privicidad.'
    ];

    const VARIABLES = [
        'claim_number'              => 'column=>fields.cb_code',
        'claim_day'                 => 'function=>getDate|fields.cb_date,day',
        'claim_month'               => 'function=>getDate|fields.cb_date,month',
        'claim_year'                => 'function=>getDate|fields.cb_date,year',
        'consumer_name'             => 'function=>getName|fields.cb_name,fields.cb_lastname',
        'consumer_address'          => 'column=>fields.cb_address',
        'consumer_number_document'  => 'column=>fields.cb_document_nmber',
        'consumer_phone_email'      => 'function=>getContact|fields.cb_phone,fields.cb_email',
        'consumer_tutor'            => 'column=>fields.cb_tutor',
        'well_hired_product'        => 'function=>getWillHired|fields.cb_goods_or_services,product',
        'well_hired_service'        => 'function=>getWillHired|fields.cb_goods_or_services,service',
        'well_hired_amount'         => 'column=>fields.cb_amount',
        'well_hired_description'    => 'column=>fields.cb_description',
        'claim_details_claim'       => 'function=>getComplaint|fields.cb_type,claim',
        'claim_details_complaint'   => 'function=>getComplaint|fields.cb_type,complaint',
        'claim_detail'              => 'column=>fields.cb_detail',
        'claim_details_request'     => 'column=>fields.cb_request',
    ];

    /**
     * @param array $data
     * @return array|true
     */
    public function validateData(array $data) :array|bool
    {
        $validate = true;
        foreach ($data as $key => $value) {
            if (in_array($key, self::REQUIRED_DATA) && empty($value)) {
                if (is_bool($validate)) {
                    $validate = [];
                }

                $validate[$key] = self::REQUIRED_DATA_MSG[$key];
            }
        }

        return $validate;
    }

    /**
     * @param array $data
     * @return string|null
     */
    public function register(array $data) :string|null
    {
        $file = null;
        $data = $this->prepareData($data);

        if(!empty($data)) {
            $idPost = $this->registerPost($data['post']);
            $this->registerAcf($idPost, $data['fields']);

            $businessName = $data['business_name'];
            $this->updateCorrelative($businessName->id, $businessName->correlative);

            $file = $this->generatePDF($idPost, $data);
            $this->sendMail($idPost, $data, $file);
        }

        return $file;
    }

    /**
     * @param $pdf
     * @return string
     */
    public function getPdfUrl($pdf) :string
    {
        $upload_dir = wp_upload_dir();

        return $upload_dir['url'] . "/$pdf";
    }

    /**
     * @param int $id
     * @param string $currentCode
     * @return void
     */
    private function updateCorrelative(int $id, string $currentCode) :void
    {
        $arrCode = explode(self::SEPARATOR_CORRELATIVE, $currentCode);
        $number = $arrCode[1] ?? 0;
        $number = (int)$number;

        update_field('cn_number', ($number + 1), $id);
    }

    /**
     * @param array $data
     * @return int
     */
    private function registerPost(array $data) :int
    {
        $insertId = wp_insert_post($data);
        return is_int($insertId) ? $insertId : 0;
    }

    /**
     * @param int $idPost
     * @param array $data
     * @return void
     */
    public function registerAcf(int $idPost, array $data) :void
    {
        if ($idPost > 0) {
            foreach ($data as $key => $item) {
                update_field($key, $item, $idPost);
            }
        }
    }

    /**
     * @param array $data
     * @return array|null
     */
    private function prepareData(array $data) :array|null
    {
        $dataPost = null;

        $businessNameId = $data['cbo-business-name'];
        $name = strtoupper($data['txt-name']);
        $lastname = strtoupper($data['txt-lastname']);
        $phone = $data['txt-phone'];
        $documentNumber = $data['txt-document-number'];
        $address = $data['txt-address'];
        $email = $data['txt-email'];
        $tutor = $data['txt-tutor'];
        $typeService = $data['cbo-type-service'];
        $amount = $data['txt-amount'];
        $description =$data['txt-description'];
        $typeClaim = $data['cbo-type-claim'];
        $detail = $data['txt-detail'];
        $request = $data['txt-request'];
        $privacyPolicy = $data['rd-privacy-policy'];

        if ($privacyPolicy === 'on') {
            $businessName = $this->getCorporateName($businessNameId);
            $code = $this->getCode($businessName);

            $titlePost = $this->getTitlePost($code, $typeClaim, $documentNumber, $name, $lastname);

            $dataPost = [
                'post' => [
                    'post_type' => ComplaintsBook::$postTypeComplaintsBook,
                    'post_title' => $titlePost,
                    'post_status' => 'publish'
                ],
                'fields' => [
                    'cb_code' => $code,
                    'cb_business' => $businessNameId,
                    'cb_date' => date('Y-m-d'),
                    'cb_name' => $name,
                    'cb_lastname' => $lastname,
                    'cb_phone' => $phone,
                    'cb_document_nmber' => $documentNumber,
                    'cb_address' => $address,
                    'cb_email' => $email,
                    'cb_tutor' => $tutor,
                    'cb_goods_or_services' => $typeService,
                    'cb_amount' => $amount,
                    'cb_description' => $description,
                    'cb_type' => $typeClaim,
                    'cb_detail' => $detail,
                    'cb_request' => $request,
                ],
                'business_name' => $businessName
            ];
        }

        return $dataPost;
    }

    /**
     * @param string $code
     * @param string $typeClaim
     * @param string $documentNumber
     * @param string $name
     * @param string $lastname
     * @return string
     */
    private function getTitlePost(string $code, string $typeClaim, string $documentNumber, string $name, string $lastname) :string
    {
        $title = $code . ': ';
        $title .= strtoupper($typeClaim === 'claim' ? 'Queja' : 'Reclamo') . ' - ';
        $title .= $documentNumber . ' ' . $name . ' ' . $lastname;

        return $title;
    }

    /**
     * @param object $data
     * @return string
     */
    private function getCode(object $data) :string
    {
        return !empty($data->correlative) ? str_replace(self::SEPARATOR_CORRELATIVE, '', $data->correlative) : 1;
    }

    /**
     * @param int $id
     * @return object|null
     */
    private function getCorporateName(int $id) :object|null
    {
        $data = null;
        $post = get_post($id);

        if(!empty($post)) {
            $id = $post->ID;

            $number = get_field('cn_number', $id);
            $correlative = get_field('cn_correlative', $id);

            if(!empty($number)) {
                $correlative .= self::SEPARATOR_CORRELATIVE . $number;
            } else {
                $correlative .= self::SEPARATOR_CORRELATIVE . 1;
            }

            $data = (object)[
                'id' => $id,
                'name' => $post->post_title,
                'ruc' => get_field('cn_ruc', $id),
                'email' => get_field('cn_email', $id),
                'correlative' => $correlative,
                'address' => get_field('cn_address', $id),
                'template_id' => get_field('cn_template', $id),
                'template_mail' => get_field('cn_template_mail', $id),
            ];
        }

        return $data;
    }

    /**
     * @param int $postId
     * @param array $data
     * @return string|null
     */
    private function generatePDF(int $postId, array $data) :string|null
    {
        $businessName = $data['business_name'];
        $templateId = $businessName->template_id;

        $name = null;

        if (!empty($templateId)) {
            $title = $data['post']['post_title'];
            $template = get_field('template_html', $templateId);
            $template = $this->replaceVariables($template, $data);

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($template);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $pdfContent = $dompdf->output();

            $upload_dir     = wp_upload_dir();
            $upload_path    = realpath($upload_dir['path']);
            $fileName       = sanitize_title($title) . '.pdf';
            $filePath       = $upload_path . DIRECTORY_SEPARATOR . $fileName;
            $name           = $fileName;

            file_put_contents($filePath, $pdfContent);

            $attachmentId = $this->uploadFile($postId, $title, $filePath);
            update_field('cb_pdf', $attachmentId, $postId);
        }

        return $name;
    }

    /**
     * @param int $parentPostId
     * @param string $title
     * @param string $filename
     * @return int
     */
    private function uploadFile(int $parentPostId, string $title, string $filename) :int
    {
        $filetype = wp_check_filetype( basename( $filename ), null );
        $wp_upload_dir = wp_upload_dir();

        $attachment = array(
            'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
            'post_mime_type' => $filetype['type'],
            'post_title'     => $title,
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        $urlFile = $wp_upload_dir['subdir'] . '/' . basename( $filename );
        $attachId = wp_insert_attachment( $attachment, substr($urlFile, 1, strlen($urlFile)), $parentPostId );

        return !empty($attachId) ? $attachId : 0;
    }

    /**
     * @param int $postId
     * @param array $data
     * @param string $filename
     * @return void
     */
    private function sendMail(int $postId, array $data, string $filename) :void
    {
        if ($postId > 0) {
            $fields = $data['fields'];
            $businessName = $data['business_name'];
            $templateMailId = !empty($businessName->template_mail) ? $businessName->template_mail : 0;

            $form = wpcf7_contact_form($templateMailId);

            if ( !empty($form) ) {
                $name = $fields['cb_name'] . ' ' . $fields['cb_lastname'];
                $template = $form->prop( 'mail' );

                $template = str_replace('[your-subject]', $fields['cb_code'], $template );
                $template = str_replace('[your-name]', $name, $template );
                $template = str_replace( '[your-email]', $fields['cb_email'], $template );

                $headers = [];
                $headers[] = 'Cc: ' . $businessName->email;

                $attachment = [];
                if ($filename !== '') {
                    $wp_upload_dir = wp_upload_dir();
                    $filename = $wp_upload_dir['path'] . DIRECTORY_SEPARATOR . $filename;
                    $attachment = [$filename];
                }

                wp_mail( $template['recipient'], $template['subject'], $template['body'], $headers, $attachment );
            }
        }
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    private function replaceVariables(string $template, array $data) :string
    {
        $variables = self::VARIABLES;

        foreach ($variables as $key => $variable) {
            $template = str_replace('{' . $key . '}', $this->replaceVariable($variable, $data), $template);
        }

        return $template;
    }

    /**
     * @param string $replace
     * @param array $data
     * @return string
     */
    private function replaceVariable(string $replace, array $data) :string
    {
        $textReplace = '';

        $isColumn = strpos($replace, 'column=>');
        if (!is_bool($isColumn)) {
            $field = str_replace('column=>', '', $replace);
            $textReplace = $this->replaceColumn($field, $data);
        }

        $isFunction = strpos($replace, 'function=>');
        if (!is_bool($isFunction)) {
            $dataFunction = str_replace('function=>', '', $replace);
            $textReplace = $this->replaceFunction($dataFunction, $data);
        }

        return $textReplace;
    }

    /**
     * @param string $field
     * @param $data
     * @return string
     */
    private function replaceColumn(string $field, $data) :string
    {
        $search = explode('.', $field);
        $textReplace = $data[$search[0]][$search[1]];

        return !empty($textReplace) ? nl2br($textReplace) : '';
    }

    /**
     * @param string $dataFunction
     * @param $data
     * @return string
     */
    private function replaceFunction(string $dataFunction, $data) :string
    {
        $textReplace = '';
        $arrDataFunction = explode('|', $dataFunction);
        if (count($arrDataFunction) > 1) {
            $function = $arrDataFunction[0];
            $params = explode(',', $arrDataFunction[1]);
            $params = $this->getParams($params, $data);

            switch ($function) {
                case 'getDate':
                    $textReplace = $this->getDate($params[0], $params[1]);
                    break;
                case 'getName':
                    $textReplace = $this->getName($params[0], $params[1]);
                    break;
                case 'getContact':
                    $textReplace = $this->getContact($params[0], $params[1]);
                    break;
                case 'getWillHired':
                    $textReplace = $this->getWillHired($params[0], $params[1]);
                    break;
                case 'getComplaint':
                    $textReplace = $this->getComplaint($params[0], $params[1]);
                    break;
            }
        }

        return $textReplace;
    }

    /**
     * @param array $params
     * @param array $data
     * @return array
     */
    private function getParams(array $params, array $data) :array
    {
        foreach ($params as $key => $param) {
            $isField = strpos($param, 'fields.');

            if(!is_bool($isField)) {
                $search = explode('.', $param);
                $textReplace = $data[$search[0]][$search[1]];
                $value = !empty($textReplace) ? nl2br($textReplace) : '';
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * @param string $date
     * @param string $format
     * @return string
     */
    private function getDate(string $date, string $format) :string
    {
        return match ($format) {
            'day' => '[' . date('d', strtotime($date)) . ']',
            'month' => '[' . date('m', strtotime($date)) . ']',
            'year' => '[' . date('Y', strtotime($date)) . ']',
            default => '',
        };
    }

    /**
     * @param string $name
     * @param string $lastname
     * @return string
     */
    private function getName(string $name, string $lastname) :string
    {
        $name = trim($name);
        $lastname = trim($lastname);

        return $name . ($lastname !== '' ? ' ' . $lastname : '');
    }

    /**
     * @param string $phone
     * @param string $email
     * @return string
     */
    private function getContact(string $phone, string $email) :string
    {
        $textReplace = [];

        if ($phone !== '') {
            $textReplace[] = $phone;
        }

        if ($email !== '') {
            $textReplace[] = $email;
        }

        return implode(' / ', $textReplace);
    }

    /**
     * @param string $field
     * @param string $type
     * @return string
     */
    private function getWillHired(string $field, string $type) :string
    {
        $textReplace = '';

        switch ($type) {
            case 'product':
                $textReplace = $field === 'good' ? '<b>X</b>' : '';
                break;
            case 'service':
                $textReplace = $field === 'service' ? '<b>X</b>' : '';
                break;
        }

        return $textReplace;
    }

    /**
     * @param string $field
     * @param string $type
     * @return string
     */
    private function getComplaint(string $field, string $type) :string
    {
        $textReplace = '';
        switch ($type) {
            case 'claim':
                $textReplace = $field === 'claim' ? '<b>X</b>' : '';
                break;
            case 'complaint':
                $textReplace = $field === 'complaint' ? '<b>X</b>' : '';
                break;
        }

        return $textReplace;
    }
}