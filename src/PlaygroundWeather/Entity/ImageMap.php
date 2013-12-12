<?php

namespace PlaygroundWeather\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory;
use PlaygroundWeather\Entity\Location;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="weather_image_map")
 */
class ImageMap implements InputFilterAwareInterface
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
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $description;

    /**
     * @ORM\Column(type="string")
     */
    protected $country = '';

    /**
     * @ORM\Column(name="image_url", type="string")
     */
    protected $imageURL = '';

    /**
     * @ORM\Column(name="image_width", type="integer")
     */
    protected $imageWidth;

    /**
     * @ORM\Column(name="image_height", type="integer")
     */
    protected $imageHeight;

    /**
     * @ORM\Column(name="latitude1", type="decimal",  precision=8, scale=5)
     */
    protected $latitude1;

    /**
     * @ORM\Column(name="longitude1", type="decimal",  precision=8, scale=5)
     */
    protected $longitude1;

    /**
     * @ORM\Column(name="$ptx1", type="int")
     */
    protected $ptX1;

    /**
     * @ORM\Column(name="pty1", type="int")
     */
    protected $ptY1;

    /**
     * @ORM\Column(name="latitude2", type="decimal",  precision=8, scale=5)
     */
    protected $latitude2;

    /**
     * @ORM\Column(name="longitude2", type="decimal", precision=8, scale=5)
     */
    protected $longitude2;

    /**
     * @ORM\Column(name="$ptx2", type="int")
     */
    protected $ptX2;

    /**
     * @ORM\Column(name="pty2", type="int")
     */
    protected $ptY2;

    /**
     * @param unknown $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param unknown_type $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     *
     * @param unknown_type $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     *
     * @param unknown_type $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getImageURL()
    {
        return $this->imageURL;
    }

    /**
     *
     * @param unknown_type $imageURL
     */
    public function setImageURL($imageURL)
    {
        $this->imageURL = $imageURL;
        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getImageWidth()
    {
        return $this->imageWidth;
    }

    /**
     *
     * @param unknown_type $imageWidth
     */
    public function setImageWidth($imageWidth)
    {
        $this->imageWidth = $imageWidth;
        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getImageHeight()
    {
        return $this->imageHeight;
    }

    /**
     *
     * @param unknown_type $imageHeight
     */
    public function setImageHeight($imageHeight)
    {
        $this->imageHeight = $imageHeight;
        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getTopLeftLatitude()
    {
        return $this->topLeftLatitude;
    }

    /**
     *
     * @param unknown_type $topLeftLatitude
     */
    public function setTopLeftLatitude($topLeftLatitude)
    {
        $this->topLeftLatitude = $topLeftLatitude;
        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getTopLeftLongitude()
    {
        return $this->topLeftLongitude;
    }

    /**
     *
     * @param unknown_type $topLeftLongitude
     */
    public function setTopLeftLongitude($topLeftLongitude)
    {
        $this->topLeftLongitude = $topLeftLongitude;
        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getBottomRightLatitude()
    {
        return $this->bottomRightLatitude;
    }

    /**
     *
     * @param unknown_type $bottomRightLatitude
     */
    public function setBottomRightLatitude($bottomRightLatitude)
    {
        $this->bottomRightLatitude = $bottomRightLatitude;
        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getBottomRightLongitude()
    {
        return $this->bottomRightLongitude;
    }

    /**
     *
     * @param unknown_type $bottomRightLongitude
     */
    public function setBottomRightLongitude($bottomRightLongitude)
    {
        $this->bottomRightLongitude = $bottomRightLongitude;
        return $this;
    }


    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        if (isset($data['name']) && $data['name'] != null) {
            $this->name = $data['name'];
        }
        if (isset($data['country']) && $data['country'] != null) {
            $this->country = $data['country'];
        }
        if (isset($data['description']) && $data['description'] != null) {
            $this->description = $data['description'];
        }
        if (isset($data['imageWidth']) && $data['imageWidth'] != null) {
            $this->imageWidth = $data['imageWidth'];
        }
        if (isset($data['imageHeight']) && $data['imageHeight'] != null) {
            $this->imageHeight = $data['imageHeight'];
        }
        if (isset($data['topLeftLatitude']) && $data['topLeftLatitude'] != null) {
            $this->topLeftLatitude = $data['topLeftLatitude'];
        }
        if (isset($data['topLeftLongitude']) && $data['topLeftLongitude'] != null) {
            $this->topLeftLongitude = $data['topLeftLongitude'];
        }
        if (isset($data['bottomRightLatitude']) && $data['bottomRightLatitude'] != null) {
            $this->bottomRightLatitude = $data['bottomRightLatitude'];
        }
        if (isset($data['bottomRightLongitude']) && $data['bottomRightLongitude'] != null) {
            $this->bottomRightLongitude = $data['bottomRightLongitude'];
        }
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * @param InputFilterInterface
     */
    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used");
    }

    /**
     * @return InputFilter
     */
    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new Factory();

            $inputFilter->add($factory->createInput(array('name' => 'id', 'required' => true, 'filters' => array(array('name' => 'Int'),),)));

            $inputFilter->add($factory->createInput(array(
                'name' => 'name',
                'required' => true,
                'validators' => array(
                    array('name' => 'NotEmpty',),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'description',
                'required' => true,
                'validators' => array(
                    array('name' => 'NotEmpty',),
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
                    array(
                        'name' => 'InArray',
                        'options' => array(
                            'haystack' => Location::$countries,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => 'imageWidth',
                'required' => false,
                'validators' => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'imageHeight',
                'required' => false,
                'validators' => array(
                    array('name' => 'Int'),
                ),
            )));$inputFilter->add($factory->createInput(array(
                'name' => 'topLeftLatitude',
                'required' => true,
                'validators' => array(
                    array('name' => 'Float'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'topLeftLongitude',
                'required' => true,
                'validators' => array(
                    array('name' => 'Float'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => 'bottomRightLatitude',
                'required' => true,
                'validators' => array(
                    array('name' => 'Float'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'bottomRightLongitude',
                'required' => true,
                'validators' => array(
                    array('name' => 'Float'),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}