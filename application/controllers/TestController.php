<?php
/**
 * Test Controller for testing scripts
 */
class TestController extends Coda_Controller
{
    public function directoryScanAction()
    {
        _d('directory scan');
        
        $path = APPLICATION_PATH . '/../public/Women/Ariel';
        
        $directories = new God_Model_File($path);
        
        foreach ($directories->getDirectories() as $directory) {
            
            $files = new God_Model_File($path .'/'.$directory);
            
            _d($files->getFiles());
            
        }
        exit;
    }
    
    public function updatePhotosetsAction() {
        
        $modelTable = new God_Model_ModelTable;
        $modelsQuery = $modelTable->getInstance()
            ->createQuery('m')
            ->leftJoin('m.photosets p')
            //->where('photosetsChecked < ?', date("Y-m-d", strtotime("today")))
            //->orWhere('photosetsChecked = ?', "0000-00-00")
            ->andWhere('m.ranking >= ?', 0)
            ->andWhere('p.active = ?', 1)
                
            ->limit(1);
        $models = $modelsQuery->execute();
        
        foreach ($models as $model) {
            _d( $model->getName() );
            
            if ($model->isActive()) {
//                $model->updatePhotosets();
                
                _d('Update Photosets');
                
                $path = APPLICATION_PATH . '/../public' . $model->path;
        
                $directories = new God_Model_File($path);

                foreach ($directories->getDirectories() as $directory) {
                    
                    $files = new God_Model_File($path .'/'.$directory);

                    // Query for photoset
                    $photosetFound = false;
                    foreach ($model->photosets as $photoset) {
                        if ($photoset->path == $model->path.'/'.$directory) {
                            $photosetFound = true;
                        }
                    }
                    
                    if ($photosetFound == false && is_array($files)) {
                        
                        $model->photosets[]->fromArray(array(
                            'name' => $directory,
                            'path' => $model->path.'/'.$directory,
                            'uri' => $model->uri.'/'.$directory,
                            'thumbnail' => $model->path.'/'.$directory.'/'.$files[0]
                        ));
                        
                        $model->save();

                    }
                    
                    /*
                    $files = new God_Model_File($path .'/'.$directory);

                    foreach ($files->getFiles() as $file) {
                        $filepath = $path.'/'.$directory.'/'.$file;
                        
                        _d($filepath);
                        
                        $hash = ph_dct_imagehash_to_array(ph_dct_imagehash($filepath));
                        
                        _d(implode(",", $hash));
                        
                    }
                    */

                }
                
            }
            
            exit;
            
        }
        exit;
    }
}