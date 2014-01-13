<?php

namespace PlaygroundWeather\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundWeather\Options\ModuleOptions;

use PlaygroundWeather\Mapper\Location  as LocationMapper;
use PlaygroundWeather\Entity\Location as LocationEntity;

class Location extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var LocationMapper
     */
    protected $locationMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     *
     * @param array $locationData
     * Can contain city name, city name + country name, US city name + US state name
     * ip address, latitude + longitude, UK/Canadian/US zipcode
     * @param integer $num_of_results = 1
     * @param boolean $timezone = false
     * @param boolean $popular = false
     * @param string $category = ''
     * @return string url
     */
    public function request(array $locationData, $numResults = 1, $timezone = true, $popular = false, $category = '')
    {
        $location = $this->createQueryString($locationData);
        if (!$location) {
            return '';
        }
        // Set Optional Parameters Value (default)
        $timezone = ($timezone) ? 'yes' : 'no';
        $popular = ($popular) ? 'yes' : 'no';
        if (!in_array($category, array('Ski', 'Cricket', 'Footbal', 'Golf', 'Fishing'))) {
            $category = '';
        }

        $premium = $this->getOptions()->getUserKeyFree() ? false : true;
        $key = $premium ? $this->getOptions()->getUserKeyPremium() : $this->getOptions()->getUserKeyFree();
        return $this->getOptions()->getLocationURL($premium)
            . '?query=' . $location
            . '&popular=' . $popular
            . '&timezone=' . $timezone
            . '&num_of_results=' . $numResults
            . '&format=xml'
            . '&wct=' . $category
            . '&key=' . $key
        ;
    }

    /**
     *
     * @param array $locationData
     * Can contain city name, city name + country name, US city name + US state name
     * ip address, latitude + longitude, UK/Canadian/US zipcode
     * @return string
     */
    public function createQueryString(array $locationData)
    {
        $location = '';
        if (empty($locationData) || count($locationData) > 2) {
            return $location;
        }
        foreach ($locationData as $data ) {
            $location .= str_replace(array('-', ' '), '+', (string) $data);
            if ($data != end($locationData)) {
                $location .= ',';
            }
        }
        return $location;
    }

    /**
     *
     * @param string $xmlFileURL
     * @return multitype:\PlaygroundWeather\Entity\Location
     */
    public function parseResultToObjects($xmlFileURL)
    {
        $url = curl_init($xmlFileURL);
        curl_setopt($url, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($url, CURLOPT_CONNECTTIMEOUT, 5);
        $data = curl_exec($url);
        $http_code = curl_getinfo($url, CURLINFO_HTTP_CODE);
        curl_close($url);
        if ($http_code != 200) {
            return false;
        }
        try {
            $xmlContent = simplexml_load_string($data, null, LIBXML_NOCDATA);
        } catch (\Exception $e) {
            return false;
        }

        return $this->createObjectsFromXML($xmlContent);
    }

   public function createObjectsFromXML($xmlContent)
   {
        if ($xmlContent->error) {
            return false;
        }
        $locations = array();
        foreach ($xmlContent as $result) {
             $location = new LocationEntity();
             $location->populate(array(
                 'city' => (string) $result->areaName,
                 'country' => (string) $result->country,
                 'region' => (string) $result->region,
                 'latitude' => (string) $result->latitude,
                 'longitude' => (string) $result->longitude,
                 'gtmOffset' => (string) $result->timezone->offset,
             ));
             $locations[] = $location;
        }
        return $locations;
    }

    public function retrieve($data = array())
    {
        if ((isset($data['city']) && !empty($data['city']))
               && (isset($data['country']) && !empty($data['country']))) {
            return $this->parseResultToObjects($this->request(array($data['city'], $data['country']), 3));
        } elseif (isset($data['city']) && !empty($data['city'])) {
            return $this->parseResultToObjects($this->request(array($data['city'])));
        } elseif ((isset($data['latitude']) && !empty($data['latitude']))
               && (isset($data['longitude']) && !empty($data['longitude']))) {
            return $this->parseResultToObjects($this->request(array($data['latitude'], $data['longitude'])));
        }
        return false;
    }

    public function create($data = array())
    {
        $location = new LocationEntity();
        $location->populate($data);
        if (!$this->getLocationMapper()->assertNoOther($location)) {
            return false;
        }
        $location = $this->getLocationMapper()->insert($location);
        if (!$location) {
            return false;
        }
        return $location;
    }

    public function remove($id)
    {
        $locationMapper = $this->getLocationMapper();
        $location = $locationMapper->findById($id);
        if (!$location) {
            return false;
        }
        $locationMapper->remove($location);
        return true;
    }

    public function getLocationMapper()
    {
        if ($this->locationMapper === null) {
            $this->locationMapper = $this->getServiceManager()->get('playgroundweather_location_mapper');
        }
        return $this->locationMapper;
    }

    public function setLocationMapper(LocationMapper $locationMapper)
    {
        $this->locationMapper = $locationMapper;
        return $this;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('playgroundweather_module_options'));
        }
        return $this->options;
    }
}
