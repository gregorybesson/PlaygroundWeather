<?php

namespace PlaygroundWeatherTest\Controller\Admin;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use PlaygroundWeather\Entity\Location;

class LocationControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../TestConfig.php'
        );

        parent::setUp();
    }

    public function testCreateAction()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminLocationService = $this->getMockBuilder('PlaygroundWeather\Service\Location')
        ->setMethods(array('create'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundweather_location_service', $adminLocationService);

        $location = new Location();

        $adminLocationService->expects($this->any())
        ->method('create')
        ->will($this->returnValue($location));

        $response = $this->dispatch('/admin/weather/locations/create/Toulouse/France/Franche-Comte/46.817/5.583');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('playgroundweather');
        $this->assertControllerClass('LocationController');
        $this->assertActionName('create');
        $this->assertRedirectTo('/admin/weather/locations/list');
    }

    public function testCreateActionNotWorking()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminLocationService = $this->getMockBuilder('PlaygroundWeather\Service\Location')
        ->setMethods(array('create'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundweather_location_service', $adminLocationService);
        $adminLocationService->expects($this->any())
        ->method('create')
        ->will($this->returnValue(false));

        $response = $this->dispatch('/admin/weather/locations/create/Toulouse/France/Franche-Comte/46.817/5.583');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('playgroundweather');
        $this->assertControllerClass('LocationController');
        $this->assertActionName('create');
        $this->assertRedirectTo('/admin/weather/locations/add');
    }

    public function testCreateActionMissingParams()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminLocationService = $this->getMockBuilder('PlaygroundWeather\Service\Location')
        ->setMethods(array('create'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundweather_location_service', $adminLocationService);

        $location = new Location();

        $adminLocationService->expects($this->any())
        ->method('create')
        ->will($this->returnValue($location));

        $response = $this->dispatch('/admin/weather/locations/create');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('playgroundweather');
        $this->assertControllerClass('LocationController');
        $this->assertActionName('create');
        $this->assertRedirectTo('/admin/weather/locations/add');
    }

    public function testRemoveAction()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminLocationService = $this->getMockBuilder('PlaygroundWeather\Service\Location')
        ->setMethods(array('remove'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundweather_location_service', $adminLocationService);

        $adminLocationService->expects($this->any())
        ->method('remove')
        ->will($this->returnValue(true));

        $response = $this->dispatch('/admin/weather/locations/remove/1');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('playgroundweather');
        $this->assertControllerClass('LocationController');
        $this->assertActionName('remove');
        $this->assertRedirectTo('/admin/weather/locations/list');
    }

//     public function testRemoveActionNoParam()
//     {
//         $serviceManager = $this->getApplicationServiceLocator();
//         $serviceManager->setAllowOverride(true);

//         $adminLocationService = $this->getMockBuilder('PlaygroundWeather\Service\Location')
//         ->setMethods(array('remove'))
//         ->disableOriginalConstructor()
//         ->getMock();

//         $serviceManager->setService('playgroundweather_location_service', $adminLocationService);

//         $adminLocationService->expects($this->any())
//         ->method('remove')
//         ->will($this->returnValue(true));

//         $response = $this->dispatch('/admin/weather/locations/remove');
//         $this->assertResponseStatusCode(404);
//         $this->assertModuleName('playgroundweather');
//         $this->assertControllerClass('LocationController');
//         $this->assertActionName('remove');
//         $this->assertRedirectTo('/admin/weather/locations/list');
//     }
}