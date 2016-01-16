<?php
class God_Model_ModelNameTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_ModelName');
    }

    public function getActiveModelNames()
    {
        $cache = Zend_Cache::factory('Core', 'Memcached', array('automatic_serialization' => true));
        $cachekey = 'activeModelNames';

        $activeModelNames = $cache->load($cachekey);

        if (!$activeModelNames) {
            _d('Query active names');
            $query = $this->getInstance()
                ->createQuery('mn')
                ->innerJoin('mn.model m')
                ->where('m.active = ?', 1)
                ->andWhere('m.ranking > -1');
            $activeModelNames = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
            $cache->save($activeModelNames, $cachekey, array(), 36000); // cache for 1 hour
        }
        
        return $activeModelNames;
    }

}