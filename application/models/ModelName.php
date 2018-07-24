<?php
class God_Model_ModelName extends God_Model_Base_ModelName
{
    public function findWebUrls()
    {
        $webUrlsQuery = God_Model_WebURLTable::getInstance()->createQuery('wu');
        $webUrlsQuery->where('linked <= 0');
        foreach (explode(" ", $this->name) as $namepart) {
            $webUrlsQuery->andWhere('MATCH (`url`) against (?)', "' . $namepart . '");
            //$webUrlsQuery->andWhere('url like ?', '%' . $namepart .'%');
        }
        $webUrls = $webUrlsQuery->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        return $webUrls;
    }

    /**
     * @throws Doctrine_Query_Exception
     */
    public function verifyWebCrawlerUrls()
    {
        // Get the existing links from Model Name to WebCrawler URLs
        $webCrawlerModelNameLinks = God_Model_WebCrawlerUrlModelNameTable::getInstance()
            ->createQuery('wcmn')
            ->where('model_name_id = ?', $this->ID)
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $webCrawlerUrls = array();
        $unlinkModelNameWebCrawlerIds = array();

        if ($webCrawlerModelNameLinks) {
            foreach ($webCrawlerModelNameLinks as $webCrawlerModelNameLink) {
                $webCrawlerUrls[] = $webCrawlerModelNameLink['id'];
            }
        }

        if ($webCrawlerUrls) {
            $webCrawlerUrlsObj = God_Model_WebCrawlerUrlTable::getInstance()
                ->createQuery('wcu')
                ->whereIn('id', $webCrawlerUrls)
                ->execute();
        }

        // Format the name
        $name = God_Model_WebCrawlerUrlModelName::formatNameForUrlReg($this->name);

        // Check the URL is valid for the name
        if ((array)$webCrawlerUrlsObj) {
            foreach ($webCrawlerUrlsObj as $webCrawlerUrl) {
                if (God_Model_WebCrawlerUrlModelName::checkUrlWithName($name, $webCrawlerUrl->url) == false) {
                    $unlinkModelNameWebCrawlerIds[] = $webCrawlerUrl->id;
                }
                unset($webCrawlerUrls[$webCrawlerUrl->id]);
            }
        }

        if ($webCrawlerUrls) {
            $unlinkModelNameWebCrawlerIds = array_merge($unlinkModelNameWebCrawlerIds, $webCrawlerUrls);
        }

        if ($unlinkModelNameWebCrawlerIds) {
            foreach ($webCrawlerModelNameLinks as $webCrawlerModelNameLink) {
                if (in_array($webCrawlerModelNameLink['webcrawler_url_id'], $unlinkModelNameWebCrawlerIds)) {
                    $webCrawlerModelNameLinkObj = God_Model_WebCrawlerUrlModelNameTable::getInstance()->find($webCrawlerModelNameLink['id']);
                    $webCrawlerModelNameLinkObj->delete();
                }
            }
        }
    }

    public function linkWebCrawlerUrls()
    {
        $webCrawlerModelNameLinks = God_Model_WebCrawlerUrlModelNameTable::getInstance()
            ->createQuery('wcmn')
            ->where('model_name_id = ?', $this->ID)
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $alreadyLinked = array();
        if ($webCrawlerModelNameLinks) {
            foreach ($webCrawlerModelNameLinks as $webCrawlerModelNameLink) {
                $alreadyLinked[] = $webCrawlerModelNameLink['webcrawler_url_id'];
            }
        }

        $webUrCrawlerUrlsQuery = God_Model_WebCrawlerUrlTable::getInstance()
            ->createQuery('wcu')
            ->Where('contenttype like "%text/html%"')
            ->andWhere('statuscode = 200');

        if ($alreadyLinked) {
            $webUrCrawlerUrlsQuery->andWhereNotIn('id', $alreadyLinked);
        }

        foreach (explode(" ", $this->name) as $namepart) {
            $webUrCrawlerUrlsQuery->andWhere('MATCH (`url`) against (?)', $namepart);
            //$webUrCrawlerUrlsQuery->andWhere('url like ?', '%' . $namepart . '%');
        }

        $webCrawlerUrls = $webUrCrawlerUrlsQuery->execute();

        if ((array)$webCrawlerUrls) {
            foreach ($webCrawlerUrls as $url) {

                checkCPULoad();

                God_Model_WebCrawlerUrlModelName::createLink($url, array($this->ID => $this->name));
            }
        }
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
