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
        echo "Verifing WebCrawler Urls\r\n";

        // Get the existing links from Model Name to WebCrawler URLs
        $webCrawlerModelNameLinks = God_Model_WebCrawlerUrlModelNameTable::getInstance()
            ->createQuery('wcmn')
            ->where('model_name_id = ?', $this->ID)
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $chunks = array_chunk($webCrawlerModelNameLinks, 10);

        if ($chunks) {
            echo "Chunks - " . count($chunks) . "\r\n";

            foreach ($chunks as $chunkKey => $webCrawlerModelNameLinks) {

                echo "Chunk - " . ($chunkKey + 1) . "/" . count($chunks) . "\r\n";

                $webCrawlerUrls = array();
                $passedWebCrawlerUrls = array();
                $unlinkModelNameWebCrawlerIds = array();

                if ($webCrawlerModelNameLinks) {
                    foreach ($webCrawlerModelNameLinks as $webCrawlerModelNameLink) {
                        checkCPULoad();
                        $webCrawlerUrls[$webCrawlerModelNameLink['webcrawler_url_id']] = $webCrawlerModelNameLink['webcrawler_url_id'];
                    }
                }

                if ($webCrawlerUrls) {
                    $webCrawlerUrlsObj = God_Model_WebCrawlerUrlTable::getInstance()
                        ->createQuery('wcu')
                        ->whereIn('id', $webCrawlerUrls)
                        ->execute();
                }

                // Format the name for regular expression url matching
                $nameregex = God_Model_WebCrawlerUrlModelName::formatNameForUrlReg($this->name);

                // Check the URL is valid for the name
                if ((array)$webCrawlerUrlsObj) {
                    foreach ($webCrawlerUrlsObj as $webCrawlerUrl) {
                        checkCPULoad();
                        if (God_Model_WebCrawlerUrlModelName::checkUrlWithName($nameregex, $webCrawlerUrl->url) == false) {
                            $unlinkModelNameWebCrawlerIds[] = $webCrawlerUrl->id;
                            echo "FAIL - " . $nameregex . " - " . $webCrawlerUrl->url . "\r\n";
                            $webCrawlerUrl->promoteLinks(God_Model_WebCrawlerLink::PRIORITY_MED);
                        } else {
                            $webCrawlerUrl->promoteLinks(God_Model_WebCrawlerLink::PRIORTIY_HIGH);
                            echo "PASS - " . $nameregex . " - " . $webCrawlerUrl->url . "\r\n";
                            $passedWebCrawlerUrls[] = $webCrawlerUrl->id;
                            unset($webCrawlerUrls[$webCrawlerUrl->id]);
                        }
                    }
                }

                if ($webCrawlerUrls) {
                    $unlinkModelNameWebCrawlerIds = array_merge($unlinkModelNameWebCrawlerIds, $webCrawlerUrls);
                }

                if ($unlinkModelNameWebCrawlerIds) {
                    foreach ($webCrawlerModelNameLinks as $webCrawlerModelNameLink) {
                        checkCPULoad();
                        if (in_array($webCrawlerModelNameLink['webcrawler_url_id'], $unlinkModelNameWebCrawlerIds)) {
                            $webCrawlerModelNameLinkObj = God_Model_WebCrawlerUrlModelNameTable::getInstance()->find($webCrawlerModelNameLink['id']);
                            $webCrawlerModelNameLinkObj->delete();
                        }
                    }
                }

                if ($passedWebCrawlerUrls) {
                    $conn = Doctrine_Manager::getInstance()->connection();

                    $sql = 'UPDATE webcrawlerURL_model_name 
                            SET action = "'.($this->download ? 'Download' : 'Ask').'" 
                            WHERE model_name_id = "'.$this->ID.'" 
                            AND webcrawler_url_id in ('.implode(",", $passedWebCrawlerUrls).')';
                    $conn->execute($sql);
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

        foreach (preg_split("~[\s-]~", str_replace("'", "", $this->name)) as $namepart) {
            $webUrCrawlerUrlsQuery->andWhere('MATCH (`url`) against (?)', $namepart);
        }

        $webUrCrawlerUrlsQuery->limit(3000);
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
