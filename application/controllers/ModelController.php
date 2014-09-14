<?php

class ModelController extends Coda_Controller
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function viewAction()
    {
        $model = God_Model_ModelTable::getInstance()->find($this->_request->getParam('id'));
        
        // TODO: It would be good to have a function $model->getPhotosets with pagination
        // Needs to return a query instance.
        
//         add body
        $query = Doctrine_Core::getTable('God_Model_Photoset')
            ->createQuery('p')
            ->leftJoin('p.model m')
            ->innerJoin('m.names n')

            ->where('m.ID = ?', $this->_request->getParam('id'))
            ->andWhere('m.active = ?', 1)
            ->andWhere('n.default = ?', 1)
            ->andWhere('p.active = ?', 1)
            ->orderBy('p.name asc');
        
        $paginator = new Doctrine_Pager($query, $this->_getParam('page',1), 18 );

        $photosets = $paginator->execute();

        $this->view->paginator = $paginator;
        $this->view->model = $model;
        $this->view->photosets = $photosets;
    }

    public function addAction()
    {
        // add body
        $form = new God_Form_AddModel();
        $this->view->form = $form;
    }

    public function editAction()
    {
        // edit body
        $form = new God_Form_Model();
        $form->submit->setLabel('Save');
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData)) {
                $id = (int)$form->getValue('id');
                $artist = $form->getValue('name');
                /*
                 * TODO: Add in the path and uri
                 */
                $models = new God_Model_Model();
                $models->updateModel($id, $name, $path, $uri);
                $this->_helper->redirector('view');
            } else {
                $form->populate($formData);
            }
        }

        else {
            $model = Doctrine_Core::getTable('God_Model_Model')
                ->createQuery('m')
                ->innerJoin('m.names n')
                ->where('m.ID = ?', $this->_request->getParam('id'));
            $model = $model->execute()->toArray();

            //echo '<pre>';var_dump($model);echo '</pre>';

            $form->populate($model[0]);
        }

        $model = Doctrine_Core::getTable('God_Model_Model')->findOneBy('ID', $this->_request->getParam('id'));

        $this->view->model = $model;
    }

    public function rankingAction()
    {
        $factor = 10;
        $hour = (int)date("G", mktime());
        
        if ($this->_request->isPost()) {
            $model = Doctrine_Core::getTable('God_Model_Model')->findOneBy('ID', $this->_request->getParam('model_id'));

            // Check the ranking value to prevent mis clicks and multi clicks
            if ($model->ranking == $this->_request->getParam('model_ranking')) {
                $model->ranking++;
                $model->rankDate = date("Y-m-d h:i:s", mktime());
                $model->search = (bool)$this->_request->getParam('search');
                $model->save();
            }
        }

        $modelTable = new God_Model_ModelTable;

        // Get model ranking stats
        $modes = array();
        
        // TODO: I was thinking the needs to be in a model
        // class ModelRanking extends Model
        // it would hold the data that is worked out below
        // and would be easily called on.
        // It could be used to figure out which models need 
        // to be reset to keep the data flowing.

        $rankingStats = $modelTable->getRankingStats(2, true);
        $topHigh = max(array_keys($modelTable->getRankingStats(1, true)));
        $topLow = $topHigh - floor(($topHigh / 100) * $factor);
        $highArray = array_keys($rankingStats, max($rankingStats));
        $high = $highArray[0];

        $modes = array('random', 'high-ordered');

        $topRankingStats = $rankingStats;
        foreach (array_keys($topRankingStats) as $topKey) {
            if ($topKey < $topLow) {
                unset($topRankingStats[$topKey]);
            }
        }
        if ($topRankingStats) {
            $modes[] = 'top-random';
            $modes[] = 'top-ordered';
        }

        $bottomRankingStats = $rankingStats;
        foreach ($bottomRankingStats as $bottomKey => $bottomStat) {
            
            $offset = ceil(($high-1) / 100 ) * $factor;
            
            $highCount = $rankingStats[$high];
            
            if ( ($bottomKey < $high) || ($bottomStat < ($high-$offset)) ) {
                unset($bottomRankingStats[$bottomKey]);
            }
        }
        if ($bottomRankingStats) {
            $modes[] = 'bottom-random';
            $modes[] = 'bottom-ordered';
        }

        // Only use 'top' and 'botom' on even hours
        if ( $hour%2 == 0 ) {
            foreach (array('random', 'top-random', 'bottom-random') as $remove) {
                $key = array_search($remove, $modes);
                if($key !== false) {
                    unset($modes[$key]);
                }
            }
        } else {
            foreach (array('top-ordered', 'bottom-ordered') as $remove) {
                $key = array_search($remove, $modes);
                if($key !== false) {
                    unset($modes[$key]);
                }
            }
        }
        
        $mode = $modes[array_rand($modes, 1)];
        switch ($mode) {
            case 'random':
                $rankingStatsKey = array_rand($rankingStats, 1);
                break;
            case 'top-random':
                $rankingStatsKey = array_rand($topRankingStats, 1);
                break;
            case 'top-ordered':
                $ordered = array_keys($topRankingStats);
                $rankingStatsKey = $ordered[0];
                break;
            case 'high-ordered':
                $rankingStatsKey = $high;
                break;
            case 'bottom-random':
                $rankingStatsKey = array_rand($bottomRankingStats,1 );
                break;
            case 'bottom-ordered':
                $ordered = array_keys($bottomRankingStats);
                $rankingStatsKey = $ordered[0];
                break;
        }

        // Get models where ranking = the chosen stat
        $models = $modelTable->getModelsByRanking($rankingStatsKey);
        
        $modelArrayKeys = array_keys($models->toArray());
        $modelKeys[] = $modelArrayKeys[0];
        unset($modelArrayKeys[0]);
        shuffle($modelArrayKeys);
        $modelKeys[] = $modelArrayKeys[0];
        shuffle($modelKeys);

        $this->view->mode = $mode;
        $this->view->models = $models;
        $this->view->modelKeys = $modelKeys;
    }

    public function statsAction()
    {
        $modelTable = new God_Model_ModelTable;
        $this->view->rankings = $modelTable->getRankingStats();
    }

    /*
     * Potentially redundant
     */
    public function webLinkAction()
    {
        if ($this->_request->isPost()) {
            foreach ($this->_request->getParam('weblink') as $id => $action) {
                $weblink = Doctrine_Core::getTable('God_Model_WebLink')->findBy('id', $id);
                $weblink->set('action', $action);
                //$weblink->save();
                echo '<pre>'; var_dump($action, $weblink); echo '</pre>';
            }
        }

        $query = Doctrine_Query::create()
            ->select('l.*')
            ->from('God_Model_WebLink l')
            ->innerJoin('l.model m')
            ->innerJoin('m.names n')

            ->where('action = ?', $this->_request->getParam('key'))
            ->andWhere('m.ID = ?', $this->_request->getParam('id'))
            ->andWhere('n.default = ?', 1)

            ->limit(5);

            //->groupBy('l.model_id')
            //->orderBy('m.ranking desc')
        ;
        $weblinks = $query->execute();
        $this->view->webLinks = $weblinks;
        /*
        echo '<pre>';
        var_dump($weblinks->toArray());
        exit;
        */
    }

    public function deleteAction()
    {
        // delete body
    }

}
