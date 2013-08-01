<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    // Initialises the doctrine orm framework
    protected function _initDoctrine ()
    {
        // read doctrine configuration
        $config = $this->getOption('doctrine');

        // get an instance of our manager and configure
        $manager = Doctrine_Manager::getInstance();
        $manager->setAttribute(Doctrine_Core::ATTR_MODEL_CLASS_PREFIX, 'God_Model_');
        $manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_PEAR);
        $manager->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL & ~Doctrine_Core::VALIDATE_TYPES);
        $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);
        $manager->setAttribute(Doctrine_Core::ATTR_AUTO_FREE_QUERY_OBJECTS, true);
        $manager->setAttribute(Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
        $manager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
        $manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, 'conservative');

        // optional result caching
        if (isset($config['cache']) && $config['cache'] == true) {
            $cacheDriver = new Doctrine_Cache_Apc();
            $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $cacheDriver);
        }

        // create the connection and return
        $connection = $manager->openConnection($config['connection_string'], 'doctrine');
        $connection->setAttribute(Doctrine_Core::ATTR_USE_NATIVE_ENUM, true);
        $connection->setCharset('utf8');
        return $connection;
    }

}

