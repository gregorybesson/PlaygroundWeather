<?php

namespace PlaygroundWeather\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use PlaygroundWeather\Service\WeatherDataUse;
use PlaygroundWeather\Options\ModuleOptions;

use Zend\View\Model\ViewModel;
use DateTime;

class WeatherImageWidget extends AbstractHelper implements ServiceLocatorAwareInterface
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
     * @var WeatherDataUse
     */
    protected $weatherDataUseService;

    /**
     * $var string template used for view
     */
    protected $widgetTemplate;

    public function __invoke($params=array())
    {
//         if (array_key_exists('mapImage', $params) && $params['mapImage'] instanceof MapImage) {
//             $mapImage = $params['mapImage'];
//         } else {
//             $mapImage = null;
//         }
        if (array_key_exists('locations', $params) && is_array($params['locations'])) {
            $locations = $params['locations'];
        } else {
            $locations = array();
        }

        if (array_key_exists('template', $params)) {
            $this->setWidgetTemplate($params['template']);
        } else {
             $this->setWidgetTemplate($this->getOptions()->getImageWidgetTemplate());;
        }

        $data = array();

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

    public function getWeatherDataUseService()
    {
        $sm = $this->getServiceLocator()->getServiceLocator();
        if ($this->weatherDataUseService === null) {
            $this->weatherDataUseService = $sm->get('playgroundweather_weatherdatause_service');
        }
        return $this->weatherDataUseService;
    }

    public function setWeatherDataUseService($weatherDataUseService)
    {
        $this->weatherDataUseService = $weatherDataUseService;

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