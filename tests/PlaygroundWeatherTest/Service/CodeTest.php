<?php

namespace PlaygroundWeatherTest\Service;

use PlaygroundWeather\Entity\Code as CodeEntity;

class CodeTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        parent::setUp();
    }

    public function testCreate()
    {
        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Code();
        $ws->setCodeMapper($codeMapper);

        $code = new CodeEntity();
        $code->setValue(1);
        $code->setDescription('bla');
        $code->setIsDefault(0);

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('assertNoOther')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Code'))
        ->will($this->returnValue(true));

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('insert')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Code'))
        ->will($this->returnValue($code));

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('findById')
        ->will($this->returnValue($code));

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('update')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Code'))
        ->will($this->returnValue($code));

        $data = array('value'=>1, 'description'=>'bla', 'is_default'=> 0);
        $this->assertInstanceOf('\PlaygroundWeather\Entity\Code', $ws->create($data));
    }

    public function testNotCreateDataMissing()
    {
        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Code();
        $ws->setCodeMapper($codeMapper);

        $ws->getCodeMapper()
        ->expects($this->exactly(2))
        ->method('assertNoOther')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Code'))
        ->will($this->returnValue(true));

        $ws->getCodeMapper()
        ->expects($this->exactly(2))
        ->method('insert')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Code'))
        ->will($this->returnValue(false));

        $data = array('description'=>'blaa');
        $this->assertFalse($ws->create($data));

        $data = array('value'=>10);
        $this->assertFalse($ws->create($data));
    }

    public function testNotCreateIfOther()
    {
        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Code();
        $ws->setCodeMapper($codeMapper);

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('assertNoOther')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Code'))
        ->will($this->returnValue(false));

        $ws->getCodeMapper()
        ->expects($this->never())
        ->method('insert');

        $data = array('value'=>2, 'description'=>'same code');
        $this->assertFalse($ws->create($data));
    }

    public function testEditCodeFound()
    {
        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Code();
        $ws->setCodeMapper($codeMapper);

        $code = new CodeEntity();
        $code->setValue(1);
        $code->setDescription('bla');
        $code->setIsDefault(0);

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('findById')
        ->will($this->returnValue($code));

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('update')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Code'))
        ->will($this->returnValue($code));

        $data = array('value'=>1, 'description'=>'bla', 'is_default'=> 0);
        $this->assertInstanceOf('\PlaygroundWeather\Entity\Code', $ws->edit(1, $data));
    }

    public function testEditCodeNotFound()
    {
        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Code();
        $ws->setCodeMapper($codeMapper);

        $code = new CodeEntity();
        $code->setValue(1);
        $code->setDescription('bla');
        $code->setIsDefault(0);

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('findById')
        ->will($this->returnValue(false));

        $ws->getCodeMapper()
        ->expects($this->never())
        ->method('update');

        $data = array('value'=>1, 'description'=>'bla', 'is_default'=> 0);
        $this->assertFalse($ws->edit(1, $data));
    }

    public function testRemoveNoUrl()
    {
        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Code();
        $ws->setCodeMapper($codeMapper);

        $code = new CodeEntity();
        $code->setValue(1);
        $code->setDescription('bla');
        $code->setIsDefault(0);

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('findById')
        ->will($this->returnValue($code));

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('remove')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Code'));

        $this->assertTrue($ws->remove(1));
    }

    public function testRemoveUrlButNotExisting()
    {
        $options = $this->getMockBuilder('PlaygroundWeather\Options\ModuleOptions')
        ->disableOriginalConstructor()
        ->getMock();

        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Code();
        $ws->setCodeMapper($codeMapper);
        $ws->setOptions($options);

        $code = new CodeEntity();
        $code->setValue(1);
        $code->setDescription('bla');
        $code->setIsDefault(0);
        $code->setIconUrl('fakeUrl');

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('findById')
        ->will($this->returnValue($code));

        $ws->getOptions()
        ->expects($this->once())
        ->method('getMediaPath')
        ->will($this->returnValue('/'));

        $ws->getOptions()
        ->expects($this->once())
        ->method('getMediaUrl')
        ->will($this->returnValue('/'));

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('remove')
        ->with($this->isInstanceOf('\PlaygroundWeather\Entity\Code'));

        $this->assertTrue($ws->remove(1));
    }

    public function testRemoveCodeNotFound()
    {
        $options = $this->getMockBuilder('PlaygroundWeather\Options\ModuleOptions')
        ->disableOriginalConstructor()
        ->getMock();

        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $ws = new \PlaygroundWeather\Service\Code();
        $ws->setCodeMapper($codeMapper);
        $ws->setOptions($options);

        $ws->getCodeMapper()
        ->expects($this->once())
        ->method('findById')
        ->will($this->returnValue(false));

        $ws->getOptions()
        ->expects($this->never())
        ->method('getMediaPath');

        $ws->getOptions()
        ->expects($this->never())
        ->method('getMediaUrl');

        $ws->getCodeMapper()
        ->expects($this->never())
        ->method('remove');

        $this->assertTrue($ws->remove(1));
    }


}