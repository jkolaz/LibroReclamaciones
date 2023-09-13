<?php
namespace ComplaintsBook\PostTypes;

use ComplaintsBook\Includes\ComplaintsBook;
use ComplaintsBook\Includes\ComplaintsBookLoader;
use ComplaintsBook\Interfaces\RunHooksInterface;

class CorporateName implements RunHooksInterface
{
    private $loader;

    private $domain;

    public function __construct( $domain ) {
        $this->loader   = ComplaintsBookLoader::getInstance();
        $this->domain   = $domain;
    }

    /**
     * @return void
     */
    public function init() :void
    {
        $this->loader->add_action('init', $this, 'registerPostType');
    }

    /**
     * @return void
     */
    public function registerPostType() :void {
        $this->postTypeComplaintsBook();
        $this->postTypeCorporationName();
        $this->postTypeTemplate();
    }

    /**
     * @return void
     */
    private function postTypeCorporationName() :void
    {
        $args = [
            'labels' => $this->retrieveLabels('Corporate Name', 'Corporate Names'),
            'description' => 'Corporate Name',
            'public' => true,
            'publicly_queryable' => true,
            'query_var' => true,
            'show_ui' => true,
            'show_in_nav_menus' => true,
            'show_in_menu' => true,
            'show_in_admin_bar' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-welcome-learn-more',
            'supports' => [
                'title',
                'editor',
                'custom-fields',
                'excerpt',
                'thumbnail',
                'author',
                'comments',
                'revisions',
                'page-attributes',
            ],
            'has_archive' => true,
            'rewrite' => [
                'slug' => ComplaintsBook::$slugCorporateName
            ]
        ];

        register_post_type(ComplaintsBook::$postTypeCorporateName, $args);
    }

    /**
     * @return void
     */
    private function postTypeTemplate() :void
    {
        $args = [
            'labels' => $this->retrieveLabels('PDF Template', 'PDF Templates'),
            'description' => 'PDF Template',
            'public' => true,
            'publicly_queryable' => true,
            'query_var' => true,
            'show_ui' => true,
            'show_in_nav_menus' => true,
            'show_in_menu' => true,
            'show_in_admin_bar' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-welcome-learn-more',
            'supports' => [
                'title',
                'editor',
                'custom-fields',
                'excerpt',
                'thumbnail',
                'author',
                'comments',
                'revisions',
                'page-attributes',
            ],
            'has_archive' => true,
            'rewrite' => [
                'slug' => ComplaintsBook::$slugPdfTemplate
            ]
        ];

        register_post_type(ComplaintsBook::$postTypePdfTemplate, $args);
    }

    /**
     * @return void
     */
    private function postTypeComplaintsBook() :void
    {
        $args = [
            'labels' => $this->retrieveLabels('Complaint Book', 'Complaints Books'),
            'description' => 'Complaint Book',
            'public' => true,
            'publicly_queryable' => true,
            'query_var' => true,
            'show_ui' => true,
            'show_in_nav_menus' => true,
            'show_in_menu' => true,
            'show_in_admin_bar' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-welcome-learn-more',
            'supports' => [
                'title',
                'editor',
                'custom-fields',
                'excerpt',
                'thumbnail',
                'author',
                'comments',
                'revisions',
                'page-attributes',
            ],
            'has_archive' => true,
            'rewrite' => [
                'slug' => ComplaintsBook::$slugComplaintsBook
            ]
        ];

        register_post_type(ComplaintsBook::$postTypeComplaintsBook, $args);
    }

    /**
     * @param string $singleName
     * @param string $pluralName
     * @return array
     */
    private function retrieveLabels(string $singleName, string $pluralName) :array
    {
        return [
            'name' => __($pluralName, $this->domain),
            'singular_name' => __($singleName, $this->domain),
            'add_new' => __('New ' . $singleName, $this->domain),
            'add_new_item' => __('Add new ' . $singleName, $this->domain),
            'edit_item' => __('Edit ' . $singleName, $this->domain),
            'new_item' => __('New ' . $singleName, $this->domain),
            'view_item' => __('Show ' . $singleName, $this->domain),
            'search_items' => __('Search ' . $pluralName, $this->domain),
            'not_found' => __($singleName . ' not found', $this->domain),
            'not_found_in_trash' => __($singleName . ' not found in the trash', $this->domain),
            'all_items' => __($pluralName, $this->domain),
        ];
    }
}