<?php

namespace PlaygroundWeather\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use PlaygroundWeather\Service\DataUse;
use PlaygroundWeather\Entity\ImageMap as ImageMapEntity;
use PlaygroundWeather\Service\ImageMap as ImageMapService;
use PlaygroundWeather\Options\ModuleOptions;

use Zend\View\Model\ViewModel;
use DateTime;

class ImageWidget extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var ImageMapService
     */
    protected $imageMapService;

    /**
     * @var DataUse
     */
    protected $dataUseService;

    /**
     * $var string template used for view
     */
    protected $widgetTemplate;

    public function __invoke($params=array())
    {
        if (array_key_exists('imageMap', $params) && $params['imageMap'] instanceof ImageMapEntity) {
            $imageMap = $params['imageMap'];
        } else {
            $imageMap = $this->getImageMapService()->getImageMapMapper()->getDefault();
        }
        if (array_key_exists('date', $params) && $params['date'] instanceof DateTime) {
            $date = $params['date'];
        } else {
            $date = new DateTime();
        }
        if (array_key_exists('locations', $params) && is_array($params['locations'])) {
            $location = $params['locations'];
        } else {
            $locations = $imageMap->getLocations();
        }
        if (array_key_exists('template', $params)) {
            $this->setWidgetTemplate($params['template']);
        } else {
             $this->setWidgetTemplate($this->getOptions()->getImageWidgetTemplate());;
        }

        $data = array();
        $data['map'] = array();
        $data['map']['url']= $imageMap->getImageURL();
        $data['map']['width'] = $imageMap->getImageWidth();
        $data['map']['height'] = $imageMap->getImageHeight();
        $data['locations'] = array();
        $data['day'] = $date;
        $locationData = $this->getDataUseService()->getDailyWeatherForLocationsAsArray($locations, $date);
        foreach ($locationData as $location) {
            $locArray = $location;
            $coor = $this->getImageMapService()->getPosition($imageMap, $location['location']->getLatitude(), $location['location']->getLongitude());
            $locArray['cooX'] = current($coor);
            $locArray['cooY'] = end($coor);
            array_push($data['locations'] , $locArray);
        }

        $widgetModel = new ViewModel();
        $widgetModel->setTemplate($this->widgetTemplate);
        $widgetModel->setVariables(array('data'=> $data));
        return $this->getView()->render($widgetModel);
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setWidgetTemplate($widgetTemplate)
    {
        $this->widgetTemplate = $widgetTemplate;
        return $this;
    }

    public function getDataUseService()
    {
        $sm = $this->getServiceLocator()->getServiceLocator();
        if ($this->dataUseService === null) {
            $this->dataUseService = $sm->get('playgroundweather_datause_service');
        }
        return $this->dataUseService;
    }

    public function setDataUseService($dataUseService)
    {
        $this->dataUseService = $dataUseService;

        return $this;
    }

    public function getImageMapService()
    {
        $sm = $this->getServiceLocator()->getServiceLocator();
        if ($this->imageMapService === null) {
            $this->imageMapService = $sm->get('playgroundweather_imagemap_service');
        }
        return $this->imageMapService;
    }

    public function setImageMapService($imageMapService)
    {
        $this->imageMapService = $imageMapService;

        return $this;
    }

    public function getOptions()
    {
        $sm = $this->getServiceLocator()->getServiceLocator();
        if ($this->options === null) {
            $this->options = $sm->get('playgroundweather_module_options');
        }
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }
}