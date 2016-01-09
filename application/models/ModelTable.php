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

        $this->_getOrder();
    }
    
    public function getActivePhotosets()
    {
        $this->_query->andWhere('p.active = ?', 1);
    }
    
    public function getOnlyManualThumbs()
    {
        $this->_query->andWhere('p.manual_thumbnail = ?', 1);
    }

    /**
     * Gets the Ranking Stats
     * @param integer $minimum
     * @param boolean $checkPhotosets
     * @return ranking['rank'] => count
     */
    public function getRankingStats($minimum = 1, $checkPhotosets = false)
    {
        // SELECT ranking, count(ID) FROM `models` where active = 1 and ranking > 0 group by ranking
        // Query needs work to check photosets exist
        
        if (!$this->_ranking) {
            
            $this->getModels();
            $this->getActivePhotosets();
            $this->getOnlyManualThumbs();
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

        return $this->_query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
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
        $modelnames = God_Model_ModelNameTable::getInstance()->findBy('name', $name);
        
        if (count($modelnames) == 0) {
            $model = God_Model_ModelTable::getInstance()->create(array(
                'name' => $name
            ));
            $model->save();
                        
            $modelname = God_Model_ModelNameTable::getInstance()->create(array(
                'name' => $name,
                'model_id' => $model->id,
                'default' => 1
            ));
            $modelname->save();
            
            return true;
        } else {
            return false;
        }
    }
}