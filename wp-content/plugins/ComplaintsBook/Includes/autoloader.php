<?php
namespace ComplaintsBook\Includes\Autoloader;

use ComplaintsBook\Includes\Autoloader;

foreach (glob( dirname( __FILE__ ) . '/class-*.php' ) as $filename ) {
    include_once( $filename );
}

$autoloader = new Autoloader();
spl_autoload_register( array( $autoloader, 'load' ) );