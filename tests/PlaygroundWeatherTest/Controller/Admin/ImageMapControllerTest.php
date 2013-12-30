<?php

namespace PlaygroundWeatherTest\Controller\Admin;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ImageMapControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../TestConfig.php'
        );

        parent::setUp();
    }

    public function testRemoveAction()
    {
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);

        $adminImageMapService = $this->getMockBuilder('PlaygroundWeather\Service\ImageMap')
        ->setMethods(array('remove'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundweather_location_service', $adminImageMapService);

        $adminImageMapService->expects($this->any())
        ->method('remove')
        ->will($this->returnValue(true));

        $response = $this->dispatch('/admin/weather/images/remove/1');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('playgroundweather');
        $this->assertControllerClass('ImageMapController');
        $this->assertActionName('remove');
        $this->assertRedirectTo('/admin/weather/images');
    }
}