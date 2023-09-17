<?php
namespace ComplaintsBook\Public;

use ComplaintsBook\Includes\ComplaintsBook;
use ComplaintsBook\Includes\ComplaintsBookLoader;
use ComplaintsBook\Services\ComplaintsBookService;
use Dompdf\Dompdf;
use Dompdf\FontMetrics;
use Dompdf\Options;

class ComplaintsBookPublic
{
    private $complaintsBook;

    private $version;

    private $loader;

    protected $complaintBookService;

    public function __construct( ComplaintsBookLoader $loader, $complaintsBook, $version )
    {
        $this->loader = $loader;
        $this->complaintsBook = $complaintsBook;
        $this->version = $version;
        $this->complaintBookService = new ComplaintsBookService();
    }

    public function enqueue_styles()
    {
        $fileName = 'complaints-book.css';
        wp_enqueue_style( $this->complaintsBook, plugin_dir_url( COMPLAINTS_BOOK_FILE ) . 'public/assets/css/' . $fileName, array(), $this->version, 'all' );
    }

    /**
     * @return void
     */
    public function enqueue_scripts() :void
    {
        $fileName = 'complaints-book.js';
        wp_enqueue_script('jquery-validate', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js', array('jquery'));
        wp_enqueue_script( $this->complaintsBook, plugin_dir_url( COMPLAINTS_BOOK_FILE ) . 'public/assets/js/' . $fileName, array( 'jquery' ), $this->version, false );
        wp_localize_script( $this->complaintsBook, 'cbData', $this->getData());
    }

    /**
     * @return string
     */
    public function set_content_type() :string
    {
        return "text/html";
    }

    /**
     * @return array
     */
    private function getData() :array
    {
        return [
            'url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'complaints-book-ajax'),
        ];
    }

    public function getDomain()
    {
        return $this->complaintsBook;
    }

    /**
     * @return array
     */
    public function getCorporateNames() :array
    {
        $corporateNames = [];
        $args = [
            'post_type' => ComplaintsBook::$postTypeCorporateName,
            'orderby' => 'title',
            'nopaging' => true
        ];

        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $post = $query->post;
                $id = $post->ID;

                $corporateNames[] = (object)[
                    'id' => $id,
                    'name' => $post->post_title,
                    'ruc' => get_field('cn_ruc', $id),
                    'address' => get_field('cn_address', $id),
                    'correlative' => get_field('cn_correlative', $id),
                    'business_name' => get_field('cn_business_name', $id),
                ];
            }
        }

        wp_reset_postdata();

        return $corporateNames;
    }

    /**
     * @return void
     */
    public function registerComplaintsBook() :void
    {
        global $wpdb;

        try {
            $wpdb->query('START TRANSACTION');
            $data = $_POST;

            $validate = $this->complaintBookService->validateData($data);

            if (is_array($validate)) {
                wp_send_json_error($validate, 422);
            }

            $pdf = $this->complaintBookService->register($data);

            $url = $this->complaintBookService->getPdfUrl($pdf);

            $wpdb->query('COMMIT');

            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=$pdf");
            wp_send_json_success([
                "url" => $url,
                "filename" => $pdf
            ]);

        } catch (\Exception $e) {
            error_log($e->getMessage(), 'error');
            wp_send_json_error([
                'error' => $e->getCode(),
                'message' => $e->getMessage()
            ]);

            $wpdb->query('ROLLBACK');
        }
    }

    public function generatePDF()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);

        $html = '<html lang="ES-pe">
<head>
    <title>Ejemplo de PDF</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200;0,6..12,300;0,6..12,400;0,6..12,500;0,6..12,600;0,6..12,700;0,6..12,800;0,6..12,900;0,6..12,1000;1,6..12,200;1,6..12,300;1,6..12,400;1,6..12,500;1,6..12,600;1,6..12,700;1,6..12,800;1,6..12,900;1,6..12,1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="Public/assets/css/complaints-book.css">
    <style>
        *,
        *:before {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: "Nunito Sans", sans-serif;
            color: #8F9BB3;
            font-size: 12px;
        }
        ul {
            list-style: none;
        }
        .container {
            margin: 15px;
        }
        .bordered-table {
            border-top: 1px solid rgba(0, 0, 0, .1);
            border-right: 1px solid rgba(0, 0, 0, .1);
            border-left: 1px solid rgba(0, 0, 0, .1);
        }
        .bordered {
            border: 1px solid rgba(0, 0, 0, .1)
        }
        .bordered-top {
            border-top: 1px solid rgba(0, 0, 0, .1)
        }
        .bordered-bottom {
            border-bottom: 1px solid rgba(0, 0, 0, .1)
        }
        .bordered-right {
            border-right: 1px solid rgba(0, 0, 0, .1)
        }
        .bordered-sides {
            border-right: 1px solid rgba(0, 0, 0, .1);
            border-left: 1px solid rgba(0, 0, 0, .1);
        }
        .table {
            border-collapse: collapse;
            width: 100%;
        }
        .title {
            background-color: #F8F8F8;
            color: #768197;
            font-weight: 700;
        }
        .title-sec {
            background-color: #0399DE;
            color: #FFF;
        }
        .text-center {
            text-align: center;
        }
        .p-td {
            padding: 10px;
        }
        .disclaimer {
            font-size: 10px;
        }
        .min-height {
            min-height: 180px;
        }
        .bold {
            font-weight: 700;
        }
    </style>
</head>
<body style="margin: 0; padding: 0;">
    <div class="container">
        <table class="table">
            <tr>
                <td colspan="4" class="title text-center p-td bordered" style="width: 50%;">LIBRO DE RECLAMACIONES</td>
                <td colspan="4" rowspan="2" class="title-sec text-center p-td bold" style="width: 50%;">
                    HOJA DE RECLAMACIÓN
                    <br>
                    {claim_number}
                </td>
            </tr>
            <tr>
                <td class="p-td bordered" style="font-weight: 700;">Fecha:</td>
                <td class="p-td bordered text-center">{claim_day}</td>
                <td class="p-td bordered text-center">{claim_month}</td>
                <td class="p-td bordered text-center">{claim_year}</td>
            </tr>
            <tr>
                <td class="p-td bordered" colspan="8">
                    [NOMBRE DE LA PERSONA NATURAL O RAZÓN SOCIAL DE LA PERSONA JURÍDICA / RUC DEL PROVEEDOR]
                    <br>
                    [DOMICILIO DEL ESTABLECIMIENTO DONDE SE COLOCA EL LIBRO DE RECLAMACIONES / CÓDIGO DE IDENTIFICACIÓN] 
                </td>
            </tr>
        </table>
        <table class="table">
            <tr>
                <td class="title p-td bordered-sides" colspan="2">1. IDENTIFICACIÓN DEL CONSUMIDOR RECLAMANTE</td>
            </tr>
            <tr>
                <td class="p-td bordered" style="width: 100%;" colspan="2">NOMBRE: {consumer_name}</td>
            </tr>
            <tr>
                <td class="p-td bordered" style="width: 100%;" colspan="2">DOMICILIO: {consumer_address}</td>
            </tr>
            <tr>
                <td class="p-td bordered">DNI / CE: {consumer_number_document}</td>
                <td class="p-td bordered" style="width: 70%;">TELÉFONO / E-MAIL: {consumer_phone_email}</td>
            </tr>
            <tr>
                <td class="p-td bordered" style="width: 100%;" colspan="2">
                    PADRE O MADRE: [OPCIONAL] {consumer_tutor}
                </td>
            </tr>
        </table>
        <table class="table">
            <tr>
                <td class="title p-td bordered-sides" colspan="4">2. IDENTIFICACIÓN DEL BIEN CONTRATADO</td>
            </tr>
            <tr>
                <td class="p-td bordered" style="width: 9%;">PRODUCTO:</td>
                <td class="p-td bordered" style="width: 5%;">{well_hired_product}</td>
                <td class="p-td bordered-top" style="width: 20%;">
                    <strong>MONTO RECLAMADO:</strong>
                </td>
                <td class="p-td bordered-top bordered-right" style="width: 64%;">{well_hired_amount}</td>
            </tr>
            <tr>
                <td class="p-td bordered-sides" style="width: 9%;">SERVICIO:</td>
                <td class="p-td bordered-sides" style="width: 5%;">{well_hired_service}</td>
                <td class="p-td" style="width: 20%;">
                    <strong>DESCRIPCIÓN:</strong>
                </td>
                <td class="p-td bordered-right" style="width: 64%;">{well_hired_description}</td>
            </tr>
        </table>
        <table class="table">
            <tr>
                <td class="title p-td bordered-sides bordered-top bordered-bottom">3. DETALLE DE LA RECLAMACIÓN Y PEDIDO DEL CONSUMIDOR</td>
                <td class="p-td bordered-sides bordered-top bordered-bottom" style="width: 70px;">RECLAMO<sup>1</sup></td>
                <td class="p-td bordered-sides bordered-top bordered-bottom text-center" style="width: 20px;">{claim_details_claim}</td>
                <td class="p-td bordered-sides bordered-top bordered-bottom" style="width: 50px;">QUEJA<sup>2</sup></td>
                <td class="p-td bordered-sides bordered-top bordered-bottom text-center" style="width: 20px;">{claim_details_complaint}</td>
            </tr>
            <tr>
                <td class="p-td bordered-sides" colspan="5">DETALLE: {claim_detail}</td>
            </tr>
            <tr>
                <td class="p-td bordered-sides bordered-bottom" colspan="5">PEDIDO: {claim_details_request}</td>
            </tr>
        </table>
        <table class="table">
            <tr>
                <td class="title p-td bordered-sides bordered-bottom" colspan="4">4. OBSERVACIONES Y ACCIONES ADOPTADAS POR EL PROVEEDOR</td>
            </tr>
            <tr>
                <td class="p-td bordered-sides bordered-bottom">FECHA DE COMUNICACIÓN DE LA RESPUESTA:</td>
                <td class="p-td bordered-sides bordered-bottom">[DÍA]</td>
                <td class="p-td bordered-sides bordered-bottom">[MES]</td>
                <td class="p-td bordered-sides bordered-bottom">[AÑO]</td>
            </tr>
            <tr>
                <td class="p-td bordered-sides" colspan="4"></td>
            </tr>
            <tr>
                <td class="p-td bordered-sides" colspan="4"></td>
            </tr>
            <tr>
                <td class="p-td bordered-sides" colspan="4"></td>
            </tr>
            <tr>
                <td class="p-td disclaimer" colspan="4" style="background-color: #0399DE; color: #FFF;">
                    <ul style="list-style: none;">
                        <li><sup>1</sup>RECLAMO: Disconformidad relacionada a los productos o servicios.</li>
                        <li><sup>2</sup>QUEJA: Disconformidad no relacionada a los productos o servicios; o, malestar o descontento respecto a la atención al público.</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="p-td text-center bordered-sides bordered-bottom" colspan="4">
                    Destinatario( consumidor, proveedor o INDECOPI según corresponda) 
                </td>
            </tr>
        </table>
        <table class="table disclaimer">
            <tr>
                <td class="p-td">
                    <ul>
                        <li>*La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia ante la INDECOPI.</li>
                        <li>*El proveedor deberá dar respuesta al reclamo en un plazo no mayor a quince (15) días calendario. </li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>';
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('documento.pdf', array('Attachment' => 0));
    }

}