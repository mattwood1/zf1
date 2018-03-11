<?php
/**
 * Created by PhpStorm.
 * User: mwood
 * Date: 30/08/17
 * Time: 18:56
 */

class Job_WebCrawler_Download extends Job_Abstract
{
    public function run()
    {

        $photosetTable = new God_Model_PhotosetTable();
        $query = $photosetTable->getThumbnails();
        $rows = $query->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        if (count($rows) >= 18 * 3) {

            $conn = Doctrine_Manager::getInstance()->connection();
            $sql = "INSERT INTO webcrawlerUrlLink_ref (link_id, url_id) 
                    SELECT l.id, l.parent_url_id FROM `webcrawlerLinks` l
                    left outer join webcrawlerUrlLink_ref r on (l.parent_url_id = r.url_id and l.id = r.link_id)
                        where r.url_id is null
                        and r.link_id is null
                        and l.parent_url_id !=0
                        limit 2000";
            $query = $conn->execute($sql);
//            $models = $query->fetchAll();
            exit;
        }

        $curl = new God_Model_Curl();
//        $id = 3757903;

        $webCrawlerUrlTable = new God_Model_WebCrawlerUrlTable();
        $webCrawlerUrlQuery = $webCrawlerUrlTable->getDisplayQuery();
        $webCrawlerUrlQuery->leftJoin('mn.model model');
        $webCrawlerUrlQuery->andwhere('domain.download = ?', 1)
            ->andWhere('mn.download = ?', 1);
//        $webCrawlerUrlQuery->andWhere('wcu.id = ?', $id);
        $webCrawlerUrlQuery->limit(1);

        $webCrawlerUrls = $webCrawlerUrlQuery->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
        if (count($webCrawlerUrls) > 0) {
        foreach ($webCrawlerUrls as $webCrawlerUrl) {

            $logfile = fopen('/tmp/WC_Download_'.date('Y-m-d').'.txt', 'a');
            fwrite($logfile, date('H:i:s') . ' ' . $webCrawlerUrl['url'] . "\n");
            fclose($logfile);

            echo $webCrawlerUrl['url'] . "\r\n";
            $photosets = null;

            foreach ($webCrawlerUrl['linkref'] as $linkRef) {
                if (!is_array($linkRef['link']['url'])) continue;
            }

            //Consolidate the database records into usable data
            $thumbnails = God_Model_WebCrawlerUrlTable::getThumbnailsFromData($webCrawlerUrl);

            // Make a tempory directory to put downloaded images in.
            $pathname = '/tmp' . DIRECTORY_SEPARATOR . $webCrawlerUrl['id'];
            God_Model_File::createPath($pathname);

            $images = array();
            $hashes = array();
            $exisingImageHashes = array();

            // Download images and generate fingerprints and store in an array $images[]
            echo 'Downloading images' . "\r\n";
            foreach ($thumbnails as $thumbnail) {
                $imageIDs[] = $thumbnail['id'];

                $curl->Curl($thumbnail['url']);
                $filepath = $pathname . '/' . basename($thumbnail['url']);

                file_put_contents($filepath, $curl->rawdata());

                $fileinfo = getimagesize($filepath);

                if ($fileinfo !== false) {
                    $hash = ph_dct_imagehash_to_array(ph_dct_imagehash($filepath));
                    $hashes[] = implode(',', $hash);

                    $images[] = array(
                        'id' => $thumbnail['id'],
                        'filepath' => $filepath,
                        'targetfilename' => basename($filepath),
                        'hash' => implode(',', $hash),
                        'width' => $fileinfo[0],
                        'height' => $fileinfo[1]
                    );
                }
            }
            echo 'Downloded ' . count($images) . ' images' . "\r\n";
            $downloaded = count($images);


            // Check for existing image fingerprints
            if ($hashes) {
                $exisingImageHashes = God_Model_ImageHashTable::getInstance()->createQuery('ih')
                    ->whereIn('hash', $hashes)
                    ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                echo count($exisingImageHashes) . ' Existing images' . "\r\n";
                $existing = count($exisingImageHashes);
            }

            // Get photosets that are linked to the found image hashes
            // TODO: Group Photosets by more than 1 image hash found. Pick the one with the most hash matches
            if ($exisingImageHashes) {
                $photosets = array();
                foreach ($exisingImageHashes as $exisingImageHash) {
                    $imageObj = God_Model_ImageTable::getInstance()->find($exisingImageHash['image_id'], Doctrine_Core::HYDRATE_ARRAY);
                    $photosets[$imageObj['photoset_id']] = God_Model_PhotosetTable::getInstance()->find($imageObj['photoset_id']);
                }
                $photosets = array_filter($photosets);
            }

            // This will override the photosets if it already exists
            if ($WC_URL = God_Model_WebCrawlerUrlPhotosetsTable::getInstance()->findOneBy('url_id', $webCrawlerUrl['id'], Doctrine_Core::HYDRATE_ARRAY)) {
                $photosets = array(God_Model_PhotosetTable::getInstance()->find($WC_URL['photoset_id']));
                echo 'Photoset Defined by Existing Link' . "\r\n";
            }

            // This will get the photoset object to use later on
            if ($images && $photosets) {
                $firstPhotoset = reset($photosets);
                $photoset = God_Model_PhotosetTable::getInstance()->findOneByPath($firstPhotoset->path);
            }

            // We have images but no photoset so we can create one
            elseif ($images) {
                foreach($webCrawlerUrl['modelnamelinks'] as $modelnamelink) {
                    $model = God_Model_ModelTable::getInstance()->find($modelnamelink['modelName']['model']['ID']);
                    $photoset = $model->createPhotoset();
                }
            }

            // With the selected photoset, we get the image hashes for that photoset
            // Ensure that existing images are not transferred. But allow image download if the image
            // exists in another photoset.
            if ($images && $photosetImageHashes = $photoset->getImageHashes()) {

                foreach ($images as $imageKey => $image) {

                    if (in_array($image['hash'], array_keys($photosetImageHashes))) {
                        if ($image['width'] <= $photosetImageHashes[$image['hash']]['width']
                        && $image['height'] <= $photosetImageHashes[$image['hash']]['height']) {
                            unset($images[$imageKey]);
                        }
                        else {
                            $images[$imageKey]['targetfilename'] = $webCrawlerUrl['id'] . '-' . $image['targetfilename'];
                        }
                    }
                }
            }

            echo count($images) . ' Remaining images' . "\r\n";
            $remaining = count($images);

            // If we have images and photoset, move the files to the location and set the photoset to
            // be checked, active and not a manual image selected.
            if ($images && $photoset) {
                echo 'Photoset path ' . $photoset->path . "\r\n";
                echo 'Moving ' . count($images) . ' images' . "\r\n";            
                // Move the images
                foreach ($images as $image) {
                    rename(
                        $image['filepath'],
                        PUBLIC_PATH . $photoset->path . DIRECTORY_SEPARATOR . $image['targetfilename']);
                }

                $photoset->imagesCheckedDate = "0000-00-00 00:00:00";
                $photoset->manual_thumbnail = 0;
                $photoset->active = 1;
                $photoset->save();

                // Trigger updating images
                echo 'Updating Photoset' . "\n";
                $photoset->updateImages();
                if ($remaining + $existing != $downloaded) {
                    echo 'Updating duplicates' . "\n";
                    God_Model_ImageHashTable::getDuplicateHashes(false, 1);
                }
            }

            // Mark images as Downloaded using the imageIDs
            $conn = Doctrine_Manager::connection();
            $conn->execute('UPDATE webcrawlerUrls SET downloaded = 1 where id in ('.implode(',', $imageIDs).')');

            // Link the WebCrawler URL to the photoset
            if ($images && !God_Model_WebCrawlerUrlPhotosetsTable::getInstance()->findOneBy('url_id', $webCrawlerUrl['id'])) {
                $WC_URL = new God_Model_WebCrawlerUrlPhotosets();
                $WC_URL->fromArray(array(
                    'url_id' => $webCrawlerUrl['id'],
                    'photoset_id' => $photoset['id']
                ));
                $WC_URL->save();
            }

            // Clean up files
            if ($files = God_Model_File::scanPath($pathname)->getFiles()) {
                foreach ($files as $file) {
                    unlink($pathname . DIRECTORY_SEPARATOR . $file);
                }
            }

            // Clean tmp dir
            rmdir($pathname);

        }
        }
    }
}

