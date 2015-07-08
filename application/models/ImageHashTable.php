<?php
class God_Model_ImageHashTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_ImageHash');
    }
}