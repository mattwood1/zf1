<?php
class God_Model_WebCrawlerUrlModelName extends God_Model_Base_WebCrawlerUrlModelName
{
    private static $space = "[\/\s\-\_\+]";

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
            $names[$modelName['ID']] = self::formatNameForUrlReg($modelName['name']);
        }
        $names = array_filter($names);

        foreach ($names as $modelNameID => $name) {
            if (self::checkUrlWithName($name, $url['url'])) {
                $urlModelName = new God_Model_WebCrawlerUrlModelName();
                $urlModelName->model_name_id = $modelNameID;
                $urlModelName->webcrawler_url_id = $url->id;
                $urlModelName->save();

                $link = $url->link;
                $link->priority = 100;
                $link->save();

                $sublinks = $link->sublinks;
                foreach ($sublinks as $sublink) {
                    $sublink->priority = 100;
                    $sublink->save();
                }
            }
        }
    }

    public static function formatNameForUrlReg($name)
    {
        return strtolower(str_replace(" ", self::$space, $name));
    }

    public static function checkUrlWithName($name, $url)
    {
        $regex = self::$space . '(' . $name . ')' . self::$space;
        if (preg_match("~" . $regex . "~i", $url, $matches)) {
            return true;
        }
        return false;
    }
}