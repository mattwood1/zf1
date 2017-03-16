<?php

/**
 * Class Job_Model_CheckWebUrls
 *
 * This Job is to create new models that are found in the filesystem
 *
 * Runs at 23:00 before the photosets run.
 */
class Job_Model_UpdateModelsFromFilesystem extends Job_Abstract
{
    public function run()
    {
        $modelTable = new God_Model_ModelTable;
        $modelTable->getModelsFromFilesystem();
    }
}
