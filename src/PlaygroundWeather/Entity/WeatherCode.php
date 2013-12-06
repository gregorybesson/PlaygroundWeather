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
 * @ORM\Table(
 *              name="weather_code",
 *              uniqueConstraints={@UniqueConstraint(name="code", columns={"code", "description"})}
 *           )
 */
class WeatherCode implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $code;

    /**
     * @ORM\Column(type="string")
     */
    protected $description = '';

    /**
     * @ORM\Column(name="icon_url", type="string")
     */
    protected $iconURL = '';

    /**
     * @ORM\Column(name="is_default", type="boolean")
     */
    protected $isDefault = 0;

    /**
     * @ORM\ManyToOne(targetEntity="WeatherCode", inversedBy="WeatherCode", cascade={"persist"})
     * @ORM\JoinColumn(name="associated_code", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $associatedCode = null;


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
     * @return $code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return $iconURL
     */
    public function getIconURL()
    {
        return $this->iconURL;
    }

    /**
     * @param string $iconURL
     */
    public function setIconURL($iconURL)
    {
        $this->iconURL= $iconURL;
        return $this;
    }

    /**
     * @return $associatedCode
     */
    public function getAssociatedCode()
    {
        return $this->associatedCode;
    }


    /**
     * @param \PlaygroundWeather\WeatherCode $associatedCode
     */
    public function setAssociatedCode($associatedCode)
    {
        $this->associatedCode = $associatedCode;
        return $this;
    }

    /**
     * @return $isDefault
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * @param string $isDefault
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
        return $this;
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        if (isset($data['code']) && $data['code'] != null) {
            $this->code = $data['code'];
        }
        if (isset($data['description']) && $data['description'] != null) {
            $this->description = $data['description'];
        }
        if (isset($data['isDefault']) && $data['isDefault'] != null) {
            $this->isDefault = $data['isDefault'];
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
            'code' => $this->getCode(),
            'description' => $this->getDescription(),
            'iconURL' => $this->getIconURL(),
        );
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
                'name' => 'code',
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
                'name' => 'associatedCode',
                'required' => false,
                'allowEmpty' => true,
            )));

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}