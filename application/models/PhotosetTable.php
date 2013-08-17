<?php
class God_Model_PhotosetTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_Photoset');
    }
}