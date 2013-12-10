<?php
namespace PlaygroundWeather\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PlaygroundWeather\Service\WeatherLocation as WeatherLocationService;
use PlaygroundWeather\Service\WeatherDataUse as WeatherDataUseService;
use Datetime;

class PlaygroundWeatherController extends AbstractActionController
{
    /**
     * @var WeatherLocationService
     */
    protected $weatherLocationService;

    protected $weatherDataUseService;

    public function indexAction()
    {
        $now = new Datetime("2013-12-03");

        return new ViewModel(array(
        ));
    }

    public function getWeatherLocationService()
    {
        if ($this->weatherLocationService === null) {
            $this->weatherLocationService = $this->getServiceLocator()->get('playgroundweather_weatherlocation_service');
        }
        return $this->weatherLocationService;
    }

    public function setWeatherLocationService($weatherLocationService)
    {
        $this->weatherLocationService = $weatherLocationService;

        return $this;
    }

    public function getWeatherDataUseService()
    {
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

}