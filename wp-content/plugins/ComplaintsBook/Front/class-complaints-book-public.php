<?php
namespace ComplaintsBook\Front;

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
        wp_enqueue_style( 'material-design', "https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200", array(), $this->version, 'all' );
        wp_enqueue_style( $this->complaintsBook, plugin_dir_url( COMPLAINTS_BOOK_FILE ) . 'Front/assets/css/' . $fileName, array(), $this->version, 'all' );
    }

    /**
     * @return void
     */
    public function enqueue_scripts() :void
    {
        $fileName = 'complaints-book.js';
        wp_enqueue_script('jquery-validate', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.1/jquery.validate.min.js', array('jquery'));
        wp_enqueue_script( $this->complaintsBook, plugin_dir_url( COMPLAINTS_BOOK_FILE ) . 'Front/assets/js/' . $fileName, array( 'jquery' ), $this->version, false );
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
            'projects' => $this->getProjects()
        ];
    }

    public function getDomain()
    {
        return $this->complaintsBook;
    }

    /**
     * @return \WP_Query
     */
    private function getQueryProject() :\WP_Query
    {
        $args = [
            'post_type' => ComplaintsBook::$postTypeCorporateName,
            'orderby' => 'title',
            'nopaging' => true
        ];

        return new \WP_Query($args);
    }

    /**
     * @return array
     */
    public function getCorporateNames() :array
    {
        $corporateNames = [];
        $query = $this->getQueryProject();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $post = $query->post;
                $id = $post->ID;
                $correlative = get_field('cn_correlative', $id);
                $number = get_field('cn_number', $id);
                if (!empty($number)) {
                    $number = 1;
                }

                $corporateNames[] = (object)[
                    'id' => $id,
                    'name' => $post->post_title,
                    'ruc' => get_field('cn_ruc', $id),
                    'address' => get_field('cn_address', $id),
                    'correlative' => $correlative . $number,
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

    public function getCorrelative()
    {
        $id = $_POST['id'];
        $correlative = get_field('cn_correlative', $id);
        $number = get_field('cn_number', $id);
        $newCorrelative = $this->complaintBookService->getCorrelative($correlative, $number);

        echo wp_json_encode([
            'correlative' => $newCorrelative
        ]);
        wp_die();
    }

    /**
     * @return array
     */
    private function getProjects() :array
    {
        $projects = [];
        $query = $this->getQueryProject();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $post = $query->post;
                $id = $post->ID;
                $correlative = get_field('cn_correlative', $id);
                $number = get_field('cn_number', $id);
                if (empty($number)) {
                    $number = 1;
                }

                $projects[] = (object)[
                    'id' => $id,
                    'correlative' => $correlative . $number,
                ];
            }
        }

        wp_reset_postdata();

        return $projects;
    }
}