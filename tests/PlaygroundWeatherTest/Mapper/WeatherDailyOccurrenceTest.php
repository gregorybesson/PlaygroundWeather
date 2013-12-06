<?php

namespace PlaygroundWeatherTest\Mapper;

use PlaygroundWeather\Entity\WeatherDailyOccurrence;
use PlaygroundWeather\Entity\WeatherLocation;
use PlaygroundWeather\Entity\WeatherCode;
use PlaygroundWeatherTest\Bootstrap;
use \DateTime;

class WeatherDailyOccurrenceTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundweather_weatherdailyoccurrence_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindOneBy()
    {
        $location = new WeatherLocation();
        $location->setId(1);
        $location->setCity('Lille');
        $location->setLatitude(0);
        $location->setLongitude(0);
        $code = new WeatherCode();
        $code->setId(1);
        $code->setCode(1);
        $dailyOccurrence = new WeatherDailyOccurrence();
        $dailyOccurrence->setLocation($location);
        $forecast = true;
        $dailyOccurrence->setForecast($forecast);
        $date = new DateTime();
        $dailyOccurrence->setDate($date);
        $dailyOccurrence->setMinTemperature(5);
        $dailyOccurrence->setMaxTemperature(30);
        $dailyOccurrence->setWeatherCode($code);

        $this->tm->insert($dailyOccurrence);
        $this->assertEquals($dailyOccurrence, $this->tm->findOneBy($location, $date, $forecast));
        $this->assertNull($this->tm->findOneBy($location, $date, !$forecast));
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