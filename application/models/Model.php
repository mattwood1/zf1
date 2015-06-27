<?php
class God_Model_Model extends God_Model_Base_Model
{
    public function isActive()
    {
        if ($this->active) {
            return true;
        }
        return false;
    }

    public function hasPhotosets()
    {
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
            
            // Query for photoset
            $photosetFound = false;
            foreach ( $this->photosets as $photoset ) {
                if ( $photoset->path == $this->path . '/' . $directory ) {
                    $photosetFound = true;
                }
            }

            if ( $photosetFound == false && is_array( $files = God_Model_File::scanPath($path . '/' . $directory)->getFiles() ) ) {

                $photoset = new God_Model_Photoset();
                $photoset->fromArray(array(
                    'name' => $directory,
                    'path' => $this->path . '/' . $directory,
                    'uri' => $this->uri . '/' . $directory,
                    'thumbnail' => $this->path . '/' . $directory . '/' . $files[floor(count($files)*0.6)]
                ));

                $photoset->link('model', array($this->ID));
                $photoset->save();
                
                $photoset->updateImages();
            }
            
            /**
             * Image updates, maybe better in photosets
             */
            /*
            foreach ( $this->photosets as $photoset ) {
                
            }
            */
        }
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

    public function getRandomPhotoset()
    {
        if ($this->photosets) { // TODO: Needs to be $this->photosets->getActive()
            
            $photosets = clone($this->photosets);
            $photosetKeys = array();
            
            foreach ($photosets as $photosetKey => $photoset) {
                if ($photoset->active == 0) {
                    unset($photosets[$photosetKey]);
                } else {
                    $photosetKeys[$photosetKey] = $photosetKey;
                }
            }
            $key = array_rand($photosetKeys, 1);

            return $this->photosets[$key];
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
