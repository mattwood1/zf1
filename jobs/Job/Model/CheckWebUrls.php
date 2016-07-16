<?php
/**
 * This job is responsible for Updating Models WebUrls based on the datesearched field
 *
 * Scheduled to run every minute
 */
class Job_Model_CheckWebUrls extends Job_Abstract
{
    public function run()
    {
        $modelNames = God_Model_ModelNameTable::getModelNameToBeSearched();

        foreach ($modelNames as $modelName) {
            _d($modelName->name);
            
            checkCPULoad();
            
            $webUrls = $modelName->findWebUrls();
            
            foreach ($webUrls as $webUrl) {
                
                $modelName->linkWebUrl($webUrl);
            }
            
            $modelName->datesearched = date("Y-m-d H:i:s");
            $modelName->model->datesearched = date("Y-m-d H:i:s");
            $modelName->save();
        }
    }
}
