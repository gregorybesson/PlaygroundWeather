<?php

namespace PlaygroundWeatherTest\View\Helper;

class IconTest extends \PHPUnit_Framework_TestCase
{
    protected $traceError = true;

    public function setUp()
    {
        parent::setUp();
    }

    public function testHelperNoParameter()
    {
        $helper = new \PlaygroundWeather\View\Helper\Icon();
        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $helper->setCodeMapper($codeMapper);

        $helper->getCodeMapper()
        ->expects($this->never())
        ->method('findOneBy');

        $helper->getCodeMapper()
        ->expects($this->never())
        ->method('findDefaultByCode');

        $helper->getCodeMapper()
        ->expects($this->never())
        ->method('findLastAssociatedCode');

        $this->assertEquals('', $helper->__invoke());
    }


    public function testHelperCodeNotExisting()
    {
        $helper = new \PlaygroundWeather\View\Helper\Icon();
        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $helper->setCodeMapper($codeMapper);

         $helper->getCodeMapper()
        ->expects($this->once())
        ->method('findOneBy')
        ->will($this->returnValue(false));

        $helper->getCodeMapper()
        ->expects($this->once())
        ->method('findDefaultByCode')
        ->with($this->isType('integer'))
        ->will($this->returnValue(false));

        $helper->getCodeMapper()
        ->expects($this->never())
        ->method('findLastAssociatedCode');

        $this->assertEquals('', $helper->__invoke(11111111111));
    }

    public function testHelperCodeExisting()
    {
        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $helper = new \PlaygroundWeather\View\Helper\Icon();
        $helper->setCodeMapper($codeMapper);

        $code = new \PlaygroundWeather\Entity\Code();
        $code->setValue(3);
        $code->setIsDefault(1);
        $code->setDescription('one code');
        $code->setIconURL('imageIconUrl');

        $helper->getCodeMapper()
        ->expects($this->once())
        ->method('findOneBy')
        ->will($this->returnValue(false));

        $helper->getCodeMapper()
        ->expects($this->once())
        ->method('findDefaultByCode')
        ->will($this->returnValue($code));

        $helper->getCodeMapper()
        ->expects($this->once())
        ->method('findLastAssociatedCode')
        ->with($this->isInstanceOf('PlaygroundWeather\Entity\Code'))
        ->will($this->returnValue($code));

        $this->assertEquals('<img src="imageIconUrl" alt="Icone du code d\'état du ciel 3"/>', $helper(3));
    }

    public function testHelperCodeExisting2()
    {
        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $helper = new \PlaygroundWeather\View\Helper\Icon();
        $helper->setCodeMapper($codeMapper);

        $code = new \PlaygroundWeather\Entity\Code();
        $code->setValue(3);
        $code->setIsDefault(0);
        $code->setDescription('one code');
        $code->setIconURL('imageIconUrl');

        $helper->getCodeMapper()
        ->expects($this->once())
        ->method('findOneBy')
        ->will($this->returnValue($code));

        $helper->getCodeMapper()
        ->expects($this->never())
        ->method('findDefaultByCode');

        $helper->getCodeMapper()
        ->expects($this->once())
        ->method('findLastAssociatedCode')
        ->with($this->isInstanceOf('PlaygroundWeather\Entity\Code'))
        ->will($this->returnValue($code));

        $this->assertEquals('<img src="imageIconUrl" alt="Icone du code d\'état du ciel 3"/>', $helper(3));
    }

    public function testHelperCodeExistingNoImage()
    {
        $codeMapper = $this->getMockBuilder('PlaygroundWeather\Mapper\Code')
        ->disableOriginalConstructor()
        ->getMock();

        $helper = new \PlaygroundWeather\View\Helper\Icon();
        $helper->setCodeMapper($codeMapper);

        $code = new \PlaygroundWeather\Entity\Code();
        $code->setValue(3);
        $code->setIsDefault(0);
        $code->setDescription('one code');
        $code->setIconURL(null);

        $helper->getCodeMapper()
        ->expects($this->once())
        ->method('findOneBy')
        ->will($this->returnValue($code));

        $helper->getCodeMapper()
        ->expects($this->never())
        ->method('findDefaultByCode');

        $helper->getCodeMapper()
        ->expects($this->once())
        ->method('findLastAssociatedCode')
        ->with($this->isInstanceOf('PlaygroundWeather\Entity\Code'))
        ->will($this->returnValue($code));

        $this->assertEquals('<img src="" alt="Icone du code d\'état du ciel 3"/>', $helper(3));
    }

}
