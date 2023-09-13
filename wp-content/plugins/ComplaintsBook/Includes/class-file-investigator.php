<?php
namespace ComplaintsBook\Includes;

class FileInvestigator
{
    public function getFileType(string $filename) :string {
        $fileParts = explode( '\\', $filename );

        $filepath = '';
        $length   = count( $fileParts );
        for ( $i = 1; $i < $length; $i++ ) {

            if($i === 1) {
                $current = array_map( [$this, 'capitalize'], array_values(
                    array_filter(preg_split('/(?=[A-Z])/', $fileParts[ $i ]))
                ));
            } else {
                $current = array_map( 'strtolower', array_values(
                    array_filter(preg_split('/(?=[A-Z])/', $fileParts[ $i ]))
                ));
            }
            $current = implode( '-', $current );

            $filepath .= $this->getFileName( $fileParts, $current, $i );
            if ( count( $fileParts ) - 1 !== $i ) {
                $filepath = trailingslashit( $filepath );
            }
        }

        return $filepath;
    }

    public function getFileName(array $fileParts, string $current, int $i) :string {
        $filename = '';

        if ( count( $fileParts ) - 1 === $i ) {
            if ( $this->isInterface( $fileParts ) ) {

                $filename = $this->getInterfaceName($fileParts);
            } else if ( $this->isTrait( $fileParts ) ) {
                $filename = $this->getTraitName( $fileParts );
            } else {
                $filename = $this->getClassName( $current );
            }
        } else {
            $filename = $this->getNamespaceName( $current );
        }

        return $filename;
    }

    public function isInterface(array $fileParts) :bool {
        return strpos( strtolower( $fileParts[ count( $fileParts ) - 1 ] ), 'interface' );
    }

    public function isTrait(array $fileParts) :bool {
        return strpos( strtolower( $fileParts[ count( $fileParts ) - 1 ] ), 'trait' );
    }

    public function getInterfaceName(array $fileParts) :string {
        $interfaceNameArr = array_map( 'strtolower', array_values(
            array_filter(preg_split('/(?=[A-Z])/', $fileParts[ count( $fileParts ) - 1 ]))
        ));

        $interfaceName = array_slice($interfaceNameArr, 0, count($interfaceNameArr) - 1);
        $interfaceName = join('-', $interfaceName);

        return "interface-$interfaceName.php";
    }

    /**
     * @param array $fileParts
     * @return string
     */
    public function getTraitName(array $fileParts) :string
    {
        $traitNameArr = array_map( 'strtolower', array_values(
            array_filter(preg_split('/(?=[A-Z])/', $fileParts[ count( $fileParts ) - 1 ]))
        ));

        $traitName = array_slice($traitNameArr, 0, count($traitNameArr) - 1);
        $traitName = join('-', $traitName);

        return "trait-$traitName.php";
    }

    /**
     * @param string $current
     * @return string
     */
    public function getClassName(string $current) :string
    {
        return "class-$current.php";
    }

    /**
     * @param string $current
     * @return string
     */
    public function getNamespaceName(string $current) :string
    {
        return '/' . $current;
    }

    /**
     * @param $text
     * @return string
     */
    public function capitalize($text) :string
    {
        return ucwords(strtolower($text));
    }
}