<?php

namespace PlaygroundWeather\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\ManyToMany(targetEntity="Location", fetch="EXTRA_LAZY", cascade={"persist","remove"})
     */
    protected $locations = '';

    /**
     * @ORM\Column(name="image_url", type="string")
     */
    protected $imageURL = '';

    /**
     * @ORM\Column(name="image_width", type="integer")
     */
    protected $imageWidth=0;

    /**
     * @ORM\Column(name="image_height", type="integer")
     */
    protected $imageHeight=0;

    /**
     * @ORM\Column(name="latitude1", type="decimal",  precision=8, scale=5)
     */
    protected $latitude1;

    /**
     * @ORM\Column(name="longitude1", type="decimal",  precision=8, scale=5)
     */
    protected $longitude1;

    /**
     * @ORM\Column(name="latitude2", type="decimal",  precision=8, scale=5)
     */
    protected $latitude2;

    /**
     * @ORM\Column(name="longitude2", type="decimal", precision=8, scale=5)
     */
    protected $longitude2;

    public function __construct()
    {
        $this->locations = new ArrayCollection();
    }


    /**
     * @return the unknown_type
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * frm collection solution
     * @param unknown_type $locations
     */
    public function setLocations(ArrayCollection $locations)
    {
        $this->locations = $locations;

        return $this;
    }

    public function addLocations(ArrayCollection $locations)
    {
        foreach ($locations as $location) {
            $this->locations->add($location);
        }
    }

    public function removeLocations(ArrayCollection $locations)
    {
        foreach ($locations as $location) {
            $this->locations->removeElement($location);
        }
    }

    public function addLocation($location)
    {
        $this->locations[] = $location;
    }

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

    public function getLatitude1()
    {
        return $this->latitude1;
    }

    public function setLatitude1($latitude1)
    {
        $this->latitude1 = $latitude1;
        return $this;
    }

    public function getLatitude2()
    {
        return $this->latitude2;
    }

    public function setLatitude2($latitude2)
    {
        $this->latitude2 = $latitude2;
        return $this;
    }

    public function getLongitude1()
    {
        return $this->longitude1;
    }

    public function setLongitude1($longitude1)
    {
        $this->longitude1 = $longitude1;
        return $this;
    }

    public function getLongitude2()
    {
        return $this->longitude2;
    }

    public function setLongitude2($longitude2)
    {
        $this->longitude2 = $longitude2;
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
        if (isset($data['description']) && $data['description'] != null) {
            $this->description = $data['description'];
        }
        if (isset($data['imageWidth']) && $data['imageWidth'] != null) {
            $this->imageWidth = $data['imageWidth'];
        }
        if (isset($data['imageHeight']) && $data['imageHeight'] != null) {
            $this->imageHeight = $data['imageHeight'];
        }
        if (isset($data['latitude1']) && $data['latitude1'] != null) {
            $this->latitude1 = $data['latitude1'];
        }
        if (isset($data['latitude2']) && $data['latitude2'] != null) {
            $this->latitude2 = $data['latitude2'];
        }
        if (isset($data['longitude1']) && $data['longitude1'] != null) {
            $this->longitude1 = $data['longitude1'];
        }
        if (isset($data['longitude2']) && $data['longitude2'] != null) {
            $this->longitude2 = $data['longitude2'];
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
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => 'latitude1',
                'required' => true,
                'validators' => array(
                    array('name' => 'Float', 'options' => array('locale' => 'en')),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'longitude1',
                'required' => true,
                'validators' => array(
                    array('name' => 'Float', 'options' => array('locale' => 'en')),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name' => 'latitude2',
                'required' => true,
                'validators' => array(
                    array('name' => 'Float', 'options' => array('locale' => 'en_US')),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'longitude2',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Float',
                        'options' => array('locale' => 'en_US')
                    )
                )
            )));

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }

}