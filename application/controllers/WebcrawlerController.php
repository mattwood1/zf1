<?php

class WebcrawlerController extends Coda_Controller
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        /* Index */
//        $webUrlQuery = God_Model_WebURLTable::getInstance()
//            ->createQuery('wu')
//            ->orderBy('wu.dateCreated DESC')
//            ->leftJoin('wu.ModelNameWebURL mnwu')
//            ->leftJoin('mnwu.modelName mn');

        $webUrlQuery = God_Model_WebCrawlerUrlTable::getInstance()
            ->createQuery('wcu')
            ->leftJoin('wcu.modelnamelinks mnl')
            ->leftJoin('mnl.modelName mn');

        if ($this->_request->getParam('modelid')) {
            $this->view->model = God_Model_ModelTable::getInstance()->find($this->_request->getParam('modelid'));
            $modelNames = God_Model_ModelNameTable::getInstance()->createQuery('mn')
                ->select('ID')
                ->where('model_id = ?', $this->_request->getParam('modelid'))
                ->execute();
            foreach ($modelNames as $modelName) {
                $modelIds[] = $modelName->ID;
            }

            $webUrlQuery
                ->whereIn('mn.id', $modelIds);
        }

//        $webUrlQuery->andWhere('wu.linked < 0');

//        _d($webUrlQuery); exit;

        $paginator = new Doctrine_Pager($webUrlQuery, $this->_getParam('page', 1), 5);
        $webUrls = $paginator->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $this->view->paginator = $paginator;
        $this->view->webUrls = $webUrls;
    }
}