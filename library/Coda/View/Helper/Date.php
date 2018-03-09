<?php
class Coda_View_Helper_Date extends Zend_View_Helper_Abstract
{
    const SHORT     = 'd/m/Y';
    const TIME      = 'H:i';
    const DATETIME  = 'd/m/Y H:i';
    const SHORTTEXT = 'j M Y';
    const SQL       = 'Y-m-d H:i:s';
    const FUZZY     = 'FUZZY';

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

	if ($format != self::FUZZY) {

            return date($format, $date);
        }
        else {
            $input = $date;
            $now = time();
            $diff = $now - $date; 
            $string = 'Don\'t know';
            // Hours
            $hour = 60*60;
if ($diff < $hour * 8) {
    $hours= ceil($diff/$hour);
    switch ($hours) {
        case 0:
            $string = 'Less than an hour';
            break; 
        case 1:
            $string = 'An hour ago';
            break; 
        default:
            $string = $hours . ' hours ago';
            break; 
    }
}
else {
    $inputDate = strtotime(date('Y-m-d', $input));
    $nowDate = strtotime(date('Y-m-d', $now));
    $diffDate = $nowDate - $inputDate; 
    $day = 86400;
    
    if (!$diffDate) {
        $string = 'Today';
    }
    else {
        $days = $diffDate / $day; 
        if ($days < 7) {
        switch ($days) {
            case 1:
                $string = 'Yesterday';
                break; 
            default:
                $string = $days . ' days ago';
                break; 
        }
        }
        else {
            $weeks = floor( $days / 7);
            switch ($weeks) {
                case 1:
                    $string = 'A week ago.';
                    break; 
                default:
                    $string = $weeks . ' weeks ago.';
                    break; 
            }
        }
    }
}

            return $string;
        }
    }
}
