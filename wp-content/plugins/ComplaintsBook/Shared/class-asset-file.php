<?php
namespace ComplaintsBook\Shared;

class AssetFile
{
    private static $instance =  null;

    /**
     * @return self|null
     */
    public static function getInstance() :self|null
    {
        if ( null === self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param $folder
     * @param $type
     * @return array|false
     */
    public function getFiles($folder, $type) :array|false
    {
        $path = COMPLAINTS_BOOK_PATH . "{$folder}/assets/{$type}";
        $file = file_exists($path) ? scandir($path) : false;

        return $file ? array_values(array_filter(array_slice($file, 2), function($file) {
            return !strpos($file, '.map');
        })) : $file;
    }
}