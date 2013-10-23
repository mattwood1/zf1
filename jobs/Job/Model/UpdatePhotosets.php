<?php
/**
 * This job is responsible for Updating Models Photosets that are out of date.
 *
 * Scheduled to run at 1am every day
 */
class Job_Model_UpdatePhotosets extends Job_Abstract
{
    public function run()
    {
        $modelTable = new God_Model_ModelTable;
        $modelsQuery = $modelTable->getInstance()
            ->createQuery('m')
            ->where('photosetsChecked < ?', date("Y-m-d", strtotime("-1 day")));
        $models = $modelsQuery->execute();

        foreach ($models as $model) {
            if ($model->isActive()) {
                $model->updatePhotosets();
            }
        }
    }
}