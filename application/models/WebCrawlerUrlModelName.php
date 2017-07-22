<?php
class God_Model_WebCrawlerUrlModelName extends God_Model_Base_WebCrawlerUrlModelName
{
    public static $space = "[\/\s\-\_\+]";

    public static function createLink(God_Model_WebCrawlerUrl $url, $names = array())
    {
        // Only link html pages not other content types
        if (stripos($url->contenttype, "text/html") === false || $url->statuscode != 200) return;

        /*
         * Create a quick array of the model names, processed modelNameID => model_name
         */

//        if (!$names) {
//
//            $modelNameTable = new God_Model_ModelNameTable();
//            $modelNames = $modelNameTable->getActiveModelNames();
//
//            $names = array();
//            foreach ($modelNames as $modelName) {
//                $names[$modelName['ID']] = $modelName['name'];
//            }
//        }
//        $names = array_filter($names);

        if ($names) {
            foreach ($names as $modelNameID => $name) {

                //checkCPULoad();

                if (self::checkUrlWithName($name, $url['url'])) {

                    self::_createLink($modelNameID, $url->id);

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
            return $names;
        }
    }

    public static function formatNameForUrlReg($name)
    {
        return strtolower(str_replace(" ", self::$space, $name));
    }

    public static function checkUrlWithName($name, $url)
    {
        $regex = self::$space . '(' . self::formatNameForUrlReg($name) . ')' . self::$space;
        if (preg_match("~" . $regex . "~i", $url, $matches)) {
            return true;
        }
        return false;
    }

    private static function _createLink($model_name_id, $webcrawler_url_id)
    {
        $urlModelName = God_Model_WebCrawlerUrlModelNameTable::getInstance()
            ->createQuery('wcmn')
            ->where('model_name_id = ?', $model_name_id)
            ->andWhere('webcrawler_url_id = ?', $webcrawler_url_id)
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        if (!$urlModelName) {

            $urlModelName = new God_Model_WebCrawlerUrlModelName();
            $urlModelName->model_name_id = $model_name_id;
            $urlModelName->webcrawler_url_id = $webcrawler_url_id;
            $urlModelName->save();

        }
    }
}
