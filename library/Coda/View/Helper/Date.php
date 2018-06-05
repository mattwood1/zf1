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
            $hour = 60 * 60;
            $day = $hour * 24;
            $week = $day * 7;
            $year = $day * 365;
            $month = $year / 12;

            $strings = array(
                'hours' => array(
                    0 => 'Less than an hour',
                    1 => 'An hour ago',
                    2 => '%d %s ago'
                ),
                'days' => array(
                    0 => 'Today',
                    1 => 'Yesterday',
                    2 => '%d %s ago'
                ),
                'weeks' => array(
                    1 => 'A week ago',
                    2 => '%d %s ago'
                ),
                'months' => array(
                    1 => 'A month ago',
                    2 => '%d %s ago'
                ),
                'years' => array(
                    1 => 'A year ago',
                    2 => '%d %s ago'
                )
            );

            $input = $date;
            $now = time();
            $diff = $now - $input;
            $string = 'Don\'t know';
            $period = null;

            // Hours
            if ($diff < $hour * 8) {
                $period = 'hours';
                $periodFactor= floor($diff/$hour);
                if ($periodFactor > 2) $periodFactor = 2;
            }
            else {
                $period = 'days';
                $periodFactor = floor($diff/$day);
                $periodValue = $diff/$day;

                if ($periodValue >= 7) {
                    $period = 'weeks';
                    $periodFactor = floor($diff/$week);
                    $periodValue = $diff/$week;

                    if ($periodFactor > 2) $periodFactor = 2;

                    if ($periodValue >= floor($month/$week) && $periodValue < 12) {
                        $period = 'months';
                        $periodFactor = round($diff/$month);
                        $periodValue = round($diff/$month);
                    }
                    elseif ($periodValue > 12) {
                        $period = 'years';
                        $periodFactor = floor($diff/$year);
                        $periodValue = $diff/$year;
                    }
                }

                if ($periodFactor > 2) $periodFactor = 2;

//                var_dump(array(
//                    'period' => $period,
//                    'factor' => $periodFactor,
//                    'value' => $periodValue
//                ));

                $string = sprintf($strings[$period][$periodFactor], $periodValue, $period);
            }

            return $string;
        }
    }
}
