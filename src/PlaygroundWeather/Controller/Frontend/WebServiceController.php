<?php
namespace PlaygroundWeather\Controller\Frontend;

use Zend\Mvc\Controller\AbstractRestfulController;

use PlaygroundWeather\Service\Location as LocationService;
use PlaygroundWeather\Service\DataYield as DataYieldService;
use PlaygroundWeather\Service\DataUse as DataUseService;
use DateTime;

use Zend\View\Model\JsonModel;

class WebServiceController extends AbstractRestfulController
{
    /**
     * @var LocationService
     */
    protected $locationService;

    /**
     * @var dataUseService
     */
    protected $dataUseService;

    public function getList()
    {
        $locationId = $this->getEvent()->getRouteMatch()->getParam('locationId');
        $startStr = $this->getEvent()->getRouteMatch()->getParam('start');
        $endStr = $this->getEvent()->getRouteMatch()->getParam('end');

        if (!$startStr || !$locationId) {
            $data = '[ERROR] missing arguments';
            return new JsonModel(array('data' => $data));
        }

        $location = $this->getLocationService()->getLocationMapper()->findById($locationId);
        $start = new DateTime($startStr);
        $result = array();
        if ($endStr) {
            $end = new DateTime($endStr);
            $diff = $start->diff($end);
            if ($diff->days > 1 && !$diff->invert) {
                $data = $this->getDataUseService()->getLocationWeather($location, $start, $diff->days + 1);
                if ($data) {
                    foreach ($data as $day) {
                        $result[] =  $this->getDataUseService()->getDailyWeatherAsArray($day);
                    }
                }
                return new JsonModel(array('data' => $result));
            }
        }
        $data = $this->getDataUseService()->getLocationWeather($location, $start);
        if (current($data)) {
            $result =  $this->getDataUseService()->getDailyWeatherAsArray(current($data));
        }
        return new JsonModel(array('data' => $result));
    }

    public function getLocationService()
    {
        if ($this->locationService === null) {
            $this->locationService = $this->getServiceLocator()->get('playgroundweather_location_service');
        }
        return $this->locationService;
    }

    public function setLocationService($locationService)
    {
        $this->locationService = $locationService;

        return $this;
    }

    public function getDataUseService()
    {
        if ($this->dataUseService === null) {
            $this->dataUseService = $this->getServiceLocator()->get('playgroundweather_datause_service');
        }
        return $this->dataUseService;
    }

    public function setWeatherDataUseService($dataUseService)
    {
        $this->dataUseService = $dataUseService;

        return $this;
    }

}