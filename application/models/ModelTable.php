<?php
class God_Model_ModelTable extends Doctrine_Record
{

    protected $_query;
    protected $_order = 'ranking';
    protected $_search = '';

    const ORDER_RANKING = 'ranking';
    const ORDER_NAME = 'name';

    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_Model');
    }

    public function getModels()
    {
        $this->_query = $this->getInstance()
            ->createQuery('m')
            ->innerJoin('m.names n')
            ->where('m.active = ?', 1)
            ->andWhere('m.ranking >= ?', 0)
            ->andWhere('n.default = ?', 1);

        $this->_getOrder();
    }

    public function getRankingStats()
    {
        $this->_query = $this->getInstance()
            ->createQuery('m')
            ->select('COUNT( * ) AS count, m.ranking')
            ->where('m.active = ?', 1)
            ->andWhere('m.ranking >= ?', 0)
            ->groupBy('m.ranking')
            ->having('count > 1');
        return $this->_query;
    }

    public function getModelsByRanking($ranking)
    {
        $this->_query = $this->getInstance()
            ->createQuery('m')
            ->innerJoin('m.names n')
            ->where('m.active = ?', 1)
            ->andWhere('m.ranking = ?', $ranking)
            ->andWhere('n.default = ?', 1);
        return $this->_query;
    }

    protected function _getOrder()
    {
        switch ($this->_order) {
            case 'ranking':
                $this->_query->orderBy('m.ranking desc, n.name asc');
                break;
            case 'name':
                $this->_query->orderBy('n.name asc, m.ranking desc');
                break;
        }
    }

    public function setOrder($order)
    {
        $this->_order = $order;
    }

    public function setSearch($keyword)
    {
        $this->_query->andWhere('n.name like ?', '%' . $keyword . '%');
    }

    public function getQuery()
    {
        return $this->_query;
    }
}