<?php

namespace PlaygroundWeatherTest\Form\Admin;

use PlaygroundWeatherTest\Bootstrap;
use PlaygroundWeather\Form\Admin\Location;

class LocationTest extends \PHPUnit_Framework_TestCase
{
    protected $sm;

    protected $translator;

    protected $form;

    protected $locationData;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->getForm();
        $this->locationData = array(
            'id'=>'0',
            'city'=>'Limoges',
            'country'=>'france',
            'latitude'=>1.23456,
            'longitude' =>7.89123
        );

        parent::setUp();
    }

    public function testAllGiven()
    {
        $this->form->setData($this->locationData);
        $this->assertTrue($this->form->isValid());
        $data = $this->form->getData();
    }

    public function testAllGivenNoId()
    {
        $this->locationData = array(
            'city'=>'Limoges',
            'country'=>'france',
            'latitude'=>1.23456,
            'longitude' =>7.89123
        );
        $this->form->setData($this->locationData);
        $this->assertFalse($this->form->isValid());
        $data = $this->form->getData();
    }

    public function testOnlyCoordinates()
    {
        $this->locationData = array(
            'id'=>'0',
            'latitude'=>1.23456,
            'longitude' =>7.89123
        );
        $this->form->setData($this->locationData);
        $this->assertTrue($this->form->isValid());
        $data = $this->form->getData();
        $this->assertEquals('1.23456', $data['latitude']);
        $this->assertEquals('7.89123', $data['longitude']);
    }

    public function testCoordinatesFloat()
    {
        $this->locationData = array(
            'id'=>'0',
            'latitude'=>'1.23456',
            'longitude' =>'7.89123'
        );
        $this->form->setData($this->locationData);
        $this->assertTrue($this->form->isValid());
        $data = $this->form->getData();
        $this->assertEquals('1.23456', $data['latitude']);
        $this->assertEquals('7.89123', $data['longitude']);
    }

    public function testCoordinatesNotUSFloat()
    {
        $this->locationData = array(
            'id'=>'0',
            'latitude'=>'1,23456',
            'longitude' =>'1,23456'
        );
        $this->form->setData($this->locationData);
        $this->assertFalse($this->form->isValid());
    }

    public function testCoordinatesString()
    {
        $this->locationData = array(
            'id'=>'0',
            'latitude'=>'cooor',
            'longitude' =>'coor'
        );
        $this->form->setData($this->locationData);
        $this->assertFalse($this->form->isValid());
    }

    public function testOnly1CoordinateLong()
    {
        $this->locationData = array(
            'id'=>'0',
            'longitude' =>7.89123
        );
        $this->form->setData($this->locationData);
        $this->assertTrue($this->form->isValid());
    }

    public function testOnly1CoordinateLat()
    {
        $this->locationData = array(
            'id'=>'0',
            'latitude' =>7.89123
        );
        $this->form->setData($this->locationData);
        $this->assertTrue($this->form->isValid());
    }

    public function testOnlyCityCountry()
    {
        $this->locationData = array(
            'id'=>'0',
            'city'=>'<tag> Limoges</tag>',
            'country'=>'<tag>FrANce</tag> ',
        );
        $this->form->setData($this->locationData);
        $this->assertTrue($this->form->isValid());
        $data = $this->form->getData();
        $this->assertEquals('limoges', $data['city']);
        $this->assertEquals('france', $data['country']);
    }

    public function testOnlyCityCountryTooLong()
    {
        $this->locationData = array(
            'id'=>'0',
            'city'=>'Limoges',
            'country'=>'oiuytrefghjytrsbcvdmpoihetgafdoiuytrefghjytrsbcvdmpoihetgafdoiuytrefghjytrsbcvdmpoihetgafdoiuytrefghjytrsbcvdmpoihetgafdoiuytrefghjytrsbcvdmpoihetgafdoiuytrefghjytrsbcvdmpoihetgafdoiuytrefghjytrsbcvdmpoihetgafdoiuytrefghjytrsbcvdmpoihetgafdoiuytrefghjytrsbcvdmpoihetgafd',
        );
        $this->form->setData($this->locationData);
        $this->assertFalse($this->form->isValid());
    }

    public function testOnlyCity()
    {
        $this->locationData = array(
            'id'=>'0',
            'city'=>'Limoges',
        );
        $this->form->setData($this->locationData);
        $this->assertTrue($this->form->isValid());
        $data = $this->form->getData();
    }

    public function getForm()
    {
        if (null === $this->form) {
            $this->form = $this->sm->get('playgroundweather_location_form');
        }
        return $this->form;
    }
}