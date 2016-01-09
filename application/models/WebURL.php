<?php
class God_Model_WebURL extends God_Model_Base_WebURL
{
    public function linkModelNameToUrl()
    {
        // Link Model Name
        $modelNames = God_Model_ModelNameTable::getInstance()->getActiveModelNames();

        foreach ($modelNames as $modelName) {
            $name = str_replace(" ", "[\s\-\_]", $modelName->name);

            if (preg_match("~((?:[\-\/])" . $name . "(?:[\-\/\_\.])?)~i", $this->url)) { // ~(" . $name . ")~i is pants
                $this->linked = God_Model_WebURLTable::LINK_FOUND;            // Name Match found
                $this->action = God_Model_WebURLTable::ACTION_GET_THUMBNAILS; // Set to get thumbs

                $modelNameWebUrl = God_Model_ModelNameWebURLTable::getInstance()->create(array(
                        'model_name_id' => $modelName->ID,
                        'webUrl_id'     => $this->id
                ));
                $modelNameWebUrl->save();
            }
        }
    }
}