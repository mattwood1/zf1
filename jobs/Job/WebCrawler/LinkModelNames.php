<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_LinkModelNames extends Job_Abstract
{
    public function run()
    {
        $modelNameTable = new God_Model_ModelNameTable();
        $modelNames = $modelNameTable->getActiveModelNames();

//        var_dump($modelNames);

        /*
         * Create a quick array of the model names, processed modelNameID => model_name
         */

        $names = array();
        foreach ($modelNames as $modelName) {
            $names[$modelName['ID']] = strtolower(str_replace(" ", "[\s\-\_]", $modelName['name']));
        }
        $names = array_filter($names);

        // Get existing ModelNameUrlLinks
        $webCrawlerURLModelNameTable = God_Model_WebCrawlerUrlModelNameTable::getInstance();
        $webCrawlerURLNameURLLinks = $webCrawlerURLModelNameTable->findAll(Doctrine_Core::HYDRATE_ARRAY);

        $modelNameURLLinks = array();
        foreach ($webCrawlerURLNameURLLinks as $webCrawlerURLNameURLLink) {
            $modelNameURLLinks[] = $webCrawlerURLNameURLLink['webcrawler_url_id'];
        }
        $modelNameURLLinks = array_values(array_unique(array_filter($modelNameURLLinks)));


//        var_dump($names, count($names));

        $webCrawlerUrlTable = God_Model_WebCrawlerUrlTable::getInstance();
        $webCrawlerUrlQuery = $webCrawlerUrlTable->createQuery('wu')
            ->select('*')
            ->leftJoin('wu.links wl')
            ->leftJoin('wl.url u')
            ->andWhere('u.contenttype like "%jpeg%"')
            ->andWhere('u.contentlength >= 90000')

            ->andWhereNotIn('wu.id', $modelNameURLLinks)
            ;
        $links = $webCrawlerUrlQuery->execute(Doctrine_Core::HYDRATE_ARRAY);
//        _d($links);

        foreach ($links as $link) {
            foreach ($names as $modelNameID => $name) {
                if (preg_match("~((?:[\-\/])" . $name . "(?:[\-\/\_\.])?)~i", $link['url'], $matches)) {
                    _d($modelNameID, $name, $link['url'], $matches);
                }
            }
        }

    }
}