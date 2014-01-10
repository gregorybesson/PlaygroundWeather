<?php

namespace PlaygroundWeather\Form\Admin;

use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;
use Zend\InputFilter\InputFilterProviderInterface;

class FilterFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $serviceManager;

    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($serviceManager);

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'columnName',
            'options' => array(
                'label' => $translator->translate('Filter'),
            ),
            'attributes' => array(
                'placeholder' => $translator->translate('Filter'),
                'allow_empty' => false,
                'required' => true,
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'columnFilter',
            'options' => array(
                'label' => $translator->translate('Filter'),
            ),
            'attributes' => array(
                'placeholder' => $translator->translate('Filter'),
                'allow_empty' => true,
                'required' => false,
            ),
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'columnFilter' => array(
                'required' => false,
                'allowEmpty' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            ),
        );
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