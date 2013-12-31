<?php

namespace PlaygroundWeatherTest\Form\Admin;

use PlaygroundWeatherTest\Bootstrap;
use PlaygroundWeather\Form\Admin\FileImport;

class FileImportTest extends \PHPUnit_Framework_TestCase
{
    protected $sm;

    protected $translator;

    protected $form;

    protected $fileData;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->getForm();
        $this->fileData = array(

        );

        parent::setUp();
    }

    public function testEmpty()
    {
        $this->form->setData($this->fileData);

        //need to be corrected
        $this->assertTrue($this->form->isValid());
    }

    public function getForm()
    {
        if (null === $this->form) {
            $this->form = $this->sm->get('playgroundweather_fileimport_form');
        }
        return $this->form;
    }
}