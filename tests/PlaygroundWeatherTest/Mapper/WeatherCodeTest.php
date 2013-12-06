<?php

namespace PlaygroundWeatherTest\Mapper;

use PlaygroundWeather\Entity\WeatherDailyOccurrence;
use PlaygroundWeather\Entity\WeatherHourlyOccurrence;
use PlaygroundWeatherTest\Bootstrap;
use PlaygroundWeather\Entity\WeatherCode;

class WeatherCodeTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundweather_weathercode_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindLastAssociatedCode()
    {
        $code1 = new WeatherCode();
        $code1->setCode(1);
        $code1->setIsDefault(0);
        $this->tm->insert($code1);

        $code2 = new WeatherCode();
        $code2->setCode(2);
        $code2->setIsDefault(0);
        $code2->setAssociatedCode($code1);
        $this->tm->insert($code2);

        $code3 = new WeatherCode();
        $code3->setCode(3);
        $code3->setIsDefault(1);
        $code3->setAssociatedCode($code2);
        $this->tm->insert($code3);

        $this->assertEquals($code1, $this->tm->findLastAssociatedCode($code3));
    }

    public function testFindLastAssociatedCodeNoAssociated()
    {
        $code1 = new WeatherCode();
        $code1->setCode(1);
        $code1->setDescription('bla');
        $code1->setIsDefault(0);
        $code1->setAssociatedCode(null);
        $this->tm->insert($code1);

        $this->assertEquals($code1, $this->tm->findLastAssociatedCode($code1));
    }

    public function testFindDefaultByCode()
    {
        $code = new WeatherCode();
        $code->setCode(100);
        $code->setIsDefault(0);
        $code1->setDescription('toto');
        var_dump($code);
        $code = $this->tm->insert($code);
        var_dump($code);
        var_dump(var_dump($this->tm->findAll()));

        $this->assertEquals($code, current($this->tm->findBy(array('code' =>100))));
//         $this->assertNull($this->tm->findDefaultByCode(100));

//         $code->setIsDefault(1);
//         $this->tm->update($code);
//         var_dump($this->tm->findBy(array('code' =>100)));
//         var_dump($this->tm->findDefaultByCode(100));
//         $this->assertEquals($code, $this->tm->findDefaultByCode(100));
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