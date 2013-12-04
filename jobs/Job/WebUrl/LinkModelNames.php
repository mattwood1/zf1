<?php
/**
 * This job is responsible for Importing WebLinks into WebUrls for better management.
 *
 * Scheduled to run every minute every day.
 */
class Job_WebUrl_LinkModelNames extends Job_Abstract
{
    public function run()
    {
        $webUrlsTable = new God_Model_WebURLTable;
        $webUrlsQuery = $webUrlsTable->getInstance()
            ->createQuery('wu')
            ->where('linked = ?', -1)
            ->limit(50);
        $webUrls = $webUrlsQuery->execute();

        foreach ($webUrls as $webUrl) {
            //var_dump($webUrl->url);
            $webUrl->linked = -2;                        // Processed by this script

            $modelNameTable = new God_Model_ModelNameTable;
            $modelNames = $modelNameTable->getInstance()->findAll();

            foreach ($modelNames as $modelName) {
                //var_dump(($modelName->name));
                $name = str_replace(" ", "[\s\-\_]", $modelName->name);

                if (preg_match("~(" . $name . ")~i", $webUrl->url)) {
                    $webUrl->linked = -5;                // Name Match found
                    $modelNamewebUrl = God_Model_ModelNameWebURLTable::getInstance()->createQuery('mnwu')
                        ->where('model_name_id = ?', $modelName->ID)
                        ->andWhere('webUrl_id = ?', $webUrl->id)
                        ->execute();
                    if (!$modelNamewebUrl->toArray()) {
                        $modelNamewebUrl = Doctrine_Core::getTable('God_Model_ModelNameWebURL')->create(array(
                                'model_name_id' => $modelName->ID,
                                'webUrl_id'     => $webUrl->id
                        ));
                        $modelNamewebUrl->save();
                    }
                }
            }

            $webUrl->save();

        }
    }
}