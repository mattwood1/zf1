<?php
class God_Model_WebURLTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_WebURL');
    }
}