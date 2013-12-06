<?php

namespace PlaygroundWeather\Form\Admin;

use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Form\Element;
use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;
use Zend\InputFilter\InputFilterProviderInterface;

class WeatherCodeFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $serviceManager;

    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($serviceManager);

        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        $this->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundWeather\Entity\WeatherCode'))
        ->setObject(new \PlaygroundWeather\Entity\WeatherCode());

        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'code',
            'options' => array(
                'label' => $translator->translate('Code', 'playgroundweather'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Code', 'playgroundweather'),
            ),
        ));

        $this->add(array(
            'name' => 'description',
            'options' => array(
                'label' => $translator->translate('Description', 'playgroundweather')
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Description', 'playgroundweather'),
            )
        ));

        $this->add(array(
            'name' => 'icon',
            'options' => array(
                'label' => $translator->translate('Icône', 'playgroundweather')
            ),
            'attributes' => array(
                'type' => 'file'
            )
        ));
        $this->add(array(
            'name' => 'iconURL',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => '',
            ),
        ));

        $this->add(array(
            'name' => 'isDefault',
            'type' => 'Zend\Form\Element\Hidden',
            'options' => array(
                'label' => $translator->translate('Default', 'playgroundweather')
            ),
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $codes = $this->getNonDefaultCodes();
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'associatedCode',
            'options' => array(
                'empty_option' => $translator->translate('Pas d\'association, valeur par défaut', 'playgroundweather'),
                'value_options' => $codes,
                'label' => $translator->translate('Code associé', 'playgroundweather')
            )
        ));

    }

    public function getInputFilterSpecification()
    {
        return array(
            'id' => array(
                'required' => false,
                'allowEmpty' => true,
            ),
            'code' => array(
                'required' => true,
                'allowEmpty' => false,
                'properties' => array(
                    'required' => true,
                ),
                'validators' =>array (
                    array('name' =>'Digits'),
                )
            ),
            'description' => array(
                'required' => true,
                'allowEmpty' => false,
                'properties' => array(
                    'required' => true,
                ),
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            ),
            'icon' => array(
                'required' => false,
                'allowEmpty' => true,
                'properties' => array(
                    'required' => false,
                    'allowEmpty' => true,
                )
            ),
            'associatedCode' => array(
                'required' => false,
                'allowEmpty' => true,
                'properties' => array(
                    'required' => false,
                    'allowEmpty' => true,
                )
            ),
        );
    }

    public function getNonDefaultCodes()
    {
    	$codes = array();
    	$codeService = $this->getServiceManager()->get('playgroundweather_weathercode_service');
    	$results = $codeService->getWeatherCodeMapper()->findBy(array('isDefault'=>0));

    	foreach ($results as $result) {
    		$codes[$result->getId()] = $result->getDescription();
    	}

    	return $codes;
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
     * @return User
     */
    public function setServiceManager (ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

}
