<?php

class GalleryController extends Coda_Controller
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function viewAction()
    {
        $photoset = Doctrine_Core::getTable('God_Model_Photoset')
            ->findOneBy('id', $this->_request->getParam('photoset'));

        $urlIDs = array();
        $webCrawlerUrlPhotosets = God_Model_WebCrawlerUrlPhotosetsTable::getInstance()->findBy('photoset_id', $photoset->id);
        foreach ($webCrawlerUrlPhotosets as $webCrawlerUrlPhotoset) {
            $urlIDs[] = $webCrawlerUrlPhotoset->url_id;
        }

        $webCrawlerUrls = null;
        if ($urlIDs) {
            $webCrawlerUrls = God_Model_WebCrawlerUrlTable::getInstance()->createQuery()
                ->whereIn('id', $urlIDs)
                ->execute();
        }

        $this->view->photoset = $photoset;
        $this->view->files = $this->_getFiles($photoset->path);
        $this->view->webcrawlerurls = $webCrawlerUrls;
    }

    public function thumbnailAction()
    {
        $photoset = Doctrine_Core::getTable('God_Model_Photoset')->findOneBy('id', $this->_request->getParam('photoset'));

        if ($this->_request->isPost()) {
            if ($this->_request->getParam('thumbnail')) {
                $photoset->thumbnail = $this->_request->getParam('thumbnail');
                $photoset->manual_thumbnail = true;
            }

            if ($this->_request->getParam('disable')) {
                $photoset->active = false;
            } else {
                $photoset->active = true;
            }

            $photoset->save();

            if ($this->_request->getParam('referer')) {
                $this->_redirect($this->_request->getParam('referer'));
            }
        }

        $this->view->photoset = $photoset;
        $this->view->files = $this->_getFiles($photoset->path);
    }

    public function duplicateAction()
    {
        $pretestResults = God_Model_ImageHashTable::getDuplicateHashes(false, 1);

        // SQL query with a WHERE seems to take a long time.

        $duplicateImages = null;
        $photosets = null;
        if ($pretestResults) {

            $conn = Doctrine_Manager::getInstance()->connection();

            $results = $conn->execute('SELECT
                im1.id as imageid1,
                p1.id photosetid1,

                im2.id as imageid2,
                p2.id photosetid2

                    FROM `imagehash` ih1
                    JOIN imagehash ih2 ON (ih1.hash = ih2.hash and ih1.id != ih2.id)
                    JOIN images im1 ON (ih1.image_id = im1.id)
                    JOIN images im2 ON (ih2.image_id = im2.id)

                    JOIN photosets p1 ON (im1.photoset_id = p1.id AND p1.id = ' . $pretestResults[0]['photosetid1'] . ')
                    JOIN photosets p2 ON (im2.photoset_id = p2.id AND p2.id = ' . $pretestResults[0]['photosetid2'] . ')

                    WHERE ih1.hash != ""'
            );

            $duplicateImages = $results->fetchAll();
        }

        if ($duplicateImages) {

            $usedPhotosetIds = array(); // Storing used photosets
            $photosets = array(); // storing photosets and duplicate images

            foreach ($duplicateImages as $duplicateImage) {

                if (!(in_array($duplicateImage['photosetid1'], $usedPhotosetIds) || in_array($duplicateImage['photosetid2'], $usedPhotosetIds))) {
                    // add photosets to photosets array
                    $photosets[$duplicateImage['photosetid1']]['photosets'] = array(
                        'photoset1' => God_Model_PhotosetTable::getInstance()->find($duplicateImage['photosetid1']),
                        'photoset2' => God_Model_PhotosetTable::getInstance()->find($duplicateImage['photosetid2']),
                    );
                }

                // Store the duplicate images
                $photosets[$duplicateImage['photosetid1']]['images1'][] = $duplicateImage['imageid1'];
                $photosets[$duplicateImage['photosetid1']]['images2'][] = $duplicateImage['imageid2'];

            }
        }

        $this->view->duplicates = $photosets;
    }

    public function updateAction()
    {
        $photoset = God_Model_PhotosetTable::getInstance()->find($this->_request->getParam('photoset'));

        $photoset->updateImages(true);

        $this->gotoRoute(array('controller' => 'gallery', 'action' => 'duplicate'), false, true);
    }


    protected function _getFiles($path)
    {
        $data = array();
        if (is_dir($_SERVER['DOCUMENT_ROOT'].'/'.$path)) {
            $handle = opendir($_SERVER['DOCUMENT_ROOT'].'/'.$path);
            //    $counter = 0;
            while (false !== ($files = readdir($handle))) {
                if ($files != "." && $files != "..") {        // remove '.' '..' directories
                    if (is_file($_SERVER['DOCUMENT_ROOT'].$path.'/'.$files) == true) {
//                        $counter = $this->_threeDigits( str_ireplace(".jpg", "", $files));
                        list($width,$height)=getimagesize($_SERVER['DOCUMENT_ROOT'].$path.'/'.$files);
                        $data[] = array(
                            'uri' => $path.'/'.$files,
                            'name' => $files,
                            'width' => $width,
                            'height' => $height
                        );
                    } else {
                        echo '<p>'.$path.'/'.$files.' is a directory!</p>';
                    }
                }
            }
            closedir($handle);
        } else {
            return false;
        }

        sort($data, SORT_NUMERIC);

        return $data;
    }

    protected function _threeDigits($value) {
        return str_pad($value, 3, '0', STR_PAD_LEFT);
    }
}
