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
 * @ORM\Table(name="weather_location",
 *   uniqueConstraints={@UniqueConstraint(name="unique_location", columns={"latitude", "longitude"}),
 *                      @UniqueConstraint(name="unique_city", columns={"city", "region", "country"})})
 */
class Location implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $country = 'france';

    /**     *
     * @ORM\Column(type="string")
     */
    protected $region = '';

    /**
     * @ORM\Column(type="string")
     */
    protected $city;

    /**
     * @ORM\Column(type="decimal",  precision=8, scale=5)
     */
    protected $latitude;

    /**
     * @ORM\Column(type="decimal",  precision=8, scale=5)
     */
    protected $longitude;

    /**
     * @ORM\Column(type="decimal")
     */
    protected $gtmOffset = 1.0;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    public function getRegion()
    {
        return $this->region;
    }

    public function setRegion($region)
    {
        $this->region = $region;
        return $this;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getGtmOffset()
    {
        return $this->gtmOffset;
    }

    public function setGtmOffset($gtmOffset)
    {
        $this->gtmOffset = $gtmOffset;
        return $this;
    }


    public function getQueryArray()
    {
        return array((string) $this->getLatitude(), (string) $this->getLongitude());
    }

    public function populate($data = array())
    {
        if (isset($data['city']) && $data['city'] != null) {
            $this->city = $data['city'];
        }
        if (isset($data['country']) && $data['country'] != null) {
            $this->country = $data['country'];
        }
        if (isset($data['region']) && $data['region'] != null) {
            $this->region = $data['region'];
        }
        if (isset($data['latitude']) && $data['latitude'] != null) {
            $this->latitude = $data['latitude'];
        }
        if (isset($data['longitude']) && $data['longitude'] != null) {
            $this->longitude = $data['longitude'];
        }
        if (isset($data['gtmOffset']) && $data['gtmOffset'] != null) {
            $this->gtmOffset = $data['gtmOffset'];
        }
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function getForJson()
    {
        return array(
            'id' => $this->getId(),
            'city' => $this->getCity(),
            'country' => $this->getCountry(),
            'region' => $this->getRegion(),
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
            'gtmOffset' => $this->getGtmOffset(),
        );
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception('not used');
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new Factory();

            $inputFilter->add($factory->createInput(array('name' => 'id', 'required' => true, 'filters' => array(array('name' => 'Int'),),)));

            $inputFilter->add($factory->createInput(array(
                'name' => 'city',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    array('name' => 'StringToLower', 'options' => array('encoding' => 'UTF-8')),
                ),
                'validators' => array(
                    array('name' => 'StringLength', 'options' => array('min'=>1, 'max' => 255)),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'country',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    array('name' => 'StringToLower', 'options' => array('encoding' => 'UTF-8')),
                ),
                'validators' => array(
                    array('name' => 'StringLength', 'options' => array('min'=>1, 'max' => 255)),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'latitude',
                'required' => false,
                'validators' => array(
                    array('name' => 'Float', 'options' => array('locale' => 'en_US')),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'longitude',
                'required' => false,
                'validators' => array(
                    array(
                        'name' => 'Float',
                        'options' => array(
                            'locale' => 'en_US'
                        )
                    )
                )
            )));

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}