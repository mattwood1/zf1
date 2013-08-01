<?php

// Define path to application directory
defined('APPLICATION_PATH') 
	|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path()
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->getAutoloader()->pushAutoloader(array('Doctrine_Core', 'autoload'));

$application->getBootstrap()->bootstrap('doctrine');

$config = $application->getOption('doctrine');

$options = array(
	'data_fixtures_path' => APPLICATION_PATH . "/doctrine/data/fixtures",
	'sql_path' => APPLICATION_PATH . "/doctrine/data/sql",
	'migrations_path' => APPLICATION_PATH . "/doctrine/migrations",
	'yaml_schema_path' => APPLICATION_PATH . "/doctrine/schema",
	'models_path' => APPLICATION_PATH . "/models_generated",
	'generate_models_options' => array(
		'pearStyle' => true,
		'generateTableClasses' => true,
		'generateBaseClasses' => true,
		'baseClassPrefix' => 'Base_',
		'baseClassesDirectory' => null,
		'classPrefixFiles' => false,
		'classPrefix' => ''
	)
);

$config = array_merge($config, $options);

$cli = new Doctrine_Cli($config);
$cli->run($_SERVER['argv']);

?>
