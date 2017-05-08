<?php
/**
 * Scheduled to run every 1 hours every day.
 */
class Job_Cache_DuplicateImages extends Job_Abstract
{
    public function run()
    {
        God_Model_ImageHashTable::getDuplicateHashes(false, 1);
    }
}