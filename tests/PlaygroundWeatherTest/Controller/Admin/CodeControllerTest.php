<?php

namespace PlaygroundWeatherTest\Controller\Admin;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class CodeControllerTest extends AbstractHttpControllerTestCase
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

        $adminCodeService = $this->getMockBuilder('PlaygroundWeather\Service\Code')
        ->setMethods(array('remove'))
        ->disableOriginalConstructor()
        ->getMock();

        $serviceManager->setService('playgroundweather_code_service', $adminCodeService);

        $adminCodeService->expects($this->any())
        ->method('remove')
        ->will($this->returnValue(true));

        $response = $this->dispatch('/admin/weather/codes/remove/1');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('playgroundweather');
        $this->assertControllerClass('CodeController');
        $this->assertActionName('remove');
        $this->assertRedirectTo('/admin/weather/codes/list');
    }
}