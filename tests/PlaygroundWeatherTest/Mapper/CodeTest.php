<?php

namespace PlaygroundWeatherTest\Mapper;

use PlaygroundWeather\Entity\WeatherDailyOccurrence;
use PlaygroundWeather\Entity\WeatherHourlyOccurrence;
use PlaygroundWeatherTest\Bootstrap;
use PlaygroundWeather\Entity\Code;

class CodeTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->em = $this->sm->get('doctrine.entitymanager.orm_default');
        $this->tm = $this->sm->get('playgroundweather_code_mapper');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $classes = $this->em->getMetadataFactory()->getAllMetadata();
        $tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    public function testFindDefaultByCode()
    {
        $code = new Code();
        $code->setValue(100);
        $code->setIsDefault(0);
        $code->setDescription('toto');
        $code = $this->tm->insert($code);

        $this->assertEquals($code, current($this->tm->findBy(array('code' =>100))));
        $this->assertNull($this->tm->findDefaultByCode(100));

        $code->setIsDefault(1);
        $this->tm->update($code);
        $this->assertEquals($code, $this->tm->findDefaultByCode(100));
    }

    public function testFindLastAssociatedCode()
    {
        $code1 = new Code();
        $code1->setValue(1);
        $code1->setIsDefault(0);
        $this->tm->insert($code1);

        $code2 = new Code();
        $code2->setValue(2);
        $code2->setIsDefault(0);
        $code2->setAssociatedCode($code1);
        $this->tm->insert($code2);

        $code3 = new Code();
        $code3->setValue(3);
        $code3->setIsDefault(1);
        $code3->setAssociatedCode($code2);
        $this->tm->insert($code3);

        $this->assertEquals($code1, $this->tm->findLastAssociatedCode($code3));
    }

    public function testFindLastAssociatedCodeNoAssociated()
    {
        $code1 = new Code();
        $code1->setValue(10);
        $code1->setDescription('bla');
        $code1->setIsDefault(0);
        $code1->setAssociatedCode(null);
        $this->tm->insert($code1);

        $this->assertEquals($code1, $this->tm->findLastAssociatedCode($code1));
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