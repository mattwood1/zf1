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
        $photoset = Doctrine_Core::getTable('God_Model_Photoset')->findOneBy('id', $this->_request->getParam('photoset'));

        $this->view->photoset = $photoset;
        $this->view->files = $this->_getFiles($photoset->path);
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
        ini_set('xdebug.var_display_max_depth', 10);
        $conn = Doctrine_Manager::getInstance()->connection();
        
        $pretest = $conn->execute('SELECT 
            p1.id photosetid1,
            p2.id photosetid2
            
                FROM `imagehash` ih1
                JOIN imagehash ih2 ON (ih1.hash = ih2.hash and ih1.id != ih2.id)
                JOIN images im1 ON (ih1.image_id = im1.id)
                JOIN images im2 ON (ih2.image_id = im2.id)
                
                JOIN photosets p1 ON (im1.photoset_id = p1.id)
                JOIN photosets p2 ON (im2.photoset_id = p2.id)
                
                WHERE ih1.hash != ""
                LIMIT 1');
        $pretestResults = $pretest->fetchAll();
        
//        _dexit($pretestResults);
        
        // SQL query with a WHERE seems to take a long time.
        
        $duplicateImages = null;
        $photosets = null;
        if ($pretestResults) {
        
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
        
//        _d(array('$duplicateImages' => $duplicateImages));
        
        if ($duplicateImages) {
            
            $usedPhotosetIds = array(); // Storing used photosets
            $photosets = array(); // storing photosets and duplicate images
            
            foreach ($duplicateImages as $duplicateImage) {
                
                if ( ! (in_array($duplicateImage['photosetid1'], $usedPhotosetIds) || in_array($duplicateImage['photosetid2'], $usedPhotosetIds)) ) {
                    // add photosets to photosets array
                    $photosets[$duplicateImage['photosetid1']]['photosets'] = array(
                        'photoset1' => God_Model_PhotosetTable::getInstance()->find($duplicateImage['photosetid1']),
                        'photoset2' => God_Model_PhotosetTable::getInstance()->find($duplicateImage['photosetid2']),
                    );
                }
                
//                _d($duplicateImage['photosetid1'], $duplicateImage['imageid1'], $duplicateImage['imageid2']);
                
                // Store the duplicate images
                $photosets[$duplicateImage['photosetid1']]['images1'][] = $duplicateImage['imageid1'];
                $photosets[$duplicateImage['photosetid1']]['images2'][] = $duplicateImage['imageid2'];
                
//                _d('Array', $photosets[$duplicateImage['photosetid1']]['duplicateImages']);
            }
            
//            _d(array('$photosets' => $photosets));
        }
        
        $this->view->duplicates = $photosets;
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
                        $counter = $this->_threeDigits( str_ireplace(".jpg", "", $files));
                        list($width,$height)=getimagesize($_SERVER['DOCUMENT_ROOT'].$path.'/'.$files);
                        $data[$counter]['uri'] = $path.'/'.$files;
                        $data[$counter]['name'] = $files;
                        $data[$counter]['width'] = $width;
                        $data[$counter]['height'] = $height;
                        $counter++;
                    } else {
                        echo '<p>'.$path.'/'.$files.' is a directory!</p>';
                    }
                }
            }
            closedir($handle);
        } else {
            return false;
        }

        ksort($data);

        return $data;
    }

    protected function _threeDigits($value) {
        return str_pad($value, 3, '0', STR_PAD_LEFT);
    }
}
