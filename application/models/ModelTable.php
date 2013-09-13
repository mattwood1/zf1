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

    /**
     * Gets a single model
     */
    public function getModel($id)
    {
        $this->_query = $this->getInstance()
            ->createQuery('m')
            ->innerJoin('m.names n')
            ->where('m.ID = ?', $id);
    }

    /**
     *
     * @return array of ranking => count
     */
    public function getRankingStats($minimum = null)
    {
        $this->getModels();

        $ranking = array();
        foreach ($this->_query->execute() as $model) {
            @$ranking[$model->ranking]++; // @ to suppress warnings.
        }

        if ($minimum) {
            foreach ($ranking as $rank => $number) {
                if ($number < $minimum) {
                    unset($ranking[$rank]);
                }
            }
        }

        ksort($ranking);

        return $ranking;
    }

    public function getModelsByRanking($ranking)
    {
        $this->_query = $this->getInstance()
            ->createQuery('m')
            ->innerJoin('m.names n')
            ->innerJoin('m.photosets p')
            ->where('m.active = ?', 1)
            ->andWhere('m.ranking = ?', $ranking)
            ->andWhere('n.default = ?', 1)
            ->andWhere('p.active = ?',1);

        return $this->_query->execute();
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