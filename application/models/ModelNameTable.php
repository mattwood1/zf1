<?php
class God_Model_ModelNameTable extends Doctrine_Record
{
    const daysForRefresh = 6;

    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_ModelName');
    }

    // TODO: To be removed replaced by function below
    public static function getModelNameToBeSearched()
    {
        $modelNamesQuery = self::getInstance()
            ->createQuery('mn')
            ->where('mn.datesearched < ? OR mn.datesearched = "0000-00-00 00:00:00"', date("Y-m-d", strtotime("-".self::daysForRefresh." days")) )
            ->leftJoin('mn.model m')
            ->andWhere('m.active = ?', 1)
            ->andWhere('m.search = ?', 1)
            ->andWhere('m.ranking >= ?', 0)
            ->orderBy('mn.datesearched asc')
            ->limit(1);
        return $modelNamesQuery->execute();
    }

    // Using the newer WebCrawler
    public static function getModelNameForWebCrawlerUpdate()
    {
        // total Model Names array set for the day.
        $modelNameCount = self::getInstance()
            ->createQuery()
            ->select('count(*) as count')
            ->where('name != ""')
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $modelNameCount = reset($modelNameCount);
        $count = ceil($modelNameCount['count'] / self::daysForRefresh);

        $modelNamesQuery = self::getInstance()
            ->createQuery('mn')
            ->where('mn.webcrawler_updated < ? OR mn.webcrawler_updated = "0000-00-00 00:00:00"', date("Y-m-d 00:00:00", strtotime("-".self::daysForRefresh." days")) )
            ->leftJoin('mn.model m')
            ->andWhere('m.active = ?', 1)
            ->andWhere('m.search = ?', 1)
            ->andWhere('m.ranking >= ?', 0)
            ->andWhere('mn.name != ""')
            ->orderBy('mn.webcrawler_updated asc')
            ->limit($count);
        return $modelNamesQuery->execute();
    }

    public static function getByUrl($url)
    {
        $url_parts = parse_url($url);

        if (array_key_exists('path', $url_parts)) {

            $name = preg_replace("~" . God_Model_WebCrawlerUrlModelName::$space . "~", " ", $url_parts['path']);
            $names = explode(" ", $name);
            $names = array_filter($names);

            if ($names) {

                $modelNameQuery = self::getInstance()->createQuery('mn')->innerJoin('mn.model m');
                foreach ($names as $name) {
                    //$modelNameQuery->orWhere('MATCH (m.name) AGAINST (?)', $name);
                    $modelNameQuery->orWhere('m.name like ?', '%' . $name . '%');
                }
                $modelNameQuery->andWhere('m.active = 1')->andWhere('m.ranking > -1');

                $modelNames = $modelNameQuery->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                $modelArray = array();
                foreach ($modelNames as $modelName) {
                    $modelArray[$modelName['ID']] = $modelName['name'];
                }

                return $modelArray;
            }
        }
    }

    public function getActiveModelNames()
    {
        $cache = new Coda_Cache();
        $cachekey = 'activeModelNames';

        $activeModelNames = $this->unserialize($cache->load($cachekey));

        if (!$activeModelNames) {
            _d('Query active names');
            $query = $this->getInstance()
                ->createQuery('mn')
                ->innerJoin('mn.model m')
                ->where('m.active = ?', 1)
                ->andWhere('m.ranking > -1');
            $activeModelNames = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        }

        $cache->save($cachekey, $this->serialize($activeModelNames));

        return $activeModelNames;
    }

}
