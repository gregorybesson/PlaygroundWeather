<?php

namespace PlaygroundWeatherTest\Form\Admin;

use PlaygroundWeatherTest\Bootstrap;
use PlaygroundWeather\Form\Admin\Code;

class CodeTest extends \PHPUnit_Framework_TestCase
{
    protected $sm;

    protected $translator;

    protected $form;

    protected $codesData;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->getForm();
        $this->codesData = array(
            'codes' => array(),
        );

        parent::setUp();
    }

    public function testHandle7Codes()
    {
        $this->form->get('codes')->setCount(7)->prepareElement($this->form);
        $this->form->setData($this->codesData);
        $this->form->isValid();
        $data = $this->form->getData();
        $this->assertCount(7, $data['codes']);
    }

    public function getForm()
    {
        if (null === $this->form) {
            $this->form = $this->sm->get('playgroundweather_code_form');
        }
        return $this->form;
    }
}