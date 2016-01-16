<?php
class God_Model_ModelNameTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_ModelName');
    }

    public function getActiveModelNames()
    {
        $query = $this->getInstance()
            ->createQuery('mn')
            ->innerJoin('mn.model m')
            ->where('m.active = ?', 1)
            ->andWhere('m.ranking > -1');
        return $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
    }

}