<?php
class God_Model_ModelTable extends Doctrine_Record
{

    protected $_query;
    protected $_order;
    protected $_search = '';
    protected $_ranking = array(); // Array of all rankings

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
            ->leftJoin('m.photosets p')
            ->where('m.active = ?', 1)
            ->andWhere('m.ranking >= ?', 0)
            ->andWhere('n.default = ?', 1);
            //->andWhere('p.active = ?',1);

        $this->_getOrder();
    }
    
    public function getActivePhotosets()
    {
        $this->_query->andWhere('p.active = ?', 1);
    }

    /**
     * Gets the Ranking Stats
     * @param integer $minimum
     * @param boolean $checkPhotosets
     * @return ranking['rank'] => count
     */
    public function getRankingStats($minimum = 1, $checkPhotosets = false)
    {
        if (!$this->_ranking) {
            
            $this->getModels();
            $this->getActivePhotosets();
            if ($checkPhotosets) {
                $this->_query
                    ->select('m.*');
            }
            
            // 26 seconds to process models, 7 seconds for an array.
            foreach ($this->_query->execute( array(), Doctrine_Core::HYDRATE_ARRAY) as $model) {
                @$this->_ranking[$model['ranking']]++; // @ to suppress warnings.
            }
        }

        $ranking = $this->_ranking;
        
        if (@$_GET['test'] == 1) {
            $ranking = array(
                1 => 1,
                2 => 1,
                3 => 1,
                4 => 11,
                5 => 11,
                6 => 10,
                7 => 10,
                8 => 10,
                9 => 1,
                10 => 1
            );
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
        $this->getModels();
        $this->getActivePhotosets();
        $this->_query
                ->andWhere('m.ranking = ?', $ranking)
                ->orderBy('m.rankDate, m.id')
                ;

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
    
    public function addModel($name)
    {
        $modelnames = $this->getInstance()->createQuery('m')
                ->leftJoin('m.names n')
                ->where('n.name = ?', $name)
                ->execute();
        
        if (count($modelnames) == 0) {
            $model = new God_Model_Model();
            $model->name = $name;
            $model->save();
            
            $modelname = new God_Model_ModelName();
            $modelname->name = $name;
            $modelname->model_id = $model->id;
            $modelname->default = 1;
            
            return true;
        } else {
            return false;
        }
    }
}