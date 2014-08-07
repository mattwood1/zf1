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
            ->where('mn.datesearched < ?', date("Y-m-d", strtotime("-1 week")) )
            ->leftJoin('mn.model m')
            ->andWhere('m.active = ?', 1)
            ->andWhere('m.search = ?', 1)
            ->andWhere('m.ranking > ?', 0)
            ->orderBy('mn.datesearched asc')
            ->limit(10);
        $modelNames = $modelNamesQuery->execute();

        foreach ($modelNames as $modelName) {
            _d($modelName->name);
            $webUrlsTable = new God_Model_WebURLTable();
            $webUrlsQuery = $webUrlsTable->getInstance()
                ->createQuery('wu');
            //$webUrlsQuery->where('linked != ?', God_Model_WebURLTable::LINK_FOUND);
            foreach (explode(" ", $modelName->name) as $namepart) {
                $webUrlsQuery->andWhere('MATCH (`url`) against ("' . $namepart . '")');
            }
            $webUrls = $webUrlsQuery->execute();
            
            foreach ($webUrls as $webUrl) {
                $webResouceTable = new God_Model_WebResourceTable();

                $webResource = God_Model_WebResourceTable::getInstance()->findOneById($webUrl->webResourceId);
                if (!$webResource) {
                    _dexit($webUrl->url, $webUrl->webResourceId);
                }
                _d($webUrl->url);    
                
                $modelNameWebUrl = God_Model_ModelNameWebURLTable::getInstance()->createQuery('mnwu')
                    ->where('model_name_id = ?', $modelName->ID)
                    ->andWhere('webUrl_id = ?', $webUrl->id)
                    ->execute();
                if (count($modelNameWebUrl) == 0) {
                    $modelNameWebUrl = Doctrine_Core::getTable('God_Model_ModelNameWebURL')->create(array(
                        'model_name_id' => $modelName->ID,
                        'webUrl_id'     => $webUrl->id
                    ));
                    $modelNameWebUrl->save();
                }
                $webUrl->linked = God_Model_WebURLTable::LINK_FOUND;
                $webUrl->save();
            }
            
            $modelName->datesearched = date("Y-m-d H:i:s");
            $modelName->model->datesearched = date("Y-m-d H:i:s");
            $modelName->save();
        }
    }
}