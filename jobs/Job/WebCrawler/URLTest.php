<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_URLTest extends Job_Abstract
{
    public function run()
    {
        $curl = new God_Model_Curl();

        $tests = array(
            array(
                'url' => '?page=1',
                'root' => 'http://www.site.com',
                'result' => 'http://www.site.com/?page=1'
            ),
            array(
                'url' => 'gallery.php?page=1',
                'root' => 'http://www.site.com/gallery.php',
                'result' => 'http://www.site.com/gallery.php?page=1'
            ),
            array(
                'url' => 'image.jpg',
                'root' => 'http://www.site.com/section_area/',
                'result' => 'http://www.site.com/section_area/image.jpg'
            ),
        );

        $success = true;
        foreach($tests as $test) {
            if ($curl->normalizeURL($test['url'],$test['root']) != $test['result']) {
                var_dump(array('Failure' => $test, 'Result' => $curl->normalizeURL($test['url'],$test['root'])));
                $success = false;
            }
        }

        echo $success ? 'All worked' : 'Error found';
    }
}
