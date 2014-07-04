<?php

namespace PlaygroundWeather\Form\Admin;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;

use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;
use PlaygroundWeather\Form\Admin\FilterFieldset;

class Filter extends ProvidesEventsForm
{
    protected $serviceManager;

    public function __construct ($name = null, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($sm);

        $filterFieldset = new FilterFieldset(null, $sm, $translator);
        $this->add(array(
            'type'    => 'Zend\Form\Element\Collection',
            'name'    => 'columns',
            'options' => array(
                'id'    => 'columns',
                'label' => $translator->translate('Filter', 'playgroundweather'),
                'count' => 0,
                'should_create_template' => true,
                'target_element' => $filterFieldset,
            )
        ));

        $submitElement = new Element\Button('submit');
        $submitElement->setAttributes(array(
            'type'  => 'submit',
            'class' => 'btn btn-primary',
        ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));


    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager ()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     */
    public function setServiceManager (ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}