<?php

namespace PlaygroundWeatherTest\Service;

class LocationTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        parent::setUp();
    }

    public function testCreateQueryString()
    {
        $ws = new \PlaygroundWeather\Service\Location();

        $data = array('Los Angeles', 'Etats-Unis');
        $this->assertEquals('Los+Angeles,Etats+Unis', $ws->createQueryString($data));

        $data = array('Paris', 'France');
        $this->assertEquals('Paris,France', $ws->createQueryString($data));

        $data = array();
        $this->assertEquals('', $ws->createQueryString($data));

        $data = array('Paris', 'France', 'Autredata');
        $this->assertEquals('', $ws->createQueryString($data));

        $data = array('Limoges');
        $this->assertEquals('Limoges', $ws->createQueryString($data));

        $data = array(0.12345, 0.78912);
        $this->assertEquals('0.12345,0.78912', $ws->createQueryString($data));
    }

    public function testRequest()
    {
        $options = $this->getMockBuilder('PlaygroundWeather\Options\ModuleOptions')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Location();
        $ws->setOptions($options);

        $ws->getOptions()
        ->expects($this->once())
        ->method('getLocationURL')
        ->with($this->isFalse())
        ->will($this->returnValue('URL'));

        $ws->getOptions()
        ->expects($this->exactly(2))
        ->method('getUserKeyFree')
        ->will($this->returnValue('AwesomeKey'));

        $ws->getOptions()
        ->expects($this->once())
        ->method('getUserKeyPremium')
        ->will($this->returnValue('AwesomeKeyPremium'));

        $this->assertEquals('URL?query=paris,France&popular=no&num_of_results=2&format=xml&wct=&key=AwesomeKey',
                            $ws->request(array('paris', 'France'), 2, true, false, 'fakeCategory'));
    }

    public function testCreate()
    {
        $mapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Location')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Location();
        $ws->setLocationMapper($mapper);

        $location = new \PlaygroundWeather\Entity\Location();
        $location->setCity('Paris');
        $location->setCountry('france');
        $location->setLatitude(-1.25787);
        $location->setLongitude(52.39747);

        $ws->getLocationMapper()
        ->expects($this->once())
        ->method('assertNoOther')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Location'))
        ->will($this->returnValue(true));

        $ws->getLocationMapper()
        ->expects($this->once())
        ->method('insert')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Location'))
        ->will($this->returnValue($location));

        $data = array('city'=>'Paris', 'country'=>'france', 'latitude'=> -1.25787, 'longitude'=> 52.39747);
        $this->assertInstanceOf('\PlaygroundWeather\Entity\Location', $ws->create($data));
    }

    public function testNotCreateIfOther()
    {
        $mapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Location')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Location();
        $ws->setLocationMapper($mapper);

        $ws->getLocationMapper()
        ->expects($this->once())
        ->method('assertNoOther')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Location'))
        ->will($this->returnValue(false));

        $ws->getLocationMapper()
        ->expects($this->never())
        ->method('insert');

        $data = array('city'=>'Paris', 'country'=>'france', 'latitude'=> -1.25787, 'longitude'=> 52.39747);
        $this->assertFalse($ws->create($data));
    }

    public function testNotCreateIfNotInserted()
    {
        $mapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Location')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Location();
        $ws->setLocationMapper($mapper);

        $ws->getLocationMapper()
        ->expects($this->once())
        ->method('assertNoOther')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Location'))
        ->will($this->returnValue(true));

        $ws->getLocationMapper()
        ->expects($this->once())
        ->method('insert')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Location'))
        ->will($this->returnValue(false));

        $data = array('city'=>'Paris', 'country'=>'france', 'latitude'=> -1.25787, 'longitude'=> 52.39747);
        $this->assertFalse($ws->create($data));
    }

    public function testRemove()
    {
        $mapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Location')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Location();
        $ws->setLocationMapper($mapper);

        $location = new \PlaygroundWeather\Entity\Location();
        $location->setCity('Paris');
        $location->setCountry('france');
        $location->setLatitude(-1.25787);
        $location->setLongitude(52.39747);

        $ws->getLocationMapper()
        ->expects($this->once())
        ->method('findById')
        ->will($this->returnValue($location));

        $ws->getLocationMapper()
        ->expects($this->once())
        ->method('remove')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Location'));

        $this->assertTrue($ws->remove(1));
    }

    public function testRemoveLocationNotFound()
    {
        $mapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Location')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Location();
        $ws->setLocationMapper($mapper);

        $ws->getLocationMapper()
        ->expects($this->once())
        ->method('findById')
        ->will($this->returnValue(false));

        $ws->getLocationMapper()
        ->expects($this->never())
        ->method('remove');

        $this->assertFalse($ws->remove(1));
    }
}