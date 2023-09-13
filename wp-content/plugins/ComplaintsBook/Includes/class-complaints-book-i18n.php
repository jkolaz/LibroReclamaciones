<?php
namespace ComplaintsBook\Includes;

class ComplaintsBooki18n
{
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'studie-planet',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/Languages/'
        );
    }
}