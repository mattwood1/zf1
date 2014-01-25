<?php
class Coda_Cache
{
    function __construct()
    {
        // Doesn't work - WHY!
        return Zend_Cache::factory('Core', 'Memcached', array(
               'lifetime' => 7200, // cache lifetime of 2 hours
               'automatic_serialization' => true
            )
        );
    }
}
?>