<?php
namespace ComplaintsBook\Services;

use ComplaintsBook\Includes\ComplaintsBook;
use Dompdf\Dompdf;
use Dompdf\Options;

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

                $name = $fields['name'] . ' ' . $fields['lastname'];
                $template = $form->prop( 'mail' );

                $template = str_replace('[your-subject]', $fields['cb_code'], $template );
                $template = str_replace('[your-name]', $name, $template );
                $template = str_replace( '[your-email]', $fields['cb_email'], $template );

                $headers = [];
                $headers[] = 'Cc: ' . $businessName->email;

                $attachment = [];
                if ($filename !== '') {
                    $attachment = [$filename];
                }

                wp_mail( $template['recipient'], $template['subject'], $template['body'], $headers, $attachment );
            }
        }
    }
}