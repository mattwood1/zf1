<?php
proc_nice(10);
// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
        realpath(APPLICATION_PATH . '/../library'),
        realpath(APPLICATION_PATH . '/../jobs'),
        get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

// Create application, bootstrap, and run
$application = new Zend_Application(
        APPLICATION_ENV,
        APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();

$script = $argv[1];

$className = 'Job_' . $script;
$job = new $className;
$job->run();
