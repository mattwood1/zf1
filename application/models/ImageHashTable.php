<?php
class God_Model_ImageHashTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_ImageHash');
    }

    /**
    * Get duplicate hashes
    *
    * @return array [hashes]
    **/
    public static function getDuplicateHashes($useCache = false, $limit = null)
    {
        // Adding caching
        $cache = Zend_Cache::factory('Core', 'Memcached');
        $cachekey = "DuplicateHashes";
        $pretestResults = array();

        if ($useCache) {
            $pretestResults = unserialize($cache->load($cachekey));
        }

        if ($pretestResults === false) {

            $conn = Doctrine_Manager::getInstance()->connection();

            $sql = 'SELECT
                p1.id photosetid1,
                p2.id photosetid2

                    FROM `imagehash` ih1
                    JOIN imagehash ih2 ON (ih1.hash = ih2.hash and ih1.id != ih2.id)
                    JOIN images im1 ON (ih1.image_id = im1.id)
                    JOIN images im2 ON (ih2.image_id = im2.id)

                    JOIN photosets p1 ON (im1.photoset_id = p1.id)
                    JOIN photosets p2 ON (im2.photoset_id = p2.id)

                    WHERE ih1.hash != ""';
            if ($limit) {
                $sql .= ' limit ' . $limit;
            }

            $pretest = $conn->execute($sql);
            $pretestResults = $pretest->fetchAll();

            $cache->save(serialize($pretestResults), $cachekey);
        }

        return $pretestResults;
    }
}
