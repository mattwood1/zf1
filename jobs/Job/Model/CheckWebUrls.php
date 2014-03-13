<?php
/**
 * This job is responsible for Updating Models WebUrls based on the datesearched field
 *
 * Scheduled to run at 1am every day
 */
class Job_Model_CheckWebUrls extends Job_Abstract
{
    public function run()
    {
        $modelNameTable = new God_Model_ModelNameTable;
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
var_dump($modelName->toArray());
/*
            foreach ($model->names as $modelName) {
// var_dump($modelName->toArray());
                foreach ($modelName->webUrls as $modelNameWeburls) {
// var_dump($modelNameWeburls->toArray());
                    foreach ($modelNameWeburls->webUrl as $modelNameWebUrl) {
//                        var_dump($modelNameWebUrl->toArray());
                    }
//                    var_dump(count($modelNameWeburls->webUrl->toArray()));
                }
            }
*/
        }
    }
}