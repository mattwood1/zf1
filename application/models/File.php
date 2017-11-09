<?php
/**
 * File Handler
 */
class God_Model_File
{
    protected $_handle;
    protected $_path;
    protected $_files = array();
    protected $_directories = array();


    public function __construct($path)
    {
        $this->_path = $path;
        
        if (!file_exists($this->_path)) {
            self::createPath($this->_path);
        }
        
        $this->_handle = opendir($path);
        
        if ($this->_handle) {
            while (false !== ($item = readdir($this->_handle))) {
                if ($item != "." && $item != "..") {      // remove '.' '..' directories
                    if (is_file($this->_path.'/'.$item) == true) {
                        $this->_files[] = $item;
                    }
                    if (is_dir($this->_path.'/'.$item) == true) {
                        $this->_directories[] = $item;
                    }
                }
            }
        }
        return $this;
    }
    
    public function __destruct()
    {
        if (is_resource($this->_handle)) {
            closedir($this->_handle);
        }
    }

    public static function createPath($path)
    {
        $pathParts = explode(DIRECTORY_SEPARATOR, $path);
        $pathParts = array_filter($pathParts);

        $checkPath = null;
        foreach ($pathParts as $pathPart) {
            $checkPath .= DIRECTORY_SEPARATOR . $pathPart;

            if (!realpath($checkPath)) {
                mkdir($checkPath, 0777);
            }

        }
    }
    
    /**
     * Static function to perform a directory scan
     * @param string $path
     * @return \self
     */
    public static function scanPath($path)
    {
        return new self($path);
    }

    public function getDirectories()
    {
        sort($this->_directories);
        return $this->_directories;
    }
    
    public function getFiles()
    {
        sort($this->_files, SORT_NUMERIC);
        return $this->_files;
    }
}