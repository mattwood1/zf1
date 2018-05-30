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
            ->where('photosetsChecked < ? OR photosetsChecked = ?', array(date("Y-m-d", strtotime("-1 day")), "0000-00-00"))
            ->andWhere('m.ranking >= ?', 0)
            ->andWhere('m.active = ?', 1)
            ->limit(20)
            ;
        
        $models = $modelsQuery->execute();
        
        foreach ($models as $model) {

            //checkCPULoad();

            echo $model->getName() . " ($model->ID)";
            if ($model->isActive()) {
                echo " - Updating\n";
                $model->updatePhotosets();
            }

            if($model->hasPhotosets() && $model->ranking == 0) {
                $model->ranking = 1;
                $model->save();
            }
        }

        // Update duplicate Hashes Cache
        God_Model_ImageHashTable::getDuplicateHashes(false, 1);
    }
}
