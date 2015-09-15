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
        if (
            strtotime($this->imagesCheckedDate) < strtotime("-1 month")
            || $this->imagesCheckedDate == "0000-00-00"
        ) {

            $path = APPLICATION_PATH . '/../public' . $this->path;
            $files = God_Model_File::scanPath($path)->getFiles();

            foreach ($files as $file) {

                $realpath = realpath($path.'/'.$file);
                $urlPath = str_replace(IMAGE_DIR, '', $realpath);
                
                $image = God_Model_ImageTable::getInstance()->createQuery('i')
                        ->where('filename = ?', $urlPath)
                        ->fetchOne();
                
                if (!$image) {
                    $image = new God_Model_Image();
                }
                
                $imageInfo = getimagesize(IMAGE_DIR . $urlPath);
                        
                if ($imageInfo[0] && $imageInfo[1]) {
                
                    $imageData = array(
                        'photoset_id' => $this->id,
                        'width' => $imageInfo[0],
                        'height' => $imageInfo[1],
                        'bits' => $imageInfo['bits'],
                        'channels' => $imageInfo['channels'],
                        'mime' => $imageInfo['mime'],
                        'filename' => $urlPath                        
                    );

                    $image->fromArray($imageData);                
                    $image->save();

                    $imageHash = God_Model_ImageHashTable::getInstance()->createQuery('ih')
                            ->where('image_id = ?', $image->id)
                            ->fetchOne();

                    // No point re-hashing an image that hasn't changed.
                    if (!$imageHash) {
                        $imageHash = new God_Model_ImageHash();

                        $hash = ph_dct_imagehash_to_array(ph_dct_imagehash(IMAGE_DIR . $urlPath));

                        $imageHash->fromArray(array(
                            'hash' => implode(",", $hash),
                            'image_id' => $image->id
                        ));

                        $imageHash->save();
                    }
                }
            }
            
            $this->imagesCheckedDate = date("Y-m-d");
            $this->save();
        }
    }

}