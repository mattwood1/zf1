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

        foreach ($webCrawlerUrls as $webCrawlerUrl) {

            _d($webCrawlerUrl);

            foreach($webCrawlerUrl['linkref'] as $linkRef) {
                if (!is_array($linkRef['link']['url'])) continue;
            }

            $thumbnails = God_Model_WebCrawlerUrlTable::getThumbnailsFromData($webCrawlerUrl);

            $pathname = '/tmp/' . $webCrawlerUrl['id'];
            if (!realpath($pathname)) {
                mkdir($pathname);
            }

            $images = array();
            $hashes = array();
            foreach ($thumbnails as $thumbnail) {
                $curl->Curl($thumbnail['url']);
                $filepath = $pathname . '/' . basename($thumbnail['url']);

                file_put_contents($filepath, $curl->rawdata());

                $hash = ph_dct_imagehash_to_array(ph_dct_imagehash($filepath));
                $hashes[] = implode(',', $hash);
                $imageIDs[] = $thumbnail['id'];
                $fileinfo = getimagesize($filepath);

                $images[] = array(
                    'id' => $thumbnail['id'],
                    'filepath' => $filepath,
                    'targetfilename' => basename($filepath),
                    'hash' => implode(',', $hash),
                    'width' => $fileinfo[0],
                    'height' => $fileinfo[1]
                );
            }
            _d($images);

            $exisingImageHashes = God_Model_ImageHashTable::getInstance()->createQuery('ih')
                ->whereIn('hash', $hashes)
                ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            _d($exisingImageHashes);

            if ($exisingImageHashes) {

                $photosets = array();
                foreach($exisingImageHashes as $exisingImageHash) {

                    $imageObj = God_Model_ImageTable::getInstance()->find($exisingImageHash['image_id']);
                    $photosets[] = God_Model_PhotosetTable::getInstance()->find($imageObj->photoset_id);
                    $knownHashes[] = $exisingImageHash['hash']; // Needed?

                    foreach ($images as $imageKey => $image) {
                        if ($image['hash'] == $exisingImageHash['hash']) {
                            if ($image['width'] <= $imageObj['width'] && $image['height'] <= $imageObj['height']) {
                                unset($images[$imageKey]);
                            }
                            else {
                                $images[$imageKey]['targetfilename'] = $webCrawlerUrl['id'] . '-' . $image['targetfilename'];
                            }
                        }
                    }
                }

                if ($images) {
                    $photoset = $photosets[0];
                    $photoset->imagesCheckedDate = "0000-00-00 00:00:00";
                    $photoset->manual_thumbnail = 0;
                    $photoset->save();
                }
            }
            else {
                foreach($webCrawlerUrl['modelnamelinks'] as $modelnamelink) {
                    $model = God_Model_ModelTable::getInstance()->find($modelnamelink['modelName']['model']['ID']);
                    $photoset = $model->createPhotoset();
                }
            }

            _d($images);

            if ($images && $photoset) {
                foreach ($images as $image) {
                    rename(
                        $image['filepath'],
                        PUBLIC_PATH . $photoset->path . DIRECTORY_SEPARATOR . $image['targetfilename']);
                }

                $photoset->updateImages();
            }

            // Mark images as Downloaded using the imageIDs
            $conn = Doctrine_Manager::connection();
            $conn->execute('UPDATE webcrawlerUrls SET downloaded = 1 where id in ('.implode(',', $imageIDs).')');

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

