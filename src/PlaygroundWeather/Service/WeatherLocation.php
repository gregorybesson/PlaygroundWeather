<?php

namespace PlaygroundWeather\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundWeather\Options\ModuleOptions;

use PlaygroundWeather\Mapper\WeatherLocation  as WeatherLocationMapper;
use PlaygroundWeather\Entity\WeatherLocation as WeatherLocationEntity;

class WeatherLocation extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var WeatherLocationMapper
     */
    protected $weatherLocationMapper;

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
    public function request(array $locationData, $numResults = 1, $timezone = false, $popular = true, $category = '')
    {
        $location = createQueryString($locationData);
        if (!$location) {
            return '';
        }
        // Set Optional Parameters Value (default)
        $timezone = ($timezone) ? 'yes' : 'no';
        $popular = ($popular) ? 'yes' : 'no';
        if (!in_array($category, array('Ski', 'Cricket', 'Footbal', 'Golf', 'Fishing'))) {
            $category = '';
        }

        return $this->getOptions()->getLocationURL()
            . '?query=' . $location
            . '&popular=' . $popular
            . '&num_of_results=' . $numResults
            . '&format=xml'
            . '&wct=' . $category
            . '&key=' . $this->getOptions()->getUserKey();
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
     * @return multitype:\PlaygroundWeather\Entity\WeatherLocation
     */
    public function parseResultToObjects($xmlFileURL)
    {
        $xmlContent = simplexml_load_file($xmlFileURL, null, LIBXML_NOCDATA);
        $locations = array();
        foreach ($xmlContent as $result) {
             $location = new WeatherLocationEntity();
             $location->populate(array(
                 'city' => (string) $result->areaName,
                 'country' => (string) $result->country,
                 'region' => (string) $result->region,
                 'latitude' => (string) $result->latitude,
                 'longitude' => (string) $result->longitude,
             ));
             $locations[] = $location;
        }
        return $locations;
    }

    public function retrieve($data = array())
    {
        if ((isset($data['city']) && !empty($data['city']))
               && (isset($data['country']) && !empty($data['country']))) {
            return $this->parseResultToObjects($this->request(array($data['city'], $data['country']), 3, false, false));
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
        $location = new WeatherLocationEntity();
        $location->populate($data);
        $location = $this->getWeatherLocationMapper()->insert($location);
        if (!$location) {
            return false;
        }
        return $location;
    }

//     public function edit($id, $data = array())
//     {
//         $weatherLocationMapper = $this->getWeatherLocationMapper();
//         $location = $weatherLocationMapper->findAll($id);
//         if (!$location) {
//             return false;
//         }
//         $location->populate($data);
//         $location = $weatherLocationMapper->update($location);
//         return true;
//     }

    public function remove($id)
    {
        $weatherLocationMapper = $this->getWeatherLocationMapper();
        $location = $weatherLocationMapper->findById($id);
        if (!$location) {
            return false;
        }
        $weatherLocationMapper->remove($location);
        return true;
    }

    public function getWeatherLocationMapper()
    {
        if ($this->weatherLocationMapper === null) {
            $this->weatherLocationMapper = $this->getServiceManager()->get('playgroundweather_weatherlocation_mapper');
        }
        return $this->weatherLocationMapper;
    }

    public function setWeatherLocationMapper(WeatherLocationMapper $weatherLocationMapper)
    {
        $this->weatherLocationMapper = $weatherLoactionMapper;
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
