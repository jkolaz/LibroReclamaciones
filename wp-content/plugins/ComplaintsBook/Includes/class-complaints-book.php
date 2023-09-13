<?php
namespace ComplaintsBook\Includes;

use ComplaintsBook\Admin\ComplaintsBookAdmin;
use ComplaintsBook\Public\ComplaintsBookPublic;

class ComplaintsBook
{
    protected $loader;

    protected $complaints_book;

    protected $version;

    protected $publicClass;

    public static $slugCorporateName;

    public static $postTypeCorporateName;

    public static $slugPdfTemplate;

    public static $postTypePdfTemplate;

    public static $slugComplaintsBook;

    public static $postTypeComplaintsBook;

    public function __construct()
    {
        $this->complaints_book  = 'complaints-book';
        $this->version          = '1.0.0';
        $this->loader           = ComplaintsBookLoader::getInstance();

        $this->settingNames();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function settingNames() :void {
        self::$postTypeCorporateName    = 'cb-corporate-name';
        self::$slugCorporateName        = 'corporate-name';

        self::$postTypePdfTemplate      = 'cb-pdf-template';
        self::$slugPdfTemplate          = 'pdf-template';

        self::$postTypeComplaintsBook   = 'cb-complaints-book';
        self::$slugComplaintsBook       = 'complaints-book';
    }

    /**
     * @return void
     */
    public function set_locale() :void
    {
        $plugin_i18n = new ComplaintsBooki18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * @return void
     */
    public function define_admin_hooks() :void
    {
        $plugin_admin = new ComplaintsBookAdmin( $this->get_complaints_book(), $this->get_version() );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action('init', $plugin_admin, 'includeCustomFields');
    }

    private function define_public_hooks()
    {
        $plugin_public = new ComplaintsBookPublic( $this->loader, $this->get_complaints_book(), $this->get_version() );
        $this->publicClass = $plugin_public;
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public,'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public,'enqueue_scripts');
        $this->loader->add_filter( 'wp_mail_content_type', $plugin_public, 'set_content_type' );
        $this->loader->add_action( 'wp_ajax_register-complaints-book', $plugin_public, 'registerComplaintsBook' );
        $this->loader->add_action( 'wp_ajax_nopriv_register-complaints-book', $plugin_public, 'registerComplaintsBook' );
    }

    /**
     * @return mixed
     */
    public function getPublicClass() :mixed
    {
        return $this->publicClass;
    }

    /**
     * @return void
     */
    public function run() :void
    {
        $this->loader->run();
    }

    /**
     * @return mixed
     */
    public function get_complaints_book() :mixed
    {
        return $this->complaints_book;
    }

    /**
     * @return ComplaintsBookLoader
     */
    public function get_loader() :ComplaintsBookLoader
    {
        return $this->loader;
    }

    /**
     * @return string
     */
    public function get_version() :string
    {
        return $this->version;
    }
}