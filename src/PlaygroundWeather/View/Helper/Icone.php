<?php

namespace PlaygroundWeather\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Icone extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    public function __invoke($code, $default = 0)
    {
        $codeService = $this->getServiceLocator()->getServiceLocator()->get('playgroundweather_code_service');
        $codeMapper = $codeService->getCodeMapper();
        if (!$code) {
            return '';
        }
        $weatherCode = $codeMapper->findOneBy(array('value'=>$code, 'isDefault' => 0));
        if (!$weatherCode) {
            $weatherCode = $codeMapper->findDefaultByCode($code);
        }
        if ($weatherCode) {
           $weatherCode = $codeMapper->findLastAssociatedCode($weatherCode);
        }
        if (!$weatherCode->getIconURL()) {
            return '';
        }
        return '<img src="'.$weatherCode->getIconURL().'" alt="Icone du code d\'état du ciel '.$code.'"/>';
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
}