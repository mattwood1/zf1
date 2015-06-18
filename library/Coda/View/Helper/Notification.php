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


    protected function html($class = null)
    {
        return '<span class="fa-stack fa-lg menu-notification '.$class.'">
                    <i class="fa fa-circle fa-stack-2x text-danger"></i>
                    <i class="fa fa-exclamation fa-stack-1x fa-inverse"></i>
                </span>';

    }
}