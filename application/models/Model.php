<?php
class God_Model_Model extends God_Model_Base_Model
{
    public function addModelName($name, $default = 0)
    {
        $modelname = new God_Model_ModelName();
        $modelname->fromArray(array(
            'name' => $name,
            'model_id' => $this->ID,
            'default' => $default
        ));
        $modelname->save();
    }

    public function isActive()
    {
        if ($this->active) {
            return true;
        }
        return false;
    }

    public function hasPhotosets()
    {
        $this->refreshRelated('photosets');
        if ( count($this->photosets) ) {
            return true;
        }
        return false;
    }

    /**
     * Returns the Models default name
     * @return string
     */
    public function getName()
    {
        foreach ($this->names as $key => $name){
            if ($name->default == 1){
                return $this->names[$key]->name;
            }
        }
        return $this->names[0]->name . '(No Default)';
    }
    
    public static function getPrimaryName($modelID)
    {
        $model = God_Model_ModelTable::getInstance()->find($modelID);
        return $model->getName();
    }

    /**
     * Returns the Models Aliases
     * @return God_Model_Model->names[]
     */
    public function getAliases()
    {
        foreach ($this->names as $key => $name){
            if ($name->default == 1){
                unset ($this->names[$key]);
            }
        }
        return $this->names;
    }
    
    public function updatePhotosets()
    {
        $path = APPLICATION_PATH . '/../public' . $this->path;

        foreach (God_Model_File::scanPath($path)->getDirectories() as $directory) {

            checkCPULoad();

            // Query for photoset
            $photosetFound = false;
            if ($photoset = God_Model_PhotosetTable::getInstance()->findOneBy('path', $this->path . DIRECTORY_SEPARATOR . $directory)) {
                $photosetFound = true;
                $photoset->updateImages();
            }

            if ( $photosetFound == false && is_array( $files = God_Model_File::scanPath($path . DIRECTORY_SEPARATOR . $directory)->getFiles() ) ) {

                $photoset = new God_Model_Photoset();
                $photoset->fromArray(array(
                    'name' => $directory,
                    'path' => $this->path . DIRECTORY_SEPARATOR . $directory,
                    'uri' => $this->uri . DIRECTORY_SEPARATOR . $directory,
                    'thumbnail' => $this->path . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $files[floor(count($files)*0.6)]
                ));

                $photoset->link('model', array($this->ID));
                var_dump('Created gallery ' . $photoset->name, $files);                
                $photoset->save();
                
                $photoset->updateImages();
            }  
        }

        $this->photosetsChecked = date("Y-m-d", mktime());
        
        $this->save();

    }

    /**
     * Update photosets that are on disk
     *
     * Run by a CRON job
     */
    public function updatePhotosetsOld()
    {
        if ($this->photosetsChecked != date("Y-m-d", mktime())) {
            if ($handle = opendir(APPLICATION_PATH.'/../public'.$this->path)) {
                while (false !== ($photosetDir = readdir($handle))) {
                    if ($photosetDir != "." && $photosetDir != "..") {        // remove '.' '..' directories
                        if (is_dir(APPLICATION_PATH.'/../public'.$this->path.'/'.$photosetDir) == true) {

                            // Find first image
                            $fileshandle = opendir(APPLICATION_PATH.'/../public'.$this->path.'/'.$photosetDir);
                            $files = array();
                            while (false !== ($file = readdir($fileshandle))) {
                                if ($file != "." && $file != "..") {      // remove '.' '..' directories
                                    if (is_file(APPLICATION_PATH.'/../public'.$this->path.'/'.$photosetDir.'/'.$file) == true) {
                                        $files[] = $file;
                                    }
                                }
                            }

                            closedir($fileshandle);
                            asort($files);

                            // Query for photoset
                            $photosetFound = false;
                            foreach ($this->photosets as $photoset) {
                                if ($photoset->path == $this->path.'/'.$photosetDir) {
                                    $photosetFound = true;
                                }
                            }

                            if ($photosetFound == false && is_array($files)) {
                                $photosetObj = new God_Model_Photoset();
                                $photosetObj['model_id'] = $this->ID;
                                $photosetObj['name'] = $photosetDir;
                                $photosetObj['path'] = $this->path.'/'.$photosetDir;
                                $photosetObj['uri'] = $this->uri.'/'.$photosetDir;
                                $photosetObj['thumbnail'] = $this->path.'/'.$photosetDir.'/'.$files[0];
                                $photosetObj->save();

                            }

                        } // else file is not a directory
                    }
                } // End While
            }
        }

        // Set the model photosetsChecked to today
        $this->photosetsChecked = date("Y-m-d", mktime());
        $this->save();
    }

    public function createPhotoset()
    {
        $file = new God_Model_File(PUBLIC_PATH . $this->path);

        // Filter numbers only
        $dirs = $file->getDirectories();
        foreach ($dirs as $key => $dir) {
            if (!preg_match("~\d+~", $dir)) {
                unset($dirs[$key]);
            }
        }
        $maxDir = max($dirs);
        $newDir = str_pad($maxDir+1, 3, "0", STR_PAD_LEFT);

        $targetPath = PUBLIC_PATH . $this->path . DIRECTORY_SEPARATOR . $newDir;
        God_Model_File::createPath($targetPath);

        $photoset = new God_Model_Photoset();
        $photoset->fromArray(array(
            'model_id' => $this->ID,
            'name' => $newDir,
            'path' => $this->path . DIRECTORY_SEPARATOR . $newDir,
            'uri' => $this->uri . DIRECTORY_SEPARATOR . $newDir,
        ));
        $photoset->save();

        return $photoset;
    }

    public function getLatestPhotoset()
    {
        $keys = array();
        if ($this->photosets) {
            foreach ($this->photosets as $key => $photoset) {
                if ($photoset->isActive() && $photoset->isManualThumb() ) {
                    $keys[] = $key;
                }
            }
        }
        
        $key = array_pop($keys);
        return $this->photosets[$key];
    }

    public static function getRandomPhotoset($modelID)
    {
        $model = God_Model_ModelTable::getInstance()->find($modelID);
        if ($model->photosets) { // TODO: Needs to be $this->photosets->getActive(), replaces foreach below
            
            $photosets = $model->photosets;
            $photosetKeys = array();
            
            foreach ($photosets as $photosetKey => $photoset) {
                if ($photoset->active == 0 || $photoset->manual_thumbnail == 0) {
                    unset($photosets[$photosetKey]);
                } else {
                    $photosetKeys[$photosetKey] = $photosetKey;
                }
            }
            $key = array_rand($photosetKeys, 1);

            return $model->photosets[$key];
        }
        return null;
    }

    public function getActivePhotosets() // Makes this redundant
    {
        if ($this->photosets) {
            foreach ($this->photosets as $key => $photoset) {
                if ($photoset->active == 0) {
                    unset($this->photoset[$key]); // remove non active photosets
                }
            }
        }
        return $this->photosets;
    }

    public function getWebLinkStats()
    {
        $weblinks = $this->webLinks;

        return $weblinks;
    }
    

/*
    public function getModelName()
    {
        $modelnames = new Application_Model_DbTable_ModelNames();
        return $modelnames->getModelNameByModel($this);
    }

    public function getModelAlias()
    {
        $modelnames = new Application_Model_DbTable_ModelNames();
        return $modelnames->getModelAliasByModel($this);
    }

    public function getModelPhotosets()
    {
        $photosets = new Application_Model_DbTable_Photosets();
        return $photosets->getModelPhotosets($this);
    }

    public function getModelLatestPhotoset()
    {
        $photoset = new Application_Model_DbTable_Photosets();
        return $photoset->getModelLatestPhotoset($this);
    }
*/
}
