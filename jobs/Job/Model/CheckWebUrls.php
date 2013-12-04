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
        $modelTable = new God_Model_ModelTable;
        $modelsQuery = $modelTable->getInstance()
            ->createQuery('m')
            ->where('datesearched < ?', date("Y-m-d", strtotime("-7 days")) )
            ->andWhere('active = ?', 1)
            ->andWhere('search = ?', 1)
            ->andWhere('ranking > ?', 0)
            ->orderBy('datesearched asc')
            ->limit(1);
        $models = $modelsQuery->execute();

        foreach ($models as $model) {
// var_dump($model->toArray());
            foreach ($model->names as $modelName) {
// var_dump($modelName->toArray());
                foreach ($modelName->webUrls as $modelNameWeburls) {
// var_dump($modelNameWeburls->toArray());
                    foreach ($modelNameWeburls->webUrl as $modelNameWebUrl) {
                        var_dump($modelNameWebUrl->toArray());
                    }
                    var_dump(count($modelNameWeburls->webUrl->toArray()));
                }
            }
        }
    }
}