<?php
class God_Model_ModelTable extends Doctrine_Record
{

    protected $_query;
    protected $_order;
    protected $_search = '';
    protected $_ranking = array(); // Array of all rankings

    protected $_modelDir = 'Women';

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
            $conn = Doctrine_Manager::getInstance()->connection();
            
            $sql = "SELECT * "
                    . "FROM models m "
                    . "INNER JOIN model_names m2 ON m.id = m2.model_id "
                    . "LEFT JOIN photosets p ON m.id = p.model_id "
                    . "WHERE (m.active = '1' AND m.ranking >= '0' AND m2.default = '1' AND p.active = '1' AND p.manual_thumbnail = '1') "
                    . "GROUP BY m.id";
            
            $query = $conn->execute($sql);
            $models = $query->fetchAll();
            
            foreach ($models as $model) {
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
    
    public function addModel($name, $checkName = true)
    {
        $modelnames = array();
        if ($checkName) {
            $modelnames = God_Model_ModelNameTable::getInstance()->findBy('name', $name);
        }

        $uri = strtolower(str_replace(' ', '_', $name));

        $found = false;
        while (!$found) {
            $model = God_Model_ModelTable::getInstance()->findBy('uri', $uri);
            if (count($model)) {
                $uri = $uri . '_';
            }
            else {
                $found = true;
            }
        }

        if (count($modelnames) == 0 || !$checkName) {
            $model = God_Model_ModelTable::getInstance()->create(array(
                'name' => $name,
                'path' => '/' . $this->_modelDir . '/' . $name,
                'uri'  => $uri,
                'active' => 1,
                'ranking' => 0,
                'search' => 1
            ));
            $model->save();
                        
            $modelname = God_Model_ModelNameTable::getInstance()->create(array(
                'name' => $name,
                'model_id' => $model->ID,
                'default' => 1
            ));
            $modelname->save();
            
            return true;
        } else {
            return false;
        }
    }

    public function getModelsFromFilesystem()
    {
        $models = God_Model_ModelTable::getInstance()->findAll();

        $folders = array();
        if (is_dir('/raid' . '/' . $this->_modelDir)) {
            $handle = opendir('/raid' . '/' . $this->_modelDir);
            while (false !== ($files = readdir($handle))) {
                if ($files != "." && $files != "..") {        // remove '.' '..' directories
                    if (is_dir('/raid' . '/' . $this->_modelDir . '/' . $files) == true) {
                        $folders[] = stripslashes('/' . $this->_modelDir . '/' . $files);
                    }
                }
            }
        }

        foreach ($models as $model) {
            $key = array_search($model->path, $folders);
            if ($key !== null) {
                unset($folders[$key]);
            }
        }

        if ($folders) {
            foreach ($folders as $folder) {
                $model = str_replace('/' . $this->_modelDir . '/', '', $folder);
                _d($model);
                $this->addModel($model, false);
            }
        }
    }
}