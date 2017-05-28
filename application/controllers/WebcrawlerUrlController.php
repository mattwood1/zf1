<?php

class WebcrawlerUrlController extends Coda_Controller
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
            ->innerJoin('wcu.links as links')
            ->leftJoin('links.url as suburl')

            ->innerJoin('wcu.modelnamelinks mnl')
            ->leftJoin('mnl.modelName mn')

            ->andWhere('suburl.contenttype = "image/jpeg" and suburl.contentlength >= 90000');
        ;

        if ($this->_request->getParam('modelid')) {
            $this->view->model = God_Model_ModelTable::getInstance()->find($this->_request->getParam('modelid'));
            $modelNames = God_Model_ModelNameTable::getInstance()->createQuery('mn')
                ->select('ID')
                ->andWhere('model_id = ?', $this->_request->getParam('modelid'))
                ->execute();
            foreach ($modelNames as $modelName) {
                $modelIds[] = $modelName->ID;
            }

            $webUrlQuery->andWhereIn('mn.id', $modelIds);
        }

        if ($this->_request->getParam('domainid')) {
            $webUrlQuery->andWhere('wcu.domain_id = ?', $this->_request->getParam('domainid'));
            $this->view->domain = God_Model_WebCrawlerDomainTable::getInstance()->find($this->_request->getParam('domainid'));
        }

        $webUrlQuery->orderBy('wcu.id DESC');

        $paginator = new Doctrine_Pager($webUrlQuery, $this->_getParam('page', 1), 5);
        $webUrls = $paginator->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $this->view->paginator = $paginator;
        $this->view->webUrls = $webUrls;
    }
}