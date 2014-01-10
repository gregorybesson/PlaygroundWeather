<?php

namespace PlaygroundWeatherTest\Form\Admin;

use PlaygroundWeatherTest\Bootstrap;
use PlaygroundWeather\Form\Admin\Filter;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    protected $sm;

    protected $translator;

    protected $form;

    protected $filterData;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->getForm();
        $this->filterData = array(
            'columns' => array(
        ));

        parent::setUp();
    }

    public function testEmpty()
    {
        $this->form->setData($this->filterData);
        $this->assertTrue($this->form->isValid());
    }

    public function testOneColumnNoFilter()
    {
        $this->filterData[] = array('columnName'=>'name', 'columnFilter'=>'');
        $this->form->setData($this->filterData);
        $this->assertTrue($this->form->isValid());
    }

    public function testSeveralColumnsFilter()
    {
        $this->filterData[] = array('columnName'=>'nameOther', 'columnFilter'=>'filter');
        $this->filterData[] = array('columnName'=>'nameOther', 'columnFilter'=>'58');
        $this->form->setData($this->filterData);
        $this->assertTrue($this->form->isValid());
    }

    public function getForm()
    {
        if (null === $this->form) {
            $this->form = $this->sm->get('playgroundweather_adminfilter_form');
        }
        return $this->form;
    }
}