<?php
namespace PlaygroundWeather\Form\Admin;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;
use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use PlaygroundWeather\Entity\WeatherImageMap as ImageMapEntity;
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

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'country',
            'options' => array(
                'value_options' => Location::$countries,
                'label' => $translator->translate('Country', 'playgroundweather')
            )
        ));

        $this->add(array(
            'name' => 'image',
            'options' => array(
                'label' => $translator->translate('Image', 'playgroundweather')
            ),
            'attributes' => array(
                'type' => 'file'
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
            'name' => 'width',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'height',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'topLeftLatitude',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => $translator->translate('Top left latitude', 'playgroundweather')
            ),
            'attributes' => array(
                'value' => 0,
            ),
        ));


        $this->add(array(
            'name' => 'topLeftLongitude',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => $translator->translate('Top left longitude', 'playgroundweather')
            ),
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'bottomRightLatitude',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => $translator->translate('Bottom right latitude', 'playgroundweather')
            ),
            'attributes' => array(
                'value' => 0,
            ),
        ));


        $this->add(array(
            'name' => 'bottomRightLongitude',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => $translator->translate('Bottom right longitude', 'playgroundweather')
            ),
            'attributes' => array(
                'value' => 0,
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