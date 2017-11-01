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
        $path = APPLICATION_PATH . '/../public' . $this->path;
        $thumbnail = APPLICATION_PATH . '/../public' . $this->thumbnail;
        
        if ($this->manual_thumbnail == 1 && realpath($thumbnail) == false) {
            
            $this->manual_thumbnail = 0;
            $this->save();
            
        }
        
        if (
            strtotime($this->imagesCheckedDate) < strtotime("-1 month")
            || $this->imagesCheckedDate == "0000-00-00"
        ) {
            
            $files = God_Model_File::scanPath($path)->getFiles();

            if (!$this->thumbnail) {
                $this->thumbnail = $this->path . DIRECTORY_SEPARATOR . $files[floor(count($files)*0.6)];
                $this->save();
            }

            foreach ($files as $file) {
                
                checkCPULoad(1.7);
                
                $realpath = realpath($path.'/'.$file);
                $urlPath = str_replace(IMAGE_DIR, '', $realpath);
                
                $image = God_Model_ImageTable::getInstance()->createQuery('i')
                        ->where('filename = ?', $urlPath)
                        ->fetchOne();
                
                // Removing unwanted files
                if (in_array($file, array('.directory'))) {
                    unlink($realpath);
                    if ($image) {
                        $image->delete();
                    }
                    continue;
                }
                
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
                        $imageHash->image_id = $image->id;
                    }
                    
                    if (!$imageHash->hash) {
                        $hash = ph_dct_imagehash_to_array(ph_dct_imagehash(IMAGE_DIR . $urlPath));
                        $imageHash->hash = implode(",", $hash);
                    }
                    
                    $imageHash->save();
                    
                    
                    // Image Hash Index checking for indexes
                    $imageHashIndex = God_Model_ImageHashIndexTable::getInstance()->createQuery('ihi')
                            ->where('image_id = ?', $image->id)
                            ->fetchArray();
                    
                    if (!$imageHashIndex) {
                        foreach (explode(',', $imageHash->hash) as $index => $hash) {
                            $imageHashIndexItem = new God_Model_ImageHashIndex();
                            $imageHashIndexItem->position = (int)$index;
                            $imageHashIndexItem->hash = (int)$hash;
                            $imageHashIndexItem->image_id = (int)$image->id;
                            $imageHashIndexItem->save();
                        }
                    }

                }
            }


            $this->imagesCheckedDate = date("Y-m-d");
            $this->save();
        }
    }

}
