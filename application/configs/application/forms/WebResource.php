<?php
class God_Form_WebResource extends Zend_Form
{
    public function init()
    {
        $this->addElement('text', 'website', array(
                'label' => 'Website',
                'placeholder' => 'Website name',
                'required' => true
                ))

        ->addElement('checkbox', 'sitescan', array(
                'label' => 'Scan the site',
                'placeholder' => '',
                //'required' => true
                ))

        ->addElement('text', 'sitescanurl', array(
                'label' => 'Site URL to scan',
                'placeholder' => '',
                //'required' => true
                ))

        ->addElement('text', 'sitescanxpath', array(
                'label' => 'Site URL to scan XPath',
                'placeholder' => '',
                //'required' => true
                ))

        ->addElement('text', 'xpathfilter', array(
                'label' => 'Thumbnails XPath',
                'placeholder' => '',
                //'required' => true
                ))

        ->addElement('text', 'imagexpath', array(
                'label' => 'Images XPath',
                'placeholder' => '',
                //'required' => true
                ))

        ->addElement('text', 'nextCheck', array(
                'label' => 'Date of Next Check',
                'placeholder' => '',
                //'required' => true
                ))
        ;
    }

}