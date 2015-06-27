<?php
class God_Model_Photoset extends God_Model_Base_Photoset
{
    public function isActive()
    {
        if ($this->active) {
            return true;
        }
        return false;
    }

    public function isManualThumb()
    {
        if ($this->manual_thumbnail) {
            return true;
        }
        return false;
    }
    
    public function updateImages()
    {
        return;
        
        if (
            strtotime($photoset->imagesCheckedDate) < strtotime("-1 month")
            || $photoset->imagesCheckedDate = "0000-00-00"
        ) {

            $files = God_Model_File::scanPath($path .'/'.$directory)->getFiles();

            foreach ($files->getFiles() as $file) {

                $filepath = $path.'/'.$directory.'/'.$file;

                _d($filepath);

                $hash = ph_dct_imagehash_to_array(ph_dct_imagehash($filepath));

                _d(implode(",", $hash));
            }

        }
    }

}