<?php
/**
 * This job is responsible for Updating Models Photosets that are out of date.
 */
class Job_Model_UpdatePhotosets extends Job_Abstract
{
    public function run()
    {
        echo 'Update Photosets job entered';

        $models = Doctrine_Core::getTable('God_Model_Model')
                    ->findBy('photosetsChecked', '< '.date("Y-m-d", strtotime("-1 day")) );

        foreach ($models as $model) {
            if ($model->isActive()) {
                $model->updatePhotosets();
            }
        }
    }

}