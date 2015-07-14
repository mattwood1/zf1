<?php
class God_Model_PhotosetTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_Photoset');
    }
    
    public function getThumbnails()
    {
        $query = $this->getInstance()
            ->createQuery('p')
            ->leftJoin('p.model m')
            ->innerJoin('m.names n')

            ->where('m.active = ?', 1)
            ->andWhere('m.ranking > -1')
            ->andWhere('n.default = ?', 1)
            ->andWhere('p.active = ?', 1)
            ->andWhere('p.manual_thumbnail = ?', 0)
            ->orderBy('m.ranking desc, n.name asc, p.name asc');
    
        return $query;
    }
}