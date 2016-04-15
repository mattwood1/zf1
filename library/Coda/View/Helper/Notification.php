<?php
class Coda_View_Helper_Notification extends Zend_View_Helper_Abstract
{
    public function notification($type = null)
    {
        if (!$type) {
            return $this->checkAll();
        } else {
            switch ($type) {
                case 'thumbnail':
                    if ($this->checkThumbnail()) return $this->html();
                    return false;
                    break;
                case 'duplicate':
                    if ($this->checkDuplicate()) return $this->html();
                    return false;
                    break;
            }
        }
    }
    
    protected function checkAll()
    {
        $notification = false;
        
        if ($this->checkThumbnail()) $notification = true;
        
        if ($notification) return $this->html('large');
        
        return false;
    }
    
    protected function checkThumbnail()
    {
        $photosetTable = new God_Model_PhotosetTable();
        $query = $photosetTable->getThumbnails();
        $rows = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        if (count($rows)) {
            return true;
        }
        return false;
    }
    
    protected function checkDuplicate()
    {
        // Adding caching
        $cache = Zend_Cache::factory('Core', 'Memcached');
        $cachekey = "DuplicateImages";

        $pretestResults = $cache->load($cachekey);
        
        if (!$pretestResults) {
        
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
            $pretestResults = serialize($pretest->fetchAll());
            
            $cache->save($pretestResults, $cachekey);
        }
        
        if (unserialize($pretestResults)) {
            return true;
        }
        return false;
    }

        protected function html($class = null)
    {
        return '<span class="fa-stack fa-lg menu-notification '.$class.'">
                    <i class="fa fa-circle fa-stack-2x text-danger"></i>
                    <i class="fa fa-exclamation fa-stack-1x fa-inverse"></i>
                </span>';

    }
}