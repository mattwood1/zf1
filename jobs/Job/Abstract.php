<?php
abstract class Job_Abstract
{
    protected $_processAlreadyRunning = false;

    protected $_lockFile;

    public function __construct()
    {
        $this->_lockFile = '/tmp/GODJOB-' . get_class($this);

        if (file_exists($this->_lockFile)) {
            $this->_processAlreadyRunning = true;
            Log::cli('--- Process ' . get_class($this) . ' already running, exiting ---');
            exit(0);
        } else {
            Log::cli('--- Creating lock file: ' . $this->_lockFile . ' ---' . PHP_EOL);
            $fp = fopen($this->_lockFile, 'w');
            fputs($fp, 'Started: ' . date('Y-m-d H:i:s'));
            fclose($fp);
        }
    }

    abstract public function run();

    public function __destruct()
    {
        if ($this->_processAlreadyRunning) {
            return;
        }

        if (file_exists($this->_lockFile)) {
            unlink($this->_lockFile);
            Log::cli(PHP_EOL . '--- Removing lock file: ' . $this->_lockFile . ' ---');
        }
/*
        $jobName = get_class($this);

        $jobDb = CronJobLog::getCreate($jobName);

        $jobDb->populate(array(
            'jobName'     => $jobName,
            'dateLastRun' => new Zend_Db_Expr('NOW()')
        ));

        $jobDb->save();
*/
    }
}