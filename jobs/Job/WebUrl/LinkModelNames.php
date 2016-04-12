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
//        $this->cleanup();
        
        $webUrlsTable = new God_Model_WebURLTable;
        $webUrlsQuery = $webUrlsTable->getInstance()
            ->createQuery('wu')
            ->where('linked = ?', God_Model_WebURLTable::LINK_TO_BE_LINKED)
            ->limit(1500);
        $webUrls = $webUrlsQuery->execute();
        
        foreach ($webUrls as $webUrl) {
            
            $webUrl->linked = God_Model_WebURLTable::LINK_ATTEMPTED; // Processed by this script

            $webUrl->linkModelNameToUrl();

            $webUrl->save();

        }
    }
    
    protected function cleanup()
    {
        $conn = Doctrine_Manager::getInstance()->connection();
        
        $duplicates = $conn->execute('
            SELECT mnwu2.id 
            FROM `model_name_webUrls` mnwu1 
            join model_name_webUrls mnwu2 on (
                mnwu1.model_name_id = mnwu2.model_name_id 
                AND mnwu1.webUrl_id = mnwu2.webUrl_id 
                AND mnwu1.id != mnwu2.id
            )
            limit 100');
        $duplicatesResults = $duplicates->fetchAll();
        
        if ($duplicatesResults) {
            $removeIds = array();
            foreach ($duplicatesResults as $duplicatesResult) {
                $removeIds[] = $duplicatesResult['id'];
            }
                
            $conn->execute('
                DELETE FROM `model_name_webUrls` 
                WHERE `model_name_webUrls`.`id` in (' . implode(',', $removeIds) . ')');
        }
    }
}