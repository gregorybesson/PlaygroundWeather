<?php

namespace PlaygroundWeather\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use PlaygroundWeather\Service\WeatherDataUse;
use PlaygroundWeather\Options\ModuleOptions;

use Zend\View\Model\ViewModel;
use DateTime;

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
        if (array_key_exists('location', $params)) {
            $location = $params['location'];
        } else {
            $location = null;
        }
        if (array_key_exists('startDate', $params)) {
            $startDate = $params['startDate'];
        } else {
            $startDate = new DateTime();
        }
        if (array_key_exists('endDate', $params)) {
            $endDate = $params['endDate'];
        } else {
            $endDate = new DateTime();
        }
        if (array_key_exists('times', $params)) {
            $times = $params['times'];
        } else {
            $times = array();
        }
        $data = null;
        if ($location) {
            $startDate->setTime(0,0);

            $endDate->setTime(0,0);
            $diff = $startDate->diff($endDate);
            $numDays = $diff->days + 1;
            $data = $this->getWeatherDataUseService()->getDailyWeatherForTimesAsArray($location, $startDate, $numDays, $times);
        }
        var_dump($data);

        $widgetModel = new ViewModel();
        $this->setWidgetTemplate($this->getOptions()->getTableWidgetTemplate());

        $widgetModel->setTemplate($this->widgetTemplate);
        $widgetModel->setVariables(array('data'=> $data));
        return $this->getView()->render($widgetModel);
    }

    public function generateDataArray(){

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
