<?php
namespace ComplaintsBook\Includes;

class FileRegistry {

    private $investigator;

    public function __construct()
    {
        $this->investigator = new FileInvestigator();
    }

    /**
     * @param string $filepath
     * @return void
     */
    public function load(string $filepath) :void
    {
        $filepath = $this->investigator->getFileType( $filepath );
        $filepath = rtrim( plugin_dir_path( dirname( __FILE__ ) ), '/' ) . $filepath;

        if ( file_exists( $filepath ) ) {
            include_once( $filepath );
        } else {
            wp_die(
                esc_html( 'The specified file does not exist.' )
            );
        }
    }
}