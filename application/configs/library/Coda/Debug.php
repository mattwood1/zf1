<?php
class Coda_Debug extends Zend_Debug
{
    static $profile;
}

function _d()
{
    foreach (func_get_args() as $arg) {
        if (is_object($arg)) {
            switch (get_class($arg)) {
                case 'Doctrine_Query':
                    Zend_Debug::dump(vsprintf( str_replace("?", "'%s'", $arg->getSqlQuery()), $arg->getFlattenedParams() ), 'SQL Query - Object(' . get_class($arg) . ')' );
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