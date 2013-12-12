<?php
namespace PlaygroundWeather\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use PlaygroundWeather\Service\Location as LocationService;
use Zend\View\Model\ViewModel;
use DateTime;

class AdminController extends AbstractActionController
{
    /**
     * @var LocationService
     */
    protected $locationService;

    public function adminAction()
    {
        return new ViewModel(array());
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

}