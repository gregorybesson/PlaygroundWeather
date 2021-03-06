<?php
namespace PlaygroundWeather\Form\Admin;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;
use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use PlaygroundWeather\Entity\Location;

class ImageMap extends ProvidesEventsForm
{
    protected $serviceManager;

    public function __construct ($name = null, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($sm);
        $entityManager = $sm->get('doctrine.entitymanager.orm_default');

        $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundWeather\Entity\ImageMap');
        $hydrator->addStrategy('partner', new \PlaygroundCore\Stdlib\Hydrator\Strategy\ObjectStrategy());
        $this->setHydrator($hydrator);

        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => $translator->translate('Name', 'playgroundweather'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Name', 'playgroundweather'),
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

        $countries = $this->getCountries();
        $this->add(array(
            'name' => 'country',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => $translator->translate('Country', 'playgroundweather'),
                'value_options' => $countries,
            ),
            'attributes' => array(
                'id'=> 'country'
            )
        ));

        $this->add(array(
            'name' => 'image',
            'options' => array(
                'label' => $translator->translate('Image', 'playgroundweather')
            ),
            'attributes' => array(
                'type' => 'file',
                'id'=> 'imageFile'
            )
        ));
        $this->add(array(
            'name' => 'imageURL',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => '',
            ),
        ));

        $this->add(array(
            'name' => 'imageWidth',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'imageHeight',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'latitude1',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => $translator->translate('latitude', 'playgroundweather')
            ),
            'attributes' => array(
                'value' => 0,
                'id' => 'latitude1',
            ),
        ));


        $this->add(array(
            'name' => 'longitude1',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => $translator->translate('longitude', 'playgroundweather')
            ),
            'attributes' => array(
                'value' => 0,
                'id' => 'longitude1',
            ),
        ));

        $this->add(array(
            'name' => 'latitude2',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => $translator->translate('latitude', 'playgroundweather')
            ),
            'attributes' => array(
                'value' => 0,
                'id' => 'latitude2',
            ),
        ));


        $this->add(array(
            'name' => 'longitude2',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => $translator->translate('longitude', 'playgroundweather')
            ),
            'attributes' => array(
                'value' => 0,
                'id' => 'longitude2',
            ),
        ));

        $locations = $this->getLocations();
        $this->add(array(
            'name' => 'locationsCheckboxes',
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'options' => array(
                'label' => $translator->translate('Locations', 'playgroundweather'),
                'value_options' => $locations,
            ),
            'attributes' => array(
                'id' => 'locations',
            ),
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

    public function getLocations()
    {
        $locations = array();
        $locationMapper = $this->getServiceManager()->get('playgroundweather_location_mapper');
        $results = $locationMapper->findAll(array('city'=>'ASC'));
        foreach ($results as $result) {
            $locations[$result->getId()] = $result->getCity() . ' (' . $result->getCountry() . ')';
        }
        return $locations;
    }

    public function getCountries()
    {
        $countries = array();
        $locationMapper = $this->getServiceManager()->get('playgroundweather_location_mapper');

        $results = $locationMapper->getCountries();
        foreach ($results as $result) {
            $countries[current($result)] = current($result);
        }
        return $countries;
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