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
        $place = $this->getWeatherLocationService()->getWeatherLocationMapper()->findById(8);
        $time = new DateTime();
        $time1 = new DateTime();
        $time2 = new DateTime();
        return new ViewModel(array(
            'location'=>$place,
            'startDate'=> new DateTime('2013-12-02'),
            'endDate'=> new DateTime('2013-12-12'),
            'times' => array($time->setTime(10,0), $time1->setTime(16,0), $time2->setTime(18,0)),
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