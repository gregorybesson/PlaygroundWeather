<?php
namespace PlaygroundWeather\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use PlaygroundWeather\Service\DataYield as DataYieldService;
use Zend\View\Model\ViewModel;
use DateTime;

class AdminController extends AbstractActionController
{
    /**
     * @var DataYieldService
     */
    protected $dataYieldService;

    public function adminAction()
    {

        $options = $this->getDataYieldService()->getOptions();
        return new ViewModel(array());
    }

    public function getDataYieldService()
    {
        if ($this->dataYieldService === null) {
            $this->dataYieldService = $this->getServiceLocator()->get('playgroundweather_datayield_service');
        }
        return $this->dataYieldService;
    }

    public function setDataYieldService($dataYieldService)
    {
        $this->dataYieldService = $dataYieldService;

        return $this;
    }

}