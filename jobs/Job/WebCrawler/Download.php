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

            // Download thumbnails / images, fingerprint and store in an array $images[]
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

            // If there are existing image hashes get photosets that are linked to the found images
            if ($exisingImageHashes) {

                $photosets = array();
                foreach ($exisingImageHashes as $exisingImageHash) {

                    // Could be an array
                    $imageObj = God_Model_ImageTable::getInstance()->find($exisingImageHash['image_id']);

                    // Used here
                    $photosets[$imageObj->photoset_id] = God_Model_PhotosetTable::getInstance()->find($imageObj->photoset_id);
                    $knownHashes[] = $exisingImageHash['hash']; // Needed?

                    // For each image[] if image hash matches check the dimensions of the image.
                    // If image is larger rename targetfilename to stop overwritting the file.
                    /*
                    foreach ($images as $imageKey => $image) {
                        if ($image['hash'] == $exisingImageHash['hash']) {
                            if ($image['width'] <= $imageObj['width'] && $image['height'] <= $imageObj['height']) {
                                unset($images[$imageKey]);
                            } else {
                                $images[$imageKey]['targetfilename'] = $webCrawlerUrl['id'] . '-' . $image['targetfilename'];
                            }
                        }
                    }
                    */
                }
                // Remove empty elements
                $photosets = array_filter($photosets);
            }

            if ($WC_URL = God_Model_WebCrawlerUrlPhotosetsTable::getInstance()->findOneBy('url_id', $webCrawlerUrl['id'], Doctrine_Core::HYDRATE_ARRAY)) {
                $photosets = array(God_Model_PhotosetTable::getInstance()->find($WC_URL['photoset_id']));
                echo 'Photoset Defined by Existing Link' . "\r\n";
            }

            // If there are images remaining we can use the first $photosets[]
            // Reset check data, manual thumbnail.
            if ($images && $photosets) {
                $firstPhotoset = reset($photosets);
                $photoset = God_Model_PhotosetTable::getInstance()->findOneByPath($firstPhotoset->path);
//                $photoset->imagesCheckedDate = "0000-00-00 00:00:00";
//                $photoset->manual_thumbnail = 0;
//                $photoset->active = 1;
//                $photoset->save();
            }



            // No Existing images, using the model name create a new photoset from the Model object
            elseif ($images) {
                foreach($webCrawlerUrl['modelnamelinks'] as $modelnamelink) {
                    $model = God_Model_ModelTable::getInstance()->find($modelnamelink['modelName']['model']['ID']);
                    $photoset = $model->createPhotoset();
                }
            }

            if ($photosetImageHashes = $photoset->getImageHashes()) {

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

            if ($images && $photoset) {
                echo 'Photoset path ' . $photoset->path . "\r\n";
                echo 'Moving ' . count($images) . ' images' . "\r\n";            
                // Move the images
                foreach ($images as $image) {
                    rename(
                        $image['filepath'],
                        PUBLIC_PATH . $photoset->path . DIRECTORY_SEPARATOR . $image['targetfilename']);
                }

                // Trigger updating images
                echo 'Updating Photoset';
                $photoset->updateImages();
                if ($remaining + $existing != $downloaded) {
                    echo 'Updating duplicates';
                    God_Model_ImageHashTable::getDuplicateHashes(false, 1);
                }
            }

            // Mark images as Downloaded using the imageIDs
            $conn = Doctrine_Manager::connection();
            $conn->execute('UPDATE webcrawlerUrls SET downloaded = 1 where id in ('.implode(',', $imageIDs).')');

            if (! God_Model_WebCrawlerUrlPhotosetsTable::getInstance()->findOneBy('url_id', $webCrawlerUrl['id'])) {
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

