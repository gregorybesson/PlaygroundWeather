<?php

namespace PlaygroundWeatherTest\Form\Admin;

use PlaygroundWeatherTest\Bootstrap;
use PlaygroundWeather\Form\Admin\ImageMap;

class ImageMapTest extends \PHPUnit_Framework_TestCase
{
    protected $sm;

    protected $translator;

    protected $form;

    protected $imageMapData;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->getForm();
        $this->imageMapData = array(
        );

        parent::setUp();
    }

    public function testEmpty()
    {
        $this->form->setData($this->imageMapData);

        $this->assertFalse($this->form->isValid());
    }

    public function getForm()
    {
        if (null === $this->form) {
            $this->form = $this->sm->get('playgroundweather_imagemap_form');
        }
        return $this->form;
    }
}