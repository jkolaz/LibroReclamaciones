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
        try {
            $data = $_POST;

            $validate = $this->complaintBookService->validateData($data);

            if (is_array($validate)) {
                wp_send_json_error($validate, 422);
            }

            $pdf = $this->complaintBookService->register($data);

            $url = $this->complaintBookService->getPdfUrl($pdf);

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
        }
    }

    public function generatePDF()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);

        $html = '<html>
<head>
    <title>Ejemplo de PDF</title>
    <link rel="stylesheet" type="text/css" href="Public/assets/css/complaints-book.css">
    <style>
        *,
        *:before {
            margin: 0;
            padding: 0;
        }
        ul {
            list-style: none;
        }
        .container {
            margin: 15px;
        }
        .bordered {
            border: 1px solid rgba(0, 0, 0, .1)
        }
        .table {
            border-collapse: collapse;
            width: 100%;
        }
        .title {
            background-color: #F8F8F8;
            color: #768197;
        }
        .title-sec {
            background-color: #0399DE;
            color: #FFF;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body style="margin: 0; padding: 0;">
    <div class="container">
        <table class="table bordered">
            <tr>
                <td colspan="4" class="title" style="width: 50%;">LIBRO DE RECLAMACIONES</td>
                <td colspan="4" rowspan="2" class="title-sec text-center" style="width: 50%;">
                    HOJA DE RECLAMACIÓN
                    <br>
                    [N° 0000000001-201X]
                </td>
            </tr>
            <tr>
                <td style="color: #8F9BB3; font-weight: 700; border: 1px solid rgba(0, 0, 0, .1);">Fecha:</td>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1);">[DÍA]</td>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1);">[MES]</td>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1);">[AÑO]</td>
            </tr>
            <tr>
                <td colspan="8" style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1);">
                    [NOMBRE DE LA PERSONA NATURAL O RAZÓN SOCIAL DE LA PERSONA JURÍDICA / RUC DEL PROVEEDOR]
                    <br>
                    [DOMICILIO DEL ESTABLECIMIENTO DONDE SE COLOCA EL LIBRO DE RECLAMACIONES / CÓDIGO DE IDENTIFICACIÓN] 
                </td>
            </tr>
        </table>
        <table class="table bordered">
            <tr>
                <td style="color: #768197; background-color: #F8F8F8; font-weight: 700;" colspan="2">1. IDENTIFICACIÓN DEL CONSUMIDOR RECLAMANTE</td>
            </tr>
            <tr>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1); width: 100%;" colspan="2">NOMBRE:</td>
            </tr>
            <tr>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1); width: 100%;" colspan="2">DOMICILIO:</td>
            </tr>
            <tr>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1); width: 30%;">DNI / CE:</td>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1); width: 70%;">TELÉFONO / E-MAIL:</td>
            </tr>
            <tr>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1); width: 100%;" colspan="2">
                    PADRE O MADRE: [OPCIONAL]
                </td>
            </tr>
        </table>
        <table class="table bordered">
            <tr>
                <td style="color: #768197; background-color: #F8F8F8; font-weight: 700;">2. IDENTIFICACIÓN DEL BIEN CONTRATADO</td>
            </tr>
            <tr>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1); width: 100%;">PRODUCTO:</td>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1); width: 100%;"></td>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1); width: 100%;">MONTO RECLAMADO:</td>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1); width: 100%;"></td>
            </tr>
            <tr>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1); width: 100%;">SERVICIO:</td>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1); width: 100%;"></td>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1); width: 100%;">DESCRIPCIÓN:</td>
                <td style="color: #8F9BB3; border: 1px solid rgba(0, 0, 0, .1); width: 100%;"></td>
            </tr>
        </table>
        <table class="table bordered">
            <tr>
                <td style="color: #768197; background-color: #F8F8F8; font-weight: 700;">3. DETALLE DE LA RECLAMACIÓN Y PEDIDO DEL CONSUMIDOR</td>
                <td>RECLAMO<sup>1</sup></td>
                <td></td>
                <td>QUEJA<sup>2</sup></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="5">DETALLE:</td>
            </tr>
            <tr>
                <td colspan="5">PEDIDO:</td>
            </tr>
        </table>
        <table class="table bordered">
            <tr>
                <td style="color: #768197; background-color: #F8F8F8; font-weight: 700;" colspan="4">4. OBSERVACIONES Y ACCIONES ADOPTADAS POR EL PROVEEDOR</td>
            </tr>
            <tr>
                <td>FECHA DE COMUNICACIÓN DE LA RESPUESTA:</td>
                <td>[DÍA]</td>
                <td>[MES]</td>
                <td>[AÑO]</td>
            </tr>
            <tr>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td colspan="4" style="background-color: #0399DE; color: #FFF;">
                    <ul style="list-style: none;">
                        <li><sup>1</sup>RECLAMO: Disconformidad relacionada a los productos o servicios.</li>
                        <li><sup>2</sup>QUEJA: Disconformidad no relacionada a los productos o servicios; o, malestar o descontento respecto a la atención al público.</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    Destinatario( consumidor, proveedor o INDECOPI según corresponda) 
                </td>
            </tr>
        </table>
        <table class="table">
            <tr>
                <td>
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