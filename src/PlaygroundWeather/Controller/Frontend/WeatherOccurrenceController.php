<?php
namespace PlaygroundWeather\Controller\Frontend;

use Zend\Mvc\Controller\AbstractRestfulController;

use PlaygroundWeather\Service\WeatherLocation as WeatherLocationService;
use PlaygroundWeather\Service\WeatherDataYield as WeatherDataYieldService;
use PlaygroundWeather\Service\WeatherDataUse as WeatherDataUseService;
use DateTime;

use Zend\View\Model\JsonModel;

class WeatherOccurrenceController extends AbstractRestfulController
{
    /**
     * @var WeatherLocationService
     */
    protected $weatherLocationService;

    /**
     * @var WeatherDataUseService
     */
    protected $weatherDataUseService;

    public function getList()
    {
        $locationId = $this->getEvent()->getRouteMatch()->getParam('locationId');
        $startStr = $this->getEvent()->getRouteMatch()->getParam('start');
        $endStr = $this->getEvent()->getRouteMatch()->getParam('end');

        if (!$startStr || !$locationId) {
            $data = '[ERROR] missing arguments';
            return new JsonModel(array('data' => $data));
        }

        $location = $this->getWeatherLocationService()->getWeatherLocationMapper()->findById($locationId);
        $start = new DateTime($startStr);
        $result = array();
        if ($endStr) {
            $end = new DateTime($endStr);
            $diff = $start->diff($end);
            if ($diff->days > 1 && !$diff->invert) {
                $data = $this->getWeatherDataUseService()->getLocationWeather($location, $start, $diff->days + 1);
                if ($data) {
                    foreach ($data as $day) {
                        $result[] =  $this->getWeatherDataUseService()->getDailyWeatherAsArray($day);
                    }
                }
                return new JsonModel(array('data' => $result));
            }
        }
        $data = $this->getWeatherDataUseService()->getLocationWeather($location, $start);
        if (current($data)) {
            $result =  $this->getWeatherDataUseService()->getDailyWeatherAsArray(current($data));
        }
        return new JsonModel(array('data' => $result));
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