<?php
class God_Model_ModelName extends God_Model_Base_ModelName
{
    public function findWebUrls()
    {
        $webUrlsQuery = God_Model_WebURLTable::getInstance()->createQuery('wu');
        $webUrlsQuery->where('linked <= 0');
        foreach (explode(" ", $this->name) as $namepart) {
            $webUrlsQuery->andWhere('MATCH (`url`) against (?)', "' . $namepart . '");
        }
        $webUrls = $webUrlsQuery->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        
        return $webUrls;
    }
    
    public function linkWebUrl($webUrl)
    {
        // Check Web Resource link
        $webResource = God_Model_WebResourceTable::getInstance()->findOneById($webUrl['webResourceId']);
        if (!$webResource) {
            
            $urlParts = parse_url($webUrl['url']);
            $webResource = new God_Model_WebResource;
            $webResource['website'] = str_ireplace("www.", "", $urlParts['host']);
            $webResource->save();
            
            $webUrlObj = God_Model_WebURLTable::getInstance()->find($webUrl['id']);
            $webUrlObj->webResourceId = $webResource->id;
            $webUrlObj->save();            
            
        }
        _d($webUrl['url']);
        
        
        // Check if a link exists
        $modelNameWebUrl = God_Model_ModelNameWebURLTable::getInstance()->createQuery('mnwu')
                    ->where('model_name_id = ?', $this->ID)
                    ->andWhere('webUrl_id = ?', $webUrl['id'])
                    ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        
        // Does the name fit the URL
        $modelNameUrl = strtolower(str_replace(" ", "-", $this->name));
        if (preg_match("~[/-]".$modelNameUrl."~", $webUrl['url'])) {
            
            _d(array('Name fits URL'));
            
            $webUrlObj = God_Model_WebURLTable::getInstance()->find($webUrl['id']);
            
            if (count($modelNameWebUrl) < 1) {
                $modelNameWebUrl = Doctrine_Core::getTable('God_Model_ModelNameWebURL')->create(array(
                    'model_name_id' => $this->ID,
                    'webUrl_id'     => $webUrl['id']
                ));
                $modelNameWebUrl->save();

                $webUrlObj->linked = God_Model_WebURLTable::LINK_FOUND;
                if ($webUrlObj->action < God_Model_WebURLTable::ACTION_GET_THUMBNAILS
                        || $webUrlObj->action == God_Model_WebURLTable::ACTION_THUMBNAIL_ISSUE
                ) {
                    $webUrlObj->action = God_Model_WebURLTable::ACTION_GET_THUMBNAILS;
                }
                
                $webUrlObj->save();
            }

        }
        
    }
        
}