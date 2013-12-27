<?php

namespace PlaygroundWeatherTest\Service;
use PlaygroundWeather\Entity\ImageMap;

class ImageMapTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        parent::setUp();
    }

    public function testCreate()
    {
        $mapper = $this->getMockBuilder('PlaygroundWeather\Mapper\ImageMap')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\ImageMap();
        $ws->setImageMapMapper($mapper);

        $imageMap = new ImageMap();

        $ws->getImageMapMapper()
        ->expects($this->once())
        ->method('insert')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\ImageMap'))
        ->will($this->returnValue($imageMap));

        $ws->getImageMapMapper()
        ->expects($this->once())
        ->method('findById')
        ->will($this->returnValue($imageMap));

        $ws->getImageMapMapper()
        ->expects($this->once())
        ->method('update')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\ImageMap'))
        ->will($this->returnValue($imageMap));

        $data = array();
        $this->assertInstanceOf('\PlaygroundWeather\Entity\ImageMap', $ws->create($data));
    }

    public function testNotCreate()
    {
        $mapper = $this->getMockBuilder('PlaygroundWeather\Mapper\ImageMap')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\ImageMap();
        $ws->setImageMapMapper($mapper);

        $imageMap = new ImageMap();

        $ws->getImageMapMapper()
        ->expects($this->once())
        ->method('insert')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\ImageMap'))
        ->will($this->returnValue(false));

        $ws->getImageMapMapper()
        ->expects($this->never())
        ->method('findById');

        $ws->getImageMapMapper()
        ->expects($this->never())
        ->method('update');

        $data = array();
        $this->assertFalse($ws->create($data));
    }

    public function testEdit()
    {
        $mapper = $this->getMockBuilder('PlaygroundWeather\Mapper\ImageMap')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\ImageMap();
        $ws->setImageMapMapper($mapper);

        $imageMap = new ImageMap();

        $ws->getImageMapMapper()
        ->expects($this->once())
        ->method('findById')
        ->will($this->returnValue($imageMap));

        $ws->getImageMapMapper()
        ->expects($this->once())
        ->method('update')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\ImageMap'))
        ->will($this->returnValue($imageMap));

        $data = array();
        $this->assertInstanceOf('\PlaygroundWeather\Entity\ImageMap', $ws->edit(1, $data));
    }

    public function testEditImageMapNotFound()
    {
        $mapper = $this->getMockBuilder('PlaygroundWeather\Mapper\ImageMap')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\ImageMap();
        $ws->setImageMapMapper($mapper);

        $imageMap = new ImageMap();

        $ws->getImageMapMapper()
        ->expects($this->once())
        ->method('findById')
        ->will($this->returnValue(false));

        $ws->getImageMapMapper()
        ->expects($this->never())
        ->method('update');

        $data = array();
        $this->assertFalse($ws->edit(1, $data));
    }

    public function testRemove()
    {
        $options = $this->getMockBuilder('PlaygroundWeather\Options\ModuleOptions')
        ->disableOriginalConstructor()
        ->getMock();

        $mapper = $this->getMockBuilder('PlaygroundWeather\Mapper\ImageMap')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\ImageMap();
        $ws->setImageMapMapper($mapper);
        $ws->setOptions($options);

        $imageMap = new ImageMap();
        $imageMap->setImageURL('fakeImageUrl');

        $ws->getImageMapMapper()
        ->expects($this->once())
        ->method('findById')
        ->will($this->returnValue($imageMap));

        $ws->getOptions()
        ->expects($this->once())
        ->method('getMediaPath')
        ->will($this->returnValue('/'));

        $ws->getOptions()
        ->expects($this->once())
        ->method('getMediaUrl')
        ->will($this->returnValue('/'));

        $ws->getImageMapMapper()
        ->expects($this->once())
        ->method('remove')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\ImageMap'));

        $this->assertTrue($ws->remove(1));
    }

    public function testRemoveImageMapNotFound()
    {
         $options = $this->getMockBuilder('PlaygroundWeather\Options\ModuleOptions')
        ->disableOriginalConstructor()
        ->getMock();

        $mapper = $this->getMockBuilder('PlaygroundWeather\Mapper\ImageMap')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\ImageMap();
        $ws->setImageMapMapper($mapper);
        $ws->setOptions($options);

        $ws->getImageMapMapper()
        ->expects($this->once())
        ->method('findById')
        ->will($this->returnValue(false));

        $ws->getOptions()
        ->expects($this->never())
        ->method('getMediaPath');

        $ws->getOptions()
        ->expects($this->never())
        ->method('getMediaUrl');

        $ws->getImageMapMapper()
        ->expects($this->never())
        ->method('remove');

        $this->assertFalse($ws->remove(1));
    }
}