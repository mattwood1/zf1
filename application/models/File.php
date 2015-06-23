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
        closedir($this->_handle);
    }

    public function getDirectories()
    {
        sort($this->_directories);
        return $this->_directories;
    }
    
    public function getFiles()
    {
        sort($this->_files);
        return $this->_files;
    }
}