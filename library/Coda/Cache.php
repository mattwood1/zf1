<?php
class Coda_Cache
{
    protected $_cache;

    function __construct($seconds = 3600)
    {
        if (!$this->_cache) {
            $this->_cache = Zend_Cache::factory('Core', 'Memcached', array(
                   'lifetime' => $seconds,
                   'automatic_serialization' => true
                )
            );
        }
        return $this;
    }

    public function save($key, $value)
    {
        $this->_cache->save($value, $key);
    }

    public function load($key)
    {
        if (isset($_GET['ignorecache'])) return false;
        return $this->_cache->load($key);
    }
}
?>
