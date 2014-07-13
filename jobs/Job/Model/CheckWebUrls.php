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
        $modelNameTable = new God_Model_ModelNameTable();
        $modelNamesQuery = $modelNameTable->getInstance()
            ->createQuery('mn')
            ->where('mn.datesearched < ?', date("Y-m-d", strtotime("-1 month")) )
            ->leftJoin('mn.model m')
            ->andWhere('m.active = ?', 1)
            ->andWhere('m.search = ?', 1)
            ->andWhere('m.ranking > ?', 0)
            ->orderBy('mn.datesearched asc')
            ->limit(1);
        $modelNames = $modelNamesQuery->execute();

        foreach ($modelNames as $modelName) {
            _d($modelName);
            $webUrlsTable = new God_Model_WebURLTable();
            $webUrlsQuery = $webUrlsTable->getInstance()
                ->createQuery('wu');
            foreach (explode(" ", $modelName->name) as $namepart) {
                $webUrlsQuery->where('MATCH (`url`) against ("' . $namepart . '")');
            }
            $webUrls = $webUrlsQuery->execute();
            
            foreach ($webUrls as $webUrl) {
                $webResouceTable = new God_Model_WebResourceTable();

                $webResource = God_Model_WebResourceTable::getInstance()->findOneById($webUrl->webResourceId);
                if (!$webResource) {
                    _dexit($webUrl->url, $webUrl->webResourceId);
                }
                        
                $webUrlsTable->insertLink($webUrl->url, $webResource);
            }
            
            $modelName->datesearched = date("Y-m-d H:i:s");
            $modelName->model->datesearched = date("Y-m-d H:i:s");
            $modelName->save();
        }
    }
}