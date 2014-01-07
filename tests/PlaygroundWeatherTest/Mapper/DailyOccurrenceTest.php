<?php

namespace PlaygroundWeatherTest\Mapper;

use PlaygroundWeather\Entity\DailyOccurrence;
use PlaygroundWeather\Entity\Location;
use PlaygroundWeather\Entity\Code;
use PlaygroundWeatherTest\Bootstrap;
use \DateTime;

class DailyOccurrenceTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundweather_dailyoccurrence_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
        parent::setUp();
    }

    public function testFindOneBy()
    {
        $location = new Location();
        $location->setCity('Ville');
        $location->setLatitude(10);
        $location->setLongitude(50);
        $code = new Code();
        $code->setValue(1);
        $dailyOccurrence = new DailyOccurrence();
        $dailyOccurrence->setLocation($location);
        $forecast = true;
        $dailyOccurrence->setForecast($forecast);
        $date = new DateTime();
        $dailyOccurrence->setDate($date);
        $dailyOccurrence->setMinTemperature(5);
        $dailyOccurrence->setMaxTemperature(30);
        $dailyOccurrence->setCode($code);

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