<?php
class Coda_Debug extends Zend_Debug
{
}

function checkCPULoad($load = 1.75, $temp = 55)
{
    if ($load == 0) $load = 1.75;
    if ($temp == 0) $temp = 55;

    $sysload = sys_getloadavg();
    $systemp = (float)str_replace('°C', '', str_replace('+', '', trim(str_ireplace('Core0 Temp:', '', exec('sensors | sed -n 3p')))));
    
    if ((float)$sysload[0] > $load || $systemp >= $temp) {
        $time = floor(10*$sysload[0]*(1/$load));
        $string[] = 'Sleeping (' . $time . ' secs) ';
        $string[] .= $sysload[0] > $load ? 'cpu ' .$sysload[0] . ' > ' . $load : '';
        $string[] .= $systemp >= $temp ? 'temp ' . $systemp . ' => ' . $temp: '';
        echo implode(" ", $string) . "\n";
        sleep($time);
        checkCPULoad($load, $temp);
    }
    
    return;
}

function _d()
{
    foreach (func_get_args() as $arg) {
        if (is_object($arg)) {
            switch (get_class($arg)) {
                case 'Doctrine_Query':
                    Zend_Debug::dump(vsprintf(str_replace(array("%", "?"), array("%%", "%s"), $arg->getSqlQuery()), $arg->getFlattenedParams() ), 'SQL Query - Object(' . get_class($arg) . ')' );
                    $obj = clone $arg;
                    _d($obj->execute());
                    break;
                case 'Doctrine_Pager': // Experimental
                    if (! $arg->getExecuted()) {
                        Zend_Debug::dump('Pager needs to be executed.', 'Object(' . get_class($arg) .')');
                        break;
                    }

                    $data = array(
                            'HaveToPaginate' => $arg->haveToPaginate(),
                            'Results'        => $arg->getNumResults(),
                            'Page'           => $arg->getPage(),
                            'MaxPerPage'     => $arg->getMaxPerPage(),
                            'ResultsInPage'  => $arg->getResultsInPage()
                        );

                    Zend_Debug::dump($data, 'Object(' . get_class($arg) .')');
                    break;
                default:
                    if (method_exists($arg, 'toArray')) {
                        Zend_Debug::dump($arg->toArray(), 'Object(' . get_class($arg) .')');
                    } else {
                        Zend_Debug::dump($arg, 'Object(' . get_class($arg) .')');
                    }
                    break;
            }
        } else {
            Zend_Debug::dump($arg);
        }
    }
}

function _dexit()
{
    call_user_func_array('_d', func_get_args());
    exit;
}

function _dquery()
{
    foreach (func_get_args() as $arg) {
        if (is_object($arg) && get_class($arg) == 'Doctrine_Query') {

            Zend_Debug::dump(vsprintf(str_replace(array("%", "?"), array("%%", "%s"), $arg->getSqlQuery()), $arg->getFlattenedParams()), 'SQL Query - Object(' . get_class($arg) . ')');
        }
        else {

            Zend_Debug::dump('Not a Doctrine Query');
        }
    }
}


if (!function_exists('getimagesizefromstring')) {
   function getimagesizefromstring($string_data)
   {
       $uri = 'data://application/octet-stream;base64,'  . base64_encode($string_data);
       return getimagesize($uri);
   }
}
