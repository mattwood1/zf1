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
        if ($names) {
            foreach ($names as $modelNameID => $name) {

                if (self::checkUrlWithName(self::formatNameForUrlReg($name), $url->url)) {

                    self::_createLink($modelNameID, $url->id);

                    $url->promote(God_Model_WebCrawlerLink::PRIORTIY_HIGH);
                }
            }
            return $names;
        }
    }

    public static function formatNameForUrlReg($name)
    {
        return self::$space . '(' . strtolower(preg_replace("~[\s\-]~", self::$space, $name)) . ')(?:' . self::$space . '|$)';
    }

    public static function checkUrlWithName($regex, $url)
    {
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
