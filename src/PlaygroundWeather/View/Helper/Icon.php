<?php

namespace PlaygroundWeather\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PlaygroundWeather\Mapper\Code as CodeMapper;

class Icon extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var CodeMapper
     */
    protected $codeMapper;

    public function __invoke($code = '')
    {
        if (!$code) {
            return '';
        }
        $weatherCode = $this->getCodeMapper()->findOneBy(array('value'=>$code, 'isDefault' => 0));
        if (!$weatherCode) {
            $weatherCode = $this->getCodeMapper()->findDefaultByCode($code);
        }
        if (!$weatherCode) {
            return '';
        }
       $weatherCode = $this->getCodeMapper()->findLastAssociatedCode($weatherCode);
        return $weatherCode->getIconURL();
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

    public function getCodeMapper()
    {
        if ($this->codeMapper === null) {
            $this->codeMapper = $this->getServiceLocator()->getServiceLocator()->get('playgroundweather_code_mapper');
        }
        return $this->codeMapper;
    }

    public function setCodeMapper($codeMapper)
    {
        $this->codeMapper = $codeMapper;
        return $this;
    }
}