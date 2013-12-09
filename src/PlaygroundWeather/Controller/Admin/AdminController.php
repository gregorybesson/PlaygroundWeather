<?php
namespace PlaygroundWeather\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use PlaygroundWeather\Service\WeatherLocation as WeatherLocationService;
use Zend\View\Model\ViewModel;
use DateTime;

class AdminController extends AbstractActionController
{
    /**
     * @var WeatherLocationService
     */
    protected $weatherLocationService;

    public function adminAction()
    {
        $place = $this->getWeatherLocationService()->getWeatherLocationMapper()->getDefaultLocation();
        $time = new DateTime();
        $time1 = new DateTime();
        return new ViewModel(array(
            'location'=>$place,
            'startDate'=> new DateTime(),
            'endDate'=> new DateTime('2013-12-13'),
            'times' => array($time->setTime(10,0), $time1->setTime(16,0))
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

}