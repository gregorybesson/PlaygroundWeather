<?php

namespace PlaygroundWeather\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="weather_daily_occurrence", uniqueConstraints={@UniqueConstraint(name="unique_day_forecast", columns={"date", "location_id", "forecast"})})
 */
class WeatherDailyOccurrence implements InputFilterAwareInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="date")
     */
    protected $date;

    /**
     * @ORM\ManyToOne(targetEntity="WeatherLocation", inversedBy="WeatherDailyOccurrence", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $location;

    /**
     * @ORM\Column(name="min_temperature", type="decimal")
     */
    protected $minTemperature;

    /**
     * @ORM\Column(name="max_temperature", type="decimal")
     */
    protected $maxTemperature;

    /**
     * @ORM\ManyToOne(targetEntity="WeatherCode", inversedBy="WeatherCode", cascade={"persist"})
     * @ORM\JoinColumn(name="weather_code_id", referencedColumnName="id")
     */
    protected $weatherCode;

    /**
     * @ORM\Column(type="boolean")
    */
    protected $forecast;

    /**
     * @param unknown $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param date $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return $date
     */
     public function setDate($date)
     {
        $this->date = $date;
        return $this;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    public function getWeatherCode()
    {
        return $this->weatherCode;
    }

    public function setWeatherCode($weatherCode)
    {
        $this->weatherCode = $weatherCode;
        return $this;
    }

    public function getMinTemperature()
    {
        return $this->minTemperature;
    }

    public function getMinTemperatureF()
    {
        return (1.8 * $this->minTemperature)+32;
    }

    public function setMinTemperature($minTemperature)
    {
        $this->minTemperature = $minTemperature;
        return $this;
    }

    public function getMaxTemperature()
    {
        return $this->maxTemperature;
    }

    public function getMaxTemperatureF()
    {
        return (1.8 * $this->maxTemperature)+32;
    }

    public function setMaxTemperature($maxTemperature)
    {
        $this->maxTemperature = $maxTemperature;
        return $this;
    }

    public function getForecast()
    {
        return $this->forecast;
    }

    public function setForecast($forecast)
    {
        $this->forecast = $forecast;
        return $this;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        if (isset($data['location']) && $data['location'] != null) {
            $this->location = $data['location'];
        }
        if (isset($data['minTemperature']) && $data['minTemperature'] != null) {
            $this->minTemperature = $data['minTemperature'];
        }
        if (isset($data['maxTemperature']) && $data['maxTemperature'] != null) {
            $this->maxTemperature = $data['maxTemperature'];
        }
        if (isset($data['forecast']) && $data['forecast'] != null) {
            $this->forecast = $data['forecast'];
        }
    }

    public function getAsArray()
    {
        return array(
            'id' => $this->getId(),
            'date' => $this->getDate(),
            'location' => $this->getLocation()->getForJson(),
            'minTemperature' => $this->getMinTemperature(),
            'maxTemperature' => $this->getMaxTemperature(),
//             'weatherCode' => $this->getWeatherCode()->getAsJson(),
        );
    }

    /**
     * @return the $inputFilter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new Factory();

            $inputFilter->add($factory->createInput(array('name' => 'id', 'required' => true, 'filters' => array(array('name' => 'Int'),),)));

            $inputFilter->add($factory->createInput(array(
                'name' => 'date',
                'required' => true,
                'validators' => array(
                    array('name' => 'NotEmpty',),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'location',
                'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'forecast',
                'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'minTemperature',
                'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'maxTemperature',
                'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'weatherCode',
                'required' => true,
                'validators' => array(
                    array('name' => 'Digits',),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * @param field_type $inputFilter
     */
    public function setInputFilter (InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
}