<?php
namespace ComplaintsBook\Admin;

use ComplaintsBook\PostTypes\CorporateName;

class ComplaintsBookAdmin
{
    private $complaintsBook;

    private $version;

    public function __construct(string $complaintsBook, string $version)
    {
        $this->complaintsBook = $complaintsBook;
        $this->version = $version;
        $this->bootstrap();
    }

    protected function bootstrap() :void {
        $classes = $this->registerClasses();

        if ( !empty($classes) ) {
            foreach ($classes as $class) {
                $instance = new $class($this->complaintsBook);
                $instance->init();
            }
        }
    }

    public function enqueue_styles() {
        wp_enqueue_style( $this->complaintsBook, plugin_dir_url( __FILE__ ) . 'css/complaints-book-admin.css', array(), $this->version, 'all' );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( $this->complaintsBook, plugin_dir_url( __FILE__ ) . 'js/complaints-book-admin.js', array( 'jquery' ), $this->version, false );
    }

    public function includeCustomFields() :void {
        include_once( COMPLAINTS_BOOK_PATH . '/Admin/ACF/acf-complaints-book.php' );
        include_once( COMPLAINTS_BOOK_PATH . '/Admin/ACF/acf-corporate-name.php' );
        include_once( COMPLAINTS_BOOK_PATH . '/Admin/ACF/acf-pdf-template.php' );
    }

    public function addRewriteRules() :void {
        flush_rewrite_rules();
    }

    private function registerClasses(): array
    {
        return [
            CorporateName::class
        ];
    }
}