<?php
class God_Model_WebURL extends God_Model_Base_WebURL
{
    public function linkModelNameToUrl()
    {
        // Link Model Name
        $modelNameTable = new God_Model_ModelNameTable();
        $modelNames = $modelNameTable->getActiveModelNames();
        
        foreach ($modelNames as $modelName) {
            $name = str_replace(" ", "[\s\-\_]", $modelName['name']);

            if (preg_match("~((?:[\-\/])" . $name . "(?:[\-\/\_\.])?)~i", $this->url)) { // ~(" . $name . ")~i is pants
                $this->linked = God_Model_WebURLTable::LINK_FOUND;            // Name Match found
                $this->action = God_Model_WebURLTable::ACTION_GET_THUMBNAILS; // Set to get thumbs

                // Check link exists
                $modelNameWebUrl = God_Model_ModelNameWebURLTable::getInstance()->createQuery('mnwu')
                        ->where('model_name_id = ?', $modelName['ID'])
                        ->andWhere('webUrl_id = ?', $this->id)
                        ->fetchOne();
                if (!$modelNameWebUrl) {
                    $modelNameWebUrl = God_Model_ModelNameWebURLTable::getInstance()->create(array(
                            'model_name_id' => $modelName['ID'],
                            'webUrl_id'     => $this->id
                    ));
                }
                $modelNameWebUrl->save();
            }
        }
    }
    
    public function getThumbnails()
    {
        $webURLTable = new God_Model_WebURLTable;
        $webResource = God_Model_WebResourceTable::getInstance()->findOneBy('id', $this->webResourceId);

        $links = array();
        $allLinks = array();
        $imageLinks = '';
        
        $curl = new God_Model_Curl();
        $html = $curl->Curl($this->url, null, false, 5, true); // Follow redirects
        
        $this->httpStatusCode = $curl->statusCode();
        $this->dateUpdated = date("Y-m-d H-i-s", time());
        
        if ($this->httpStatusCode == 0 || $this->httpStatusCode == 404) {
            $this->action = God_Model_WebURLTable::ACTION_DISCARDED;
            $this->save();
            return;
        } else {
            $this->save();
        }
        
        if ($webResource) {
            if ($this->url != $curl->lastUrl()) {
                echo "Redirect occured.\n\n";
                $this->action = God_Model_WebURLTable::ACTION_DISCARDED;
                $this->save();
                $newWebUrl = $webURLTable->insertLink($curl->lastUrl(), $webResource);
                $newWebUrl->dateCreated = $this->dateCreated;
                $newWebUrl->save();
                $this->linked = $newWebUrl->id;
            } else {
                if ($webResource->xpathfilter) {
                    $domXPath = new God_Model_DomXPath($html);
                    $links = $domXPath->evaluate($webResource->xpathfilter);
                    $imageXPath = preg_replace(array('~//img~', '~/img~'), array('', ''), $webResource->xpathfilter);
                    $imageLinks = $domXPath->evaluate($imageXPath);
                    $allLinks = $domXPath->evaluate("//a");
                }
            }
        }

        $imageLinkHref = array();
        $allLinkHref = array();
        if ($imageLinks) {
            foreach ($imageLinks as $imageLink) {
                $imageLinkHref[] = $imageLink['href'];
            }
        }

        if ($allLinks) {
            foreach ($allLinks as $allLink) {
                $allLinkHref[] = $allLink['href'];
            }
        }
        
        if ($links) {
            // Split them - Old code millions already done...
            $img = array();
            $href = array();
            foreach ($links as $link) {
                $img[] = $link['img'];
                $href[] = $link['href'];
            }

            $this->thumbnails = serialize($img);
            $this->links = serialize($href);
            $this->action = God_Model_WebURLTable::ACTION_GOT_THUMBNAILS;
        } else {
            // Mark the webUrl as bad
            $this->action = God_Model_WebURLTable::ACTION_THUMBNAIL_ISSUE;
        }
        
        $this->save();
    }
}