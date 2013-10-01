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
        $models = Doctrine_Core::getTable('God_Model_Model')
                    ->findBy('photosetsChecked', '< '.date("Y-m-d", strtotime("-1 day")) );

        foreach ($models as $model) {

            if ($model->isActive()) {
                $model->updatePhotosets();
            }


            if ($model->hasPhotosets() && $model->ranking == 0) {
                $model->ranking = 1;
                $model->save();
            }
        }
    }

}