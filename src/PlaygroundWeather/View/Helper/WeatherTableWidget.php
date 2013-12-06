<?php

namespace PlaygroundWeather\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use PlaygroundWeather\Service\WeatherDataUse;
use PlaygroundWeather\Options\ModuleOptions;

use Zend\View\Model\ViewModel;

class WeatherTableWidget extends AbstractHelper implements ServiceLocatorAwareInterface
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
//         if (array_key_exists('location', $options)) {
//             $location = $options['location'];
//         } else {
//             $location = null;
//         }
//         if (array_key_exists('startDate', $options)) {
//             $startDate = $options['startDate'];
//         } else {
//             $startDate = null;
//         }


        $widgetModel = new ViewModel();
        $this->setWidgetTemplate($this->getOptions()->getTableWidgetTemplate());

        var_dump($this->widgetTemplate);
        $widgetModel->setTemplate($this->widgetTemplate);
        $widgetModel->setVariables(array('data'=> 'bla'));
        //return $this->getView()->render($widgetModel);
         return true;
    }

//     public function getView()
//     {
//         // TODO: Auto-generated method stub
//     }


//     public function setView($view) {

//     }

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
            $this->weatherDataUseService = $this->getServiceLocator()->get('playgroundweather_weatherdatause_service');
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
