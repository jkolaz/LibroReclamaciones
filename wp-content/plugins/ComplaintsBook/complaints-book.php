<?php
/**
 * Plugin Name: Complaints Book Plugin
 * Plugin URI: #
 * Description:
 * Version: 1.0
 * Author: Julio Salsavilca
 * Author URI: https://www.linkedin.com/in/julio-salsavilca-huamanyauri/
 * License: GPL2
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! defined( 'COMPLAINTS_BOOK_FILE' ) ) {
    define( 'COMPLAINTS_BOOK_FILE', __FILE__ );
}

if ( ! defined('COMPLAINTS_BOOK_PATH' ) ) {
    define('COMPLAINTS_BOOK_PATH', plugin_dir_path(__FILE__));
}

if ( ! defined('COMPLAINTS_BOOK_URL' ) ) {
    define('COMPLAINTS_BOOK_URL', plugin_dir_url(__FILE__));
}

if ( ! defined('COMPLAINTS_BOOK_IMAGES') ) {
    define('COMPLAINTS_BOOK_IMAGES', COMPLAINTS_BOOK_URL . 'front/assets/images');
}

require_once( COMPLAINTS_BOOK_PATH . 'vendor/autoload.php' );

require_once( COMPLAINTS_BOOK_PATH . 'includes/autoloader.php' );

function activate_complaints_book() :void {
    \ComplaintsBook\Includes\ComplaintsBookActivator::activate();
}

function deactivate_complaints_book() :void {
    \ComplaintsBook\Includes\ComplaintsBookDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_complaints_book' );
register_deactivation_hook( __FILE__, 'deactivate_complaints_book' );

function run_complaints_book() :void {
    global $complaintsBookPublic;

    $plugin = new \ComplaintsBook\Includes\ComplaintsBook();
    $plugin->run();

    $complaintsBookPublic = $plugin->getPublicClass();
}

run_complaints_book();