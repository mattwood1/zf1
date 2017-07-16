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

        $webCrawlerUrlTable = new God_Model_WebCrawlerUrlTable();

        $webUrlImagesQuery = $webCrawlerUrlTable->getInstance()
            ->createQuery('wu')
            ->where('contenttype = ?', 'image/jpeg')
            ->andwhere('width = ?', 0)
            ->andWhere('height = ?', 0)
            ->limit(50)
        ;

        $webUrlImages = $webUrlImagesQuery->execute();

        foreach ($webUrlImages as $webUrlImage) {
            list($width, $height, $type, $attr) = getimagesize($webUrlImage->url);
            $webUrlImage->width = $width;
            $webUrlImage->height = $height;

            $webUrlImage->save();
        }
    }
}