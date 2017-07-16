<?php

class WebcrawlerUrlController extends Coda_Controller
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $thumbnailSize = 90000;

        $webUrlQuery = God_Model_WebCrawlerUrlTable::getInstance()
            ->createQuery('wcu')
            ->leftJoin('wcu.links as links1')
            ->leftJoin('links1.url as wcu1')
            ->leftJoin('wcu1.links as links2')
            ->leftJoin('links2.url as wcu2')

            ->leftJoin('wcu.modelnamelinks mnl')
            ->leftJoin('mnl.modelName mn')

            ->leftJoin('wcu.domain domain')

            ->andWhere('
            (
                (    domain.link_depth = 1
                 and wcu1.contenttype = "image/jpeg"
                 and wcu1.contentlength > ' . $thumbnailSize .'
                 and wcu1.domain_id = wcu.domain_id
                 and (wcu1.width > domain.minSize or wcu1.height > domain.minSize)
                 and wcu2.contenttype is null 
                 and wcu2.contentlength is null)
            OR  (
                     domain.link_depth = 2
                 and wcu1.contenttype like "text/html%"
                 and wcu2.contenttype = "image/jpeg" 
                 and wcu2.contentlength > ' . $thumbnailSize .'
                 and wcu2.domain_id = wcu.domain_id
                 and (wcu2.width > domain.minSize or wcu2.height > domain.minSize)
                )
            )')
        ;

        if ($this->_request->getParam('modelid')) {

            $this->view->model = God_Model_ModelTable::getInstance()->find($this->_request->getParam('modelid'));

            $webUrlQuery->leftJoin('mn.model m');
            $webUrlQuery->andWhere('m.id = ?', $this->_request->getParam('modelid'));
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