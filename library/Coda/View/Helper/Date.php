<?php
class Xigen_View_Helper_Date extends Zend_View_Helper_Abstract
{
    const SHORT     = 'd-m-Y';
    const TIME      = 'H:i';
    const DATETIME  = 'd/m/Y H:i';
    const SHORTTEXT = 'j M Y';
    const SQL       = 'Y-m-d H:i:s';

    public function date($date = null, $format = null)
    {
        if ($date == '0000-00-00 00:00:00' || $date == '0000-00-00' || empty($date)) {
            return 'N/A';
        }

        if (! is_int($date) && $date > 0) {
            if (($date = strtotime($date)) < 0) {
                return 'N/A';
            }
        }

        $format = $format ? $format : self::SHORT;

        return date($format, $date);
    }
}