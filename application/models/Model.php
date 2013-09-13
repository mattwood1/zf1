<?php
class God_Model_Model extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('models');

        $this->hasColumn('ID', 'integer', 11, array(
                'type'               => 'integer',
                'fixed'              => 0,
                'unsigned'           => true,
                'primary'            => true,
                'autoincrement'      => true,
                'length'             => '11',
        ));
/*
        $this->hasColumn('name', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000',
        ));
*/
        $this->hasColumn('path', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('uri', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('active', 'boolean', 25, array(
                'type'               => 'boolean',
                'length'             => '25'
        ));

        $this->hasColumn('ranking', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('date', 'date', 25, array(
                'type'               => 'date',
                'length'             => '25'
        ));

        $this->hasColumn('search', 'boolean', 25, array(
                'type'               => 'boolean',
                'length'             => '25'
        ));

        $this->hasColumn('searched', 'boolean', 25, array(
                'type'               => 'boolean',
                'length'             => '25'
        ));

        $this->hasColumn('datesearched', 'timestamp', 25, array(
                'type'               => 'timestamp',
                'length'             => '25'
        ));

        $this->hasColumn('photosetsChecked', 'date', 25, array(
                'type'               => 'date',
                'length'             => '25'
        ));


    }

    public function setUp()
    {
        $this->hasMany('God_Model_ModelName as names', array(
                'local'   =>  'ID',
                'foreign' =>  'model_id',
                //'cascade' => array('delete')
        ));

        $this->hasMany('God_Model_Photoset as photosets', array(
                'local'   =>  'ID',
                'foreign' =>  'model_id',
                //'cascade' => array('delete')
        ));

        $this->hasMany('God_Model_WebLink as webLinks', array(
                'local'   =>  'ID',
                'foreign' =>  'model_id',
                //'cascade' => array('delete')
        ));
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

    /**
     * Update photosets that are on disk
     */
    public function updatePhotosets()
    {
        if ($this->photosetsChecked != date("Y-m-d", mktime())) {
            //echo('Update Photosets');

            /*
             * ORIGINAL CODE FROM GODDESSES
             *
             * provided to the function $model_id, $path, $uri
             *
            $sql = new sql();
            if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].$path)) {
                while (false !== ($photoset = readdir($handle))) {
                    if ($photoset != "." && $photoset != "..") {        // remove '.' '..' directories
                        if (is_dir($_SERVER['DOCUMENT_ROOT'].$path.'/'.$photoset) == true) {
                            //                      echo '<p>'.$path.'/'.$photoset;

                            // Find first image
                            $imagehandle = opendir($_SERVER['DOCUMENT_ROOT'].$path.'/'.$photoset);
                            $images = array();
                            while (false !== ($image = readdir($imagehandle))) {
                                if ($image != "." && $image != "..") {      // remove '.' '..' directories
                                    if (is_file($_SERVER['DOCUMENT_ROOT'].$path.'/'.$photoset.'/'.$image) == true) {
                                        $images[] = $image;
                                    }
                                }
                            }
                            closedir($imagehandle);
                            asort($images);

                            $sql->query('SELECT * FROM `photosets` WHERE path = "'.mysql_escape_string($path).'/'.mysql_escape_string($photoset).'"');
                            if ($sql->count() == 0) {
                                $sql->query('INSERT INTO `photosets` (`model_id`, `name`, `path`, `uri`, `thumbnail`) VALUES ("'.$model_id.'", "'.mysql_escape_string($photoset).'", "'.$path.'/'.$photoset.'", "'.$uri.'/'.$photoset.'", "'.$path.'/'.$photoset.'/'.$images[0].'")');
                                //                          echo ' -> Inserted<br />';
                            } else {
                                //                          echo ' -> Exists<br />';
                                $result = $sql->result();
                                if (!file_exists( $result['thumbnail'] )) {
                                    $sql->query("UPDATE `photosets` SET `thumbnail` = '".mysql_escape_string($path).'/'.mysql_escape_string($photoset).'/'.$images[0]."' WHERE id = ".$result['id']." AND manual_thumbnail = 0");
                                }
                            }
                            echo '</p>';
                        } else {
                            echo '<p>'.$path.'/'.$photoset.' is not a directory!</p>';
                        }
                    }
                }
                closedir($handle);
            } else {
                echo 'Cannot open '.$path;
            }
            return true;
             */

            /* TODO: Futher Developement
            if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].$this->path)) {
                while (false !== ($photosetDir = readdir($handle))) {
                    if ($photosetDir != "." && $photosetDir != "..") {        // remove '.' '..' directories
                        if (is_dir($_SERVER['DOCUMENT_ROOT'].$this->path.'/'.$photosetDir) == true) {

                            // Find first image
                            $fileshandle = opendir($_SERVER['DOCUMENT_ROOT'].$this->path.'/'.$photosetDir);
                            $files = array();
                            while (false !== ($file = readdir($fileshandle))) {
                                if ($file != "." && $file != "..") {      // remove '.' '..' directories
                                    if (is_file($_SERVER['DOCUMENT_ROOT'].$this->path.'/'.$photosetDir.'/'.$file) == true) {
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
                                    $photosetFound == true;
                                }
                            }

                            if ($photosetFound == false) {

                                $this->photosets[] =

                                array(
                                        'name' => $photosetDir,
                                        'path' => $this->path.'/'.$photosetDir,
                                        'uri' => $this->uri.'/'.$photosetDir,
                                        'thumbnail' => $this->path.'/'.$photosetDir.'/'.$files[0]
                                );

                            //    $this->save();
                            }

                        } // else file is not a directory
                    }
                }
            }
            */
        }

        // Set the model photosetsChecked to today
        $this->photosetsChecked = date("Y-m-d", mktime());
        $this->save();
    }


    public function getLatestPhotoset()
    {
        $this->updatePhotosets();
        if ($this->photosets) {
            foreach ($this->photosets as $key => $photoset) {
                if (! $photoset->isActive() || ! $photoset->isManualThumb() ) {
                    unset($this->photosets[$key]);
                }
            }
        }
        $key = array_pop(array_keys($this->photosets->toArray()));
        return $this->photosets[$key];
    }

    public function getRandomPhotoset()
    {
        $this->updatePhotosets();
        if ($this->photosets) {
            $key = array_rand($this->photosets->toArray(), 1);
            $photoset = $this->photosets[$key];

            if ($photoset->active == 0) $this->getRandomPhotoset();

            return $photoset;
        }
        return null;
    }

    public function getActivePhotosets()
    {
        $this->updatePhotosets();
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