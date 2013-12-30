<?php

namespace PlaygroundWeatherTest\Service;

use PlaygroundWeather\Entity\Location;
use PlaygroundWeather\Entity\Code;
use PlaygroundWeather\Entity\DailyOccurrence;
use PlaygroundWeather\Entity\HourlyOccurrence;
use \Datetime;

class DataUseTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        parent::setUp();
    }

    public function testGetLocationWeather()
    {
        $dailyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\DailyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $dataYieldService = $this->getMockBuilder('PlaygroundWeather\Service\DataYield')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataUse();
        $ws->setDailyOccurrenceMapper($dailyMapper);
        $ws->setDataYieldService($dataYieldService);

        $daily = new DailyOccurrence();

        $location = new Location();
        $date = new DateTime();

        $ws->getDataYieldService()
        ->expects($this->once())
        ->method('isPastDate')
        ->with($this->isInstanceOf('DateTime'))
        ->will($this->returnValue(true));

        $ws->getDailyOccurrenceMapper()
        ->expects($this->once())
        ->method('findOneBy')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Location'), $this->isInstanceOf('DateTime'), $this->isType('boolean'))
        ->will($this->returnValue($daily));

        $this->assertContains($daily, $ws->getLocationWeather($location, $date));
    }

    public function testGet3DaysLocationWeather()
    {
        $dailyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\DailyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $dataYieldService = $this->getMockBuilder('PlaygroundWeather\Service\DataYield')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataUse();
        $ws->setDailyOccurrenceMapper($dailyMapper);
        $ws->setDataYieldService($dataYieldService);

        $daily = new DailyOccurrence();

        $location = new Location();
        $date = new DateTime();

        $ws->getDataYieldService()
        ->expects($this->exactly(3))
        ->method('isPastDate')
        ->with($this->isInstanceOf('DateTime'))
        ->will($this->returnValue(true));

        $ws->getDailyOccurrenceMapper()
        ->expects($this->exactly(3))
        ->method('findOneBy')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Location'), $this->isInstanceOf('DateTime'), $this->isType('boolean'))
        ->will($this->returnValue($daily));

        $this->assertCount(3, $ws->getLocationWeather($location, $date, 3));
    }

    public function testGetNoLocationWeather()
    {
        $dailyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\DailyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $dataYieldService = $this->getMockBuilder('PlaygroundWeather\Service\DataYield')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataUse();
        $ws->setDailyOccurrenceMapper($dailyMapper);
        $ws->setDataYieldService($dataYieldService);

        $daily = new DailyOccurrence();

        $location = new Location();
        $date = new DateTime();

        $ws->getDataYieldService()
        ->expects($this->exactly(2))
        ->method('isPastDate')
        ->with($this->isInstanceOf('DateTime'))
        ->will($this->returnValue(true));

        $ws->getDailyOccurrenceMapper()
        ->expects($this->exactly(4))
        ->method('findOneBy')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Location'), $this->isInstanceOf('DateTime'), $this->isType('boolean'))
        ->will($this->returnValue(false));

        $this->assertEmpty($ws->getLocationWeather($location, $date, 2));
    }

    public function testGetCloserNoHourlyOccurrence()
    {
        $hourlyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\HourlyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $daily = new DailyOccurrence();
        $time = new DateTime();

        $ws = new \PlaygroundWeather\Service\DataUse();
        $ws->setHourlyOccurrenceMapper($hourlyMapper);

        $ws->getHourlyOccurrenceMapper()
        ->expects($this->exactly(1))
        ->method('findByDailyOccurrence')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(array()));

        $this->assertNull($ws->getCloserHourlyOccurrence($daily, $time));
    }

    public function testGetCloserOneHourlyOccurrence()
    {
        $hourlyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\HourlyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $daily = new DailyOccurrence();
        $time = new DateTime();
        $time = $time->setTime(0, 0);

        $hourly = new HourlyOccurrence();
        $time1 = new DateTime();
        $hourly->setTime($time1->setTime(1, 0));

        $ws = new \PlaygroundWeather\Service\DataUse();
        $ws->setHourlyOccurrenceMapper($hourlyMapper);

        $ws->getHourlyOccurrenceMapper()
        ->expects($this->exactly(1))
        ->method('findByDailyOccurrence')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(array($hourly)));

        $this->assertInstanceOf('PlaygroundWeather\Entity\HourlyOccurrence', $ws->getCloserHourlyOccurrence($daily, $time));
    }

    public function testGetCloserOneHourlyOccurrenceIfEqualsPreviousReturned()
    {
        $hourlyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\HourlyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $daily = new DailyOccurrence();
        $time = new DateTime();
        $time = $time->setTime(2, 0);

        $hourly = new HourlyOccurrence();
        $time1 = new DateTime();
        $hourly->setTime($time1->setTime(1, 0));

        $hourly2 = new HourlyOccurrence();
        $time2 = new DateTime();
        $hourly2->setTime($time2->setTime(3, 0));

        $ws = new \PlaygroundWeather\Service\DataUse();
        $ws->setHourlyOccurrenceMapper($hourlyMapper);

        $ws->getHourlyOccurrenceMapper()
        ->expects($this->exactly(1))
        ->method('findByDailyOccurrence')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(array($hourly, $hourly2)));

        $this->assertInstanceOf('PlaygroundWeather\Entity\HourlyOccurrence', $ws->getCloserHourlyOccurrence($daily, $time));
    }

    public function testGetCloserHourlyOccurrenceOneMatch()
    {
        $hourlyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\HourlyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $daily = new DailyOccurrence();
        $time = new DateTime();
        $time = $time->setTime(2, 0);

        $hourly = new HourlyOccurrence();
        $time1 = new DateTime();
        $hourly->setTime($time1->setTime(1, 0));

        $hourly2 = new HourlyOccurrence();
        $time2 = new DateTime();
        $hourly2->setTime($time2->setTime(3, 0));

        $hourly3 = new HourlyOccurrence();
        $time3 = new DateTime();
        $hourly3->setTime($time3->setTime(2, 0));


        $ws = new \PlaygroundWeather\Service\DataUse();
        $ws->setHourlyOccurrenceMapper($hourlyMapper);

        $ws->getHourlyOccurrenceMapper()
        ->expects($this->exactly(1))
        ->method('findByDailyOccurrence')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(array($hourly, $hourly2, $hourly3)));

        $this->assertInstanceOf('PlaygroundWeather\Entity\HourlyOccurrence', $ws->getCloserHourlyOccurrence($daily, $time));
    }

    public function testGetCloserHourlyOccurrenceManyHourlies()
    {
        $hourlyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\HourlyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $daily = new DailyOccurrence();
        $time = new DateTime();
        $time = $time->setTime(1, 45);

        $hourly = new HourlyOccurrence();
        $time1 = new DateTime();
        $hourly->setTime($time1->setTime(1, 0));

        $hourly2 = new HourlyOccurrence();
        $time2 = new DateTime();
        $hourly2->setTime($time2->setTime(3, 0));

        $hourly3 = new HourlyOccurrence();
        $time3 = new DateTime();
        $hourly3->setTime($time3->setTime(2, 0));

        $ws = new \PlaygroundWeather\Service\DataUse();
        $ws->setHourlyOccurrenceMapper($hourlyMapper);

        $ws->getHourlyOccurrenceMapper()
        ->expects($this->exactly(1))
        ->method('findByDailyOccurrence')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(array($hourly, $hourly2, $hourly3)));

        $this->assertInstanceOf('PlaygroundWeather\Entity\HourlyOccurrence', $ws->getCloserHourlyOccurrence($daily, $time));
    }

    public function testGetDailyWeatherAsArray()
    {
        $hourlyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\HourlyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $code = new Code();
        $location = new Location();
        $daily = new DailyOccurrence();
        $daily->setLocation($location);
        $daily->setcode($code);

        $ws = new \PlaygroundWeather\Service\DataUse();
        $ws->setHourlyOccurrenceMapper($hourlyMapper);
        $ws->setCodeMapper($codeMapper);

        $ws->getHourlyOccurrenceMapper()
        ->expects($this->once())
        ->method('findByDailyOccurrence')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(array()));

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('findLastAssociatedCode')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Code'))
        ->will($this->returnValue($code));

        $this->assertNotEmpty($ws->getDailyWeatherAsArray($daily));
    }

    public function testGetDailyWeatherAsArrayWithHourlies()
    {
        $hourlyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\HourlyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $code = new Code();
        $location = new Location();
        $daily = new DailyOccurrence();
        $daily->setLocation($location);
        $daily->setcode($code);
        $hourly = new HourlyOccurrence();
        $hourly->setcode($code);
        $hourly->setDailyOccurrence($daily);

        $ws = new \PlaygroundWeather\Service\DataUse();
        $ws->setHourlyOccurrenceMapper($hourlyMapper);
        $ws->setCodeMapper($codeMapper);

        $ws->getHourlyOccurrenceMapper()
        ->expects($this->once())
        ->method('findByDailyOccurrence')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(array($hourly, $hourly)));

        $ws->getCodeMapper()
        ->expects($this->exactly(3))
        ->method('findLastAssociatedCode')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Code'))
        ->will($this->returnValue($code));

        $this->assertNotEmpty($ws->getDailyWeatherAsArray($daily));
    }
}