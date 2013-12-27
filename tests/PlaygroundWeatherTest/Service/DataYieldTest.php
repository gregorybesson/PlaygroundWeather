<?php

namespace PlaygroundWeatherTest\Service;

use PlaygroundWeather\Entity\Location;
use PlaygroundWeather\Entity\Code;
use PlaygroundWeather\Entity\DailyOccurrence;
use PlaygroundWeather\Entity\HourlyOccurrence;
use \Datetime;

class DataYieldTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        parent::setUp();
    }

    public function testRequestPast()
    {
        $options = $this->getMockBuilder('PlaygroundWeather\Options\ModuleOptions')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataYield();
        $ws->setOptions($options);

        $ws->getOptions()
        ->expects($this->exactly(2))
        ->method('getPastURL')
        ->will($this->returnValue('URL'));

        $ws->getOptions()
        ->expects($this->exactly(2))
        ->method('getUserKey')
        ->will($this->returnValue('AwesomeKey'));

        $this->assertEquals('URL?q=paris&date=2013-12-25&enddate=2013-12-26&includeLocation=no&format=xml&key=AwesomeKey',
                            $ws->requestPast('paris', '2013-12-25', '2013-12-26', 'no'));
        $this->assertEquals('URL?q=paris&date=2013-12-25&enddate=2013-12-26&includeLocation=yes&format=xml&key=AwesomeKey',
            $ws->requestPast('paris', '2013-12-25', '2013-12-26', 'yes'));
    }

    public function testRequestPastByRequest()
    {
        $locationService = $this->getMockBuilder('PlaygroundWeather\Service\Location')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataYield();
        $ws->setLocationService($locationService);

        $ws->getLocationService()
        ->expects($this->once())
        ->method('createQueryString')
        ->will($this->returnValue('paris'));

        $options = $this->getMockBuilder('PlaygroundWeather\Options\ModuleOptions')
        ->disableOriginalConstructor()
        ->getMock();

        $ws->setOptions($options);

        $ws->getOptions()
        ->expects($this->once())
        ->method('getPastURL')
        ->will($this->returnValue('URL'));

        $ws->getOptions()
        ->expects($this->once())
        ->method('getUserKey')
        ->will($this->returnValue('AwesomeKey'));

        $date = new Datetime('2013-12-25');

        $this->assertEquals('URL?q=paris&date=2013-12-25&enddate=2013-12-26&includeLocation=no&format=xml&key=AwesomeKey',
            $ws->request(array('paris'), $date, 2, 3, false));
    }

    public function testRequestPastByRequestDefault()
    {
        $locationService = $this->getMockBuilder('PlaygroundWeather\Service\Location')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataYield();
        $ws->setLocationService($locationService);

        $ws->getLocationService()
        ->expects($this->once())
        ->method('createQueryString')
        ->will($this->returnValue('paris'));

        $options = $this->getMockBuilder('PlaygroundWeather\Options\ModuleOptions')
        ->disableOriginalConstructor()
        ->getMock();

        $ws->setOptions($options);

        $ws->getOptions()
        ->expects($this->once())
        ->method('getForecastURL')
        ->will($this->returnValue('URL'));

        $ws->getOptions()
        ->expects($this->once())
        ->method('getUserKey')
        ->will($this->returnValue('AwesomeKey'));

        $date = new Datetime('now');
        $dateStr = $date->format('Y-m-d');

        $this->assertEquals('URL?q=paris&num_of_days=1&date='.$dateStr.'&fx=yes&cc=yes&includeLocation=yes&showComments=no&format=xml&key=AwesomeKey',
            $ws->request(array('paris')));
    }

    public function testRequestForecast()
    {
        $locationService = $this->getMockBuilder('PlaygroundWeather\Service\Location')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataYield();
        $ws->setLocationService($locationService);

        $ws->getLocationService()
        ->expects($this->once())
        ->method('createQueryString')
        ->will($this->returnValue('paris'));

        $options = $this->getMockBuilder('PlaygroundWeather\Options\ModuleOptions')
        ->disableOriginalConstructor()
        ->getMock();

        $ws->setOptions($options);

        $ws->getOptions()
        ->expects($this->once())
        ->method('getForecastURL')
        ->will($this->returnValue('URL'));

        $ws->getOptions()
        ->expects($this->once())
        ->method('getUserKey')
        ->will($this->returnValue('AwesomeKey'));

        $date = new Datetime();
        $dateStr = $date->format('Y-m-d');

        $this->assertEquals('URL?q=paris&num_of_days=3&date='.$dateStr.'&fx=yes&cc=yes&includeLocation=no&showComments=no&format=xml&key=AwesomeKey',
            $ws->request(array('paris'), $date, 3, 3, false, true, true, false));
    }

    public function testIsPastDatePast()
    {
        $ws = new \PlaygroundWeather\Service\DataYield();

        $date = new Datetime('2013-12-25');
        $this->assertTrue($ws->isPastDate($date));
    }

    public function testIsPastDateTodayfalse()
    {
        $ws = new \PlaygroundWeather\Service\DataYield();
        $date = new Datetime();
        $this->assertFalse($ws->isPastDate($date));
    }

    public function testIsPastDate()
    {
        $ws = new \PlaygroundWeather\Service\DataYield();
        $date = new Datetime();

        // Tomorrow is not past
        $interval = new \DateInterval('P1D');
        $this->assertFalse($ws->isPastDate($date->add($interval)));

        // Yesterday is past
        $interval = new \DateInterval('P2D');
        $this->assertTrue($ws->isPastDate($date->sub($interval)));
    }

    public function testCreateDaily()
    {
        $dailyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\DailyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataYield();
        $ws->setDailyOccurrenceMapper($dailyMapper);

        $ws->getDailyOccurrenceMapper()
        ->expects($this->once())
        ->method('insert')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(true));

        $this->assertTrue($ws->createDaily(array()));
    }

    public function testCreateDailyNotInsert()
    {
        $dailyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\DailyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataYield();
        $ws->setDailyOccurrenceMapper($dailyMapper);

        $ws->getDailyOccurrenceMapper()
        ->expects($this->once())
        ->method('insert')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(false));

        $this->assertFalse($ws->createDaily(array()));
    }

    public function testCreateHourly()
    {
        $hourlyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\HourlyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataYield();
        $ws->setHourlyOccurrenceMapper($hourlyMapper);
        $ws->setCodeMapper($codeMapper);

        $ws->getHourlyOccurrenceMapper()
        ->expects($this->once())
        ->method('insert')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\HourlyOccurrence'))
        ->will($this->returnValue(true));

        $code = new Code();

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('findDefaultByCode')
        ->with($this->isType('integer'))
        ->will($this->returnValue($code));

        $daily = new DailyOccurrence();
        $date = new \Datetime();
        $daily->setDate($date);
        $data = array('code_value' => 1, 'time' => 100, 'dailyOccurrence' =>$daily);

        $this->assertTrue($ws->createHourly($data));
    }

    public function testFindDailyCode()
    {
        $hourlyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\HourlyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataYield();
        $ws->setHourlyOccurrenceMapper($hourlyMapper);
        $ws->setCodeMapper($codeMapper);

        $ws->getHourlyOccurrenceMapper()
        ->expects($this->once())
        ->method('findEveryCodeByDaily')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(array(array('id'=>1),array('id'=>2),array('id'=>1), array('id'=>4))));

        $code = new Code();
        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('findById')
        ->with($this->equalTo(1))
        ->will($this->returnValue($code));

        $daily = new DailyOccurrence();

        $this->assertInstanceOf('\PlaygroundWeather\Entity\Code', $ws->findDailyCode($daily));
    }

    public function testFindDailyCodeAllEquals()
    {
        $hourlyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\HourlyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataYield();
        $ws->setHourlyOccurrenceMapper($hourlyMapper);
        $ws->setCodeMapper($codeMapper);

        $ws->getHourlyOccurrenceMapper()
        ->expects($this->once())
        ->method('findEveryCodeByDaily')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(array(array('id'=>1),array('id'=>2),array('id'=>1), array('id'=>4))));

        $code = new Code();
        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('findById')
        ->with($this->isType('integer'))
        ->will($this->returnValue($code));

        $daily = new DailyOccurrence();

        $this->assertInstanceOf('\PlaygroundWeather\Entity\Code', $ws->findDailyCode($daily));
    }

    public function testFindDailyCodeNoCode()
    {
        $hourlyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\HourlyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataYield();
        $ws->setHourlyOccurrenceMapper($hourlyMapper);
        $ws->setCodeMapper($codeMapper);

        $ws->getHourlyOccurrenceMapper()
        ->expects($this->once())
        ->method('findEveryCodeByDaily')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(array()));

        $code = new Code();
        $ws->getCodeMapper()
        ->expects($this->never())
        ->method('findById');

        $daily = new DailyOccurrence();

        $this->assertNull($ws->findDailyCode($daily));
    }

    public function setDailyCode()
    {
        $dailyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\DailyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataYield();
        $ws->setDailyOccurrenceMapper($dailyMapper);

        $ws->getDailyOccurrenceMapper()
        ->expects($this->once())
        ->method('update')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(true));

        $daily = new DailyOccurrence();
        $this->assertInstanceOf('\PlaygroundWeather\Entity\Code', $ws->setDailyCode($daily));
    }

    public function setDailyCodeNoUpdate()
    {
        $dailyMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\DailyOccurrence')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\DataYield();
        $ws->setDailyOccurrenceMapper($dailyMapper);

        $ws->getDailyOccurrenceMapper()
        ->expects($this->once())
        ->method('update')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\DailyOccurrence'))
        ->will($this->returnValue(false));

        $daily = new DailyOccurrence();
        $this->assertFalse($ws->setDailyCode($daily));
    }

}