<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_LinkModelNames extends Job_Abstract
{
    public function run()
    {
        $modelNameTable = new God_Model_ModelNameTable();
        $modelNames = $modelNameTable->getActiveModelNames();

//        var_dump($modelNames);

        /*
         * Create a quick array of the model names, processed modelNameID => model_name
         */

        $names = array();
        foreach ($modelNames as $modelName) {
            $names[$modelName['ID']] = strtolower(str_replace(" ", "-", $modelName['name']));
        }
        $names = array_filter($names);


        var_dump($names, count($names));
    }
}