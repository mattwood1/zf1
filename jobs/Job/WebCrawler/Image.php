<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_Image extends Job_Abstract
{
    public function run()
    {
        $cpuload = 1;
        checkCPULoad($cpuload);
        $curl = new God_Model_Curl();

        $webCrawlerUrlTable = new God_Model_WebCrawlerUrlTable();

        $webUrlImagesQuery = $webCrawlerUrlTable->getInstance()
            ->createQuery('wu')
            ->innerJoin('wu.link link')
            ->innerJoin('link.parent_url parent_url')
            ->where('contenttype = ?', 'image/jpeg')
            ->andwhere('width = ?', 0)
            ->andWhere('height = ?', 0)
            ->andWhere('contentlength > ?', 0)
            ->orderBy('wu.id')
            ->limit(50)
        ;

        $webUrlImages = $webUrlImagesQuery->execute();

        foreach ($webUrlImages as $webUrlImage) {
            $curl->Curl($webUrlImage->url, $webUrlImage->link->parent_url->url);
            list($width, $height, $type, $attr) = getimagesizefromstring($curl->rawdata());
            $webUrlImage->width = $width;
            $webUrlImage->height = $height;
            $webUrlImage->pixels = $width * $height;

            $webUrlImage->save();
        }
    }
}