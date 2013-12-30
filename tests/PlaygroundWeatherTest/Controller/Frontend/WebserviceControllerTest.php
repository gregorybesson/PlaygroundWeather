<?php

namespace PlaygroundWeatherTest\Controller\Frontend;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class WebSsrviceControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../TestConfig.php'
        );

        parent::setUp();
    }

    public function testGetListNoStartingDate()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminDataUseService = $this->getMockBuilder('PlaygroundWeather\Service\DataUse')
        ->setMethods(array('getLocationWeather', 'getDailyWeatherAsArray'))
        ->disableOriginalConstructor()
        ->getMock();

        $adminLocationService = $this->getMockBuilder('PlaygroundWeather\Service\Location')
        ->setMethods(array('getLocationMapper'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundweather_datause_service', $adminDataUseService);
        $serviceManager->setService('playgroundweather_location_service', $adminLocationService);

        $adminLocationMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Location')
        ->setMethods(array('findById'))
        ->disableOriginalConstructor()
        ->getMock();

        $location = new \PlaygroundWeather\Entity\Location();

        $adminLocationService->expects($this->never())
        ->method('getLocationMapper')
        ->will($this->returnValue($adminLocationMapper));

        $adminLocationMapper->expects($this->never())
        ->method('findById')
        ->will($this->returnValue($location));

        $adminDataUseService->expects($this->never())
        ->method('getLocationWeather')
        ->will($this->returnValue(array()));

        $adminDataUseService->expects($this->never())
        ->method('getDailyWeatherAsArray')
        ->will($this->returnValue(array()));

        $response = $this->dispatch('/GET/weather/1');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('playgroundweather');
        $this->assertControllerClass('WebserviceController');
        $this->assertActionName('getList');
    }

    public function testGetList()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminDataUseService = $this->getMockBuilder('PlaygroundWeather\Service\DataUse')
        ->setMethods(array('getLocationWeather', 'getDailyWeatherAsArray'))
        ->disableOriginalConstructor()
        ->getMock();

        $adminLocationService = $this->getMockBuilder('PlaygroundWeather\Service\Location')
        ->setMethods(array('getLocationMapper'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundweather_datause_service', $adminDataUseService);
        $serviceManager->setService('playgroundweather_location_service', $adminLocationService);

        $adminLocationMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Location')
        ->setMethods(array('findById'))
        ->disableOriginalConstructor()
        ->getMock();

        $location = new \PlaygroundWeather\Entity\Location();

        $adminLocationService->expects($this->once())
        ->method('getLocationMapper')
        ->will($this->returnValue($adminLocationMapper));

        $adminLocationMapper->expects($this->once())
        ->method('findById')
        ->will($this->returnValue($location));

        $adminDataUseService->expects($this->once())
        ->method('getLocationWeather')
        ->will($this->returnValue(array()));

        $adminDataUseService->expects($this->never())
        ->method('getDailyWeatherAsArray')
        ->will($this->returnValue(array()));

        $response = $this->dispatch('/GET/weather/1/2013-12-25');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('playgroundweather');
        $this->assertControllerClass('WebserviceController');
        $this->assertActionName('getList');
    }
}