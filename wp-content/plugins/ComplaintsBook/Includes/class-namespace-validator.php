<?php
namespace ComplaintsBook\Includes;

class NamespaceValidator
{
    /**
     * @param string $filename
     * @return bool
     */
    public function isValid(string $filename) :bool
    {
        return ( 0 === strpos( $filename, 'ComplaintsBook' ) );
    }
}