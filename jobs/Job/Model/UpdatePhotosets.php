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
            ->leftJoin('m.photosets p')
            ->where('photosetsChecked < ?', date("Y-m-d", strtotime("-1 day")))
            ->orWhere('photosetsChecked = ?', "0000-00-00")
            ->andWhere('ranking >= ?', 0)
            ->andWhere('p.active = ?', 1);
        $models = $modelsQuery->execute();

        foreach ($models as $model) {
            if ($model->isActive()) {
                $model->updatePhotosets();
            }

            if($model->hasPhotosets() && $model->ranking == 0) {
                $model->ranking = 1;
                $model->save();
            }
        }
    }
}
