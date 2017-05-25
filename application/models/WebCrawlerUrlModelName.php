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

        $space = "[\/\s\-\_\+]";

        $names = array();
        foreach ($modelNames as $modelName) {
            $names[$modelName['ID']] = strtolower(str_replace(" ", $space, $modelName['name']));
        }
        $names = array_filter($names);

        foreach ($names as $modelNameID => $name) {
            $regex = $space . '(' . $name . ')' . $space;
            if (preg_match("~" . $regex . "~i", $url['url'], $matches)) {
                $urlModelName = new God_Model_WebCrawlerUrlModelName();
                $urlModelName->model_name_id = $modelNameID;
                $urlModelName->webcrawler_url_id = $url->id;
                $urlModelName->save();

                $link = $url->link;
                $link->priority = 100;
                $link->save();
            }
        }
    }
}