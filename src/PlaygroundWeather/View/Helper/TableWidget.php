<?php

namespace PlaygroundWeather\View\Helper;

use Zend\View\Helper\AbstractHelper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use PlaygroundWeather\Service\DataUse;
use PlaygroundWeather\Options\ModuleOptions;

use Zend\View\Model\ViewModel;
use DateTime;

class TableWidget extends AbstractHelper implements ServiceLocatorAwareInterface
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
     * @var DataUse
     */
    protected $dataUseService;

    /**
     * $var string template used for view
     */
    protected $widgetTemplate;

    public function __invoke($params=array())
    {
        if (array_key_exists('location', $params) && $params['location']!==null) {
            $location = $params['location'];
        } else {
            $location = $this->getDataUseService()->getLocationMapper()->getDefaultLocation();
        }
        if (array_key_exists('startDate', $params) && $params['startDate']!==null) {
            $startDate = $params['startDate'];
        } else {
            $startDate = new DateTime();
        }
        if (array_key_exists('endDate', $params) && $params['endDate']!==null) {
            $endDate = $params['endDate'];
        } else {
            $endDate = null;
        }
        if (array_key_exists('times', $params) && is_array($params['times'])) {
            $times = $params['times'];
        } else {
            $times = array();
        }

        if (array_key_exists('template', $params)) {
            $this->setWidgetTemplate($params['template']);
        } else {
             $this->setWidgetTemplate($this->getOptions()->getTableWidgetTemplate());;
        }
        $data = null;
        if ($location) {
            if ($endDate) {
                $startDate->setTime(0,0);
                $endDate->setTime(0,0);
                $diff = $startDate->diff($endDate);
                $numDays = $diff->days + 1;
            } else {
                $numDays = 1;
            }
            $data = $this->getDataUseService()->getDailyWeatherForTimesAsArray($location, $startDate, $numDays, $times);
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
