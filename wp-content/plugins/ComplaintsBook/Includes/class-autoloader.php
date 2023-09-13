<?php
namespace ComplaintsBook\Includes;

class Autoloader {

    private $namespaceValidator;

    private $fileRegistry;

    public function __construct()
    {
        $this->namespaceValidator = new NamespaceValidator();
        $this->fileRegistry       = new FileRegistry();
    }

    public function load(string $filename) {
        if ( $this->namespaceValidator->isValid( $filename ) ) {
            $this->fileRegistry->load( $filename );
        }
    }
}