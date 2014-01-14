<?php

namespace PlaygroundWeatherTest\View\Helper;

class TemperatureTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        parent::setUp();
    }

    public function testHelperCelcius()
    {
        $helper = new \PlaygroundWeather\View\Helper\Temperature();

        $this->assertEquals('5°C', $helper(5, 'fr_FR'));
        $this->assertEquals('5°C', $helper(5, 'en_UK'));
    }

    public function testHelperAllFahrenheitLocales()
    {
        $helper = new \PlaygroundWeather\View\Helper\Temperature();

        $this->assertEquals('-40°F', $helper(-40, 'en_US'));
        $this->assertEquals('-40°F', $helper(-40, 'en_BZ'));
        $this->assertEquals('-40°F', $helper(-40, 'en_KY'));

    }

    public function testHelperParticularValues()
    {
        $helper = new \PlaygroundWeather\View\Helper\Temperature();

        $this->assertEquals('-40°F', $helper(-40, 'en_US'));
        $this->assertEquals('32°F', $helper(0, 'en_US'));
        $this->assertEquals('68°F', $helper(20, 'en_US'));
        $this->assertEquals('50°F', $helper(10, 'en_US'));
        $this->assertEquals('122°F', $helper(50, 'en_US'));
        $this->assertEquals('212°F', $helper(100, 'en_US'));
    }

    public function testHelperFloat()
    {
        $helper = new \PlaygroundWeather\View\Helper\Temperature();

        $this->assertEquals('32.5°C', $helper(32.5, 'en_UK'));
        $this->assertEquals('-40.12°C', $helper(-40.12, 'en_UK'));
    }

    public function testHelperNAN()
    {
        $helper = new \PlaygroundWeather\View\Helper\Temperature();

        $this->assertEquals('', $helper('ga', 'en_UK'));
        $this->assertEquals('', $helper('aaz', 'en_US'));
    }
}