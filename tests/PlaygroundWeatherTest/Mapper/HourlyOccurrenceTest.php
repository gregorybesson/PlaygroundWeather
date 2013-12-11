<?php

namespace PlaygroundWeatherTest\Mapper;

use PlaygroundWeather\Entity\DailyOccurrence;
use PlaygroundWeather\Entity\HourlyOccurrence;
use PlaygroundWeather\Entity\Location;
use PlaygroundWeather\Entity\Code;
use PlaygroundWeatherTest\Bootstrap;
use DateTime;
use DateInterval;

class HourlyOccurrenceTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundweather_hourlyoccurrence_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFind1ByDailyOccurrence()
    {
        $dailyOccurrence = new DailyOccurrence();
        $location = new Location();
        $location->setCity('Limoges');
        $location->setLatitude(0);
        $location->setLongitude(0);
        $dailyOccurrence->setLocation($location);
        $dailyOccurrence->setForecast(true);
        $dailyOccurrence->setDate(new DateTime());
        $dailyOccurrence->setMinTemperature(5);
        $dailyOccurrence->setMaxTemperature(30);

        $hourlyOccurrence = new HourlyOccurrence();
        $hourlyOccurrence->setTime(new DateTime(date('H:i:s')));
        $hourlyOccurrence->setTemperature(30);
        $hourlyOccurrence->setDailyOccurrence($dailyOccurrence);
        $this->tm->insert($hourlyOccurrence);

        $this->assertEquals($hourlyOccurrence, current($this->tm->findByDailyOccurrence($dailyOccurrence)));
    }

    public function testFindManyByDailyOccurrence()
    {
        $dailyOccurrence = new DailyOccurrence();
        $location = new Location();
        $location->setCity('Nantes');
        $location->setLatitude(0);
        $location->setLongitude(0);
        $dailyOccurrence->setLocation($location);
        $dailyOccurrence->setForecast(true);
        $dailyOccurrence->setDate(new DateTime());
        $dailyOccurrence->setMinTemperature(5);
        $dailyOccurrence->setMaxTemperature(30);

        $interval = new DateInterval('PT1H');
        $time = new DateTime();
        $hourlyOccurrence = new HourlyOccurrence();
        $hourlyOccurrence->setTime($time);
        $hourlyOccurrence->setTemperature(30);
        $hourlyOccurrence->setDailyOccurrence($dailyOccurrence);
        $this->tm->insert($hourlyOccurrence);

        $hourlyOccurrence2 = new HourlyOccurrence();
        $hourlyOccurrence2->setTime($time->add($interval));
        $hourlyOccurrence2->setTemperature(35);
        $hourlyOccurrence2->setDailyOccurrence($dailyOccurrence);
        $this->tm->insert($hourlyOccurrence2);

        $hourlyOccurrence3 = new HourlyOccurrence();
        $hourlyOccurrence3->setTime($time->add($interval));
        $hourlyOccurrence3->setTemperature(30);
        $hourlyOccurrence3->setDailyOccurrence($dailyOccurrence);
        $this->tm->insert($hourlyOccurrence3);

        $this->assertEquals(3, count($this->tm->findByDailyOccurrence($dailyOccurrence)));
    }

    public function testFindEveryCodeByDaily()
    {
        $dailyOccurrence = new DailyOccurrence();
        $location = new Location();
        $location->setCity('Paris');
        $location->setLatitude(5);
        $location->setLongitude(6);
        $dailyOccurrence->setLocation($location);
        $dailyOccurrence->setForecast(true);
        $dailyOccurrence->setDate(new DateTime());
        $dailyOccurrence->setMinTemperature(5);
        $dailyOccurrence->setMaxTemperature(30);

        $interval = new DateInterval('PT1H');
        $time = new DateTime();
        $code = new Code();
        $code->setValue(300);
        $code->setDescription('toto');
        $hourlyOccurrence = new HourlyOccurrence();
        $hourlyOccurrence->setCode($code);
        $hourlyOccurrence->setTime($time);
        $hourlyOccurrence->setTemperature(30);
        $hourlyOccurrence->setDailyOccurrence($dailyOccurrence);
        $this->tm->insert($hourlyOccurrence);

        $code2 = new Code();
        $code2->setValue(500);
        $code2->setDescription('titi');
        $hourlyOccurrence2 = new HourlyOccurrence();
        $hourlyOccurrence2->setCode($code2);
        $hourlyOccurrence2->setTime($time->add($interval));
        $hourlyOccurrence2->setTemperature(35);
        $hourlyOccurrence2->setDailyOccurrence($dailyOccurrence);
        $this->tm->insert($hourlyOccurrence2);

        $hourlyOccurrence3 = new HourlyOccurrence();
        $hourlyOccurrence3->setCode($code2);
        $hourlyOccurrence3->setTime($time->add($interval));
        $hourlyOccurrence3->setTemperature(30);
        $hourlyOccurrence3->setDailyOccurrence($dailyOccurrence);
        $this->tm->insert($hourlyOccurrence3);


        $this->assertCount(3, $this->tm->findByDailyOccurrence($dailyOccurrence));
        $this->assertCount(3, $this->tm->findEveryCodeByDaily($dailyOccurrence));

    }

    public function tearDown()
    {
        $dbh = $this->em->getConnection();
        unset($this->tm);
        unset($this->sm);
        unset($this->em);
        parent::tearDown();
    }
}