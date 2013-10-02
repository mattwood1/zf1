<?php
class Coda_View_Helper_Flash extends Zend_View_Helper_Abstract
{
    public function flash()
    {
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('flash');
        
        $messages = $helper->getMessages();
        
        $helper->clean();
        
        $html = '<div id="flashMessageContainer">';
        
        foreach ($messages as $messageSpec) {
            $html .= $this->renderMessage($messageSpec->message, $messageSpec->type);
        }
        
        return $html . '</div>';
    }
    
    public function renderMessage($message, $type = Coda_Helper_Flash::SUCCESS)
    {
        return '<div class="flashMessage alert alert-' . $type . '">'
                   . '<button type="button" class="close" data-dismiss="alert">×</button>'
                   . '<span class="' . $this->_icon($type) . '" style="margin-right: 15px"></span>' . $message . '</div>';
    }
    
    public function renderNow($message, $type = Coda_Helper_Flash::SUCCESS)
    {
        $this->view->js()->onload('Aub.Flash.show("' . addslashes($message) . '", "' . $type . '")');
    }
    
    protected function _icon($type)
    {
        switch ($type) {
            case Coda_Helper_Flash::ERROR:
                return 'icon-exclamation-sign';
            case Coda_Helper_Flash::INFO;
                return 'icon-info-sign';
            case Coda_Helper_Flash::SUCCESS:
                return 'icon-ok';
        }
    }
}