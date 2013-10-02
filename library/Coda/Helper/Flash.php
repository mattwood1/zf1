<?php
class Coda_Helper_Flash extends Zend_Controller_Action_Helper_Abstract
{
	const INFO    = 'info';
    const SUCCESS = 'success';
    const ERROR   = 'error';
    
    /**
     * Add a new flash message to the session
     * 
     * @param string $message
     * @param string $type
     * @return Xigen_Helper_Flash
     */
    public function direct($message, $type = self::SUCCESS)
    {
        $this->_getSession()->messages[] = (object)array(
            'message' => $message,
            'type'    => $type
        );
        
        return $this;
    }
    
    /**
     * Get all of the messages currently in the session
     * 
     * @return array
     */
    public function getMessages()
    {
        return $this->_getSession()->messages;
    }
    
    /**
     * Clear all flash messages out of the session
     * 
     * @return Xigen_Helper_Flash
     */
    public function clean()
    {
        $this->_getSession()->messages = array();
        return $this;
    }
    
    /**
     * @return Zend_Session_Namespace
     */
    protected function _getSession()
    {
        if (! $this->_session) {
            $this->_session = new Zend_Session_Namespace(__CLASS__);
            if (! isset($this->_session->messages)) {
                $this->_session->messages = array();
            }
        }
        
        return $this->_session;
    }
}