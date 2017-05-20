<?php
class God_Model_WebCrawlerUrlModelName extends God_Model_Base_WebCrawlerUrlModelName
{
    public static function createLink(God_Model_WebCrawlerUrl $url)
    {
        // Only link html pages not other content types
        if (stripos($url->contenttype, "text/html") === false || $url->statuscode != 200) return;

        /*
         * Create a quick array of the model names, processed modelNameID => model_name
         */

        $modelNameTable = new God_Model_ModelNameTable();
        $modelNames = $modelNameTable->getActiveModelNames();

        $names = array();
        foreach ($modelNames as $modelName) {
            $names[$modelName['ID']] = strtolower(str_replace(" ", "[\s\-\_\+]", $modelName['name']));
        }
        $names = array_filter($names);

        foreach ($names as $modelNameID => $name) {
            if (preg_match("~((?:[\-\/])" . $name . "(?:[\-\/\_\.])?)~i", strtolower($url['url']), $matches)) {
                _d($modelNameID, $name, $url['url'], $matches);

                $urlModelName = new God_Model_WebCrawlerUrlModelName();
                $urlModelName->model_name_id = $modelNameID;
                $urlModelName->webcrawler_url_id = $url->id;
                $urlModelName->save();
            }
        }
    }
}