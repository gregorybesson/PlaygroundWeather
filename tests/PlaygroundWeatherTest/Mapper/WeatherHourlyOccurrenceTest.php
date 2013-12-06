<?php

namespace PlaygroundWeatherTest\Mapper;

use PlaygroundWeather\Entity\WeatherDailyOccurrence;
use PlaygroundWeather\Entity\WeatherHourlyOccurrence;
use PlaygroundWeather\Entity\WeatherLocation;
use PlaygroundWeather\Entity\WeatherCode;
use PlaygroundWeatherTest\Bootstrap;
use DateTime;
use DateInterval;

class WeatherHourlyOccurrenceTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundweather_weatherhourlyoccurrence_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFind1ByDailyOccurrence()
    {
        $dailyOccurrence = new WeatherDailyOccurrence();
        $dailyOccurrence->setId(1);
        $location = new WeatherLocation();
        $location->setCity('Limoges');
        $location->setLatitude(0);
        $location->setLongitude(0);
        $dailyOccurrence->setLocation($location);
        $dailyOccurrence->setForecast(true);
        $dailyOccurrence->setDate(new DateTime());
        $dailyOccurrence->setMinTemperature(5);
        $dailyOccurrence->setMaxTemperature(30);

        $hourlyOccurrence = new WeatherHourlyOccurrence();
        $hourlyOccurrence->setTime(new DateTime(date('H:i:s')));
        $hourlyOccurrence->setTemperature(30);
        $hourlyOccurrence->setDailyOccurrence($dailyOccurrence);
        $this->tm->insert($hourlyOccurrence);

        $this->assertEquals($hourlyOccurrence, current($this->tm->findByDailyOccurrence($dailyOccurrence)));
    }

    public function testFindManyByDailyOccurrence()
    {
        $dailyOccurrence = new WeatherDailyOccurrence();
        $dailyOccurrence->setId(2);
        $location = new WeatherLocation();
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
        $hourlyOccurrence = new WeatherHourlyOccurrence();
        $hourlyOccurrence->setId(1);
        $hourlyOccurrence->setTime($time);
        $hourlyOccurrence->setTemperature(30);
        $hourlyOccurrence->setDailyOccurrence($dailyOccurrence);
        $this->tm->insert($hourlyOccurrence);

        $hourlyOccurrence2 = new WeatherHourlyOccurrence();
        $hourlyOccurrence2->setId(2);
        $hourlyOccurrence2->setTime($time->add($interval));
        $hourlyOccurrence2->setTemperature(35);
        $hourlyOccurrence2->setDailyOccurrence($dailyOccurrence);
        $this->tm->insert($hourlyOccurrence2);

        $hourlyOccurrence3 = new WeatherHourlyOccurrence();
        $hourlyOccurrence3->setId(3);
        $hourlyOccurrence3->setTime($time->add($interval));
        $hourlyOccurrence3->setTemperature(30);
        $hourlyOccurrence3->setDailyOccurrence($dailyOccurrence);
        $this->tm->insert($hourlyOccurrence3);

        $this->assertEquals(3, count($this->tm->findByDailyOccurrence($dailyOccurrence)));
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