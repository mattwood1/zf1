<?php

class WebLinkController extends Coda_Controller
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {

    }

    public function statusAction()
    {
        /*
         * List Models where WebLinks are outstanding - Takes a long time!!!
         */

        $query = Doctrine_Query::create()
            ->select('count(l.model_id) count, n.name, m.ID, l.*')
            ->from('God_Model_WebLink l')
            ->innerJoin('l.model m')
            ->innerJoin('m.names n')

            ->where('action = ?', God_Model_WebLink::webLink_GotThumbs)
            ->andWhere('n.default = ?', 1)

            ->groupBy('l.model_id')
            ->orderBy('m.ranking desc')
        ;

        $weblinks = $query->execute();
        $this->view->webLinks = $weblinks;
    }
}