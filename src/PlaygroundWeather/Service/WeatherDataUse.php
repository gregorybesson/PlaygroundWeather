<?php

namespace PlaygroundWeather\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;

use PlaygroundWeather\Entity\WeatherDailyOccurrence;
use PlaygroundWeather\Entity\WeatherHourlyOccurrence;
use PlaygroundWeather\Entity\WeatherLocation;

use PlaygroundWeather\Service\WeatherDataYield;
use PlaygroundWeather\Mapper\WeatherDailyOccurrence as WeatherDailyOccurrenceMapper;
use PlaygroundWeather\Mapper\WeatherHourlyOccurrence as WeatherHourlyOccurrenceMapper;
use PlaygroundWeather\Mapper\WeatherLocation  as WeatherLocationMapper;
use PlaygroundWeather\Mapper\WeatherCode  as WeatherCodeMapper;
use PlaygroundWeather\Options\ModuleOptions;
use \DateTime;
use \DateInterval;

class WeatherDataUse extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var ModuleOptions
     */
    protected $options;
    /**
     * @var WeatherCodeMapper
     */
    protected $weatherCodeMapper;

    /**
     * @var WeatherLocationMapper
     */
    protected $weatherLocationMapper;

    /**
     * @var WeatherDailyOccurrenceMapper
     */
    protected $weatherDailyOccurrenceMapper;

    /**
     * @var WeatherHourlyOccurrenceMapper
     */
    protected $weatherHourlyOccurrenceMapper;

    /**
     * @var WeatherDataYieldService
     */
    protected $weatherDataYieldService;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     *
     * @param WeatherLocation $location
     * @param DateTime $date
     */
    public function getLocationWeather(WeatherLocation $location, DateTime $date, $numDays=1)
    {
        $dates = array($date);
        $interval = new DateInterval('P1D');
        for ($i=1; $i<$numDays; $i++) {
            $date = new DateTime($date->format('Y-m-d'));
            $date->add($interval);
            $dates[] = $date;
        }
        $results = array();
        foreach ($dates as $day) {
            // If the day searched is over, we query on REAL weather data and not forecasts
            $past = $this->getWeatherDataYieldService()->isPastDate($day);
            $daily = $this->getWeatherDailyOccurrenceMapper()->findOneBy($location, $day, !$past);
            if (!$daily) {
                // Query WWO
                $this->getWeatherDataYieldService()->getLocationWeather($location, $day);
                $daily = $this->getWeatherDailyOccurrenceMapper()->findOneBy($location, $day, !$past);
                if (!$daily) {
                    continue;
                }
            }
            $results[] = $daily;
        }
        return $results;
    }

    public function getHourlyAsArray($hourly)
    {
        $time = $hourly->getTime();
        $lastAssociatedCode = $this->getWeatherCodeMapper()->findLastAssociatedCode($hourly->getWeatherCode());
        return array(
            'id' => $hourly->getId(),
            'dailyOccurrence' => $hourly->getDailyOccurrence()->getId(),
            'time' => $time,
            'temperature' => $hourly->getTemperature(),
            'weatherCode' => $this->getCodeAsArray($lastAssociatedCode),
        );
    }

    public function getDailyAsArray($daily)
    {
        $lastAssociatedCode = $this->getWeatherCodeMapper()->findLastAssociatedCode($daily->getWeatherCode());
        return array(
            'id' => $daily->getId(),
            'date' => $daily->getDate(),
            'location' => $daily->getLocation()->getForJson(),
            'minTemperature' => $daily->getMinTemperature(),
            'maxTemperature' => $daily->getMaxTemperature(),
            'weatherCode' => $this->getCodeAsArray($lastAssociatedCode),
        );
    }

    public function getCodeAsArray($code)
    {
        $media_path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $media_url = $this->getOptions()->getMediaUrl() . '/';
        return array(
            'id' => $code->getId(),
            'code' => $code->getCode(),
            'description' => $code->getDescription(),
            'iconURL' => str_replace($media_url, $media_path, $code->getIconURL()),
        );
    }

    public function getCloserHourlyOccurrence(WeatherDailyOccurrence $dailyOccurrence, DateTime $time)
    {
        $hourlies = $this->getWeatherHourlyOccurrenceMapper()->findByDailyOccurrence($dailyOccurrence, array('time' => 'ASC'));
        if (!$hourlies) {
            return null;
        }

        $lower = $bigger = null;
        for ($i=0; $i<count($hourlies)-1; $i++) {
            if (current($hourlies)->getTime()<=$time && next($hourlies)->getTime()>$time) {
                $lower = prev($hourlies);
                $bigger =  next($hourlies);
            }
        }
        if (!$lower || !$bigger) {
            return end($hourlies);
        } else {
            $diff1 = $time->getTimestamp() - $lower->getTime()->getTimestamp();
            $diff2 = $bigger->getTime()->getTimestamp() - $time->getTimestamp();
            return ($diff1 <= $diff2) ? $lower : $bigger;
        }
    }

    public function getDailyWeatherForTimesAsArray(WeatherLocation $location, Datetime $day, $numDays, array $hours)
    {
        $dailies = $this->getLocationWeather($location, $day, $numDays);
        $resultArray = array();
        $resultArray['location'] = current($dailies) ? current($dailies)->getLocation() : null;
        $resultArray['days'] = array();
        foreach($dailies as $daily) {
            $dayArray = $this->getDailyAsArray($daily);
            $dayArray['times'] = array();
            foreach ($hours as $hour) {
                $dayArray['times'][]= $this->getHourlyAsArray($this->getCloserHourlyOccurrence($daily, $hour));
            }
            array_push($resultArray['days'], $dayArray);
        }
        return $resultArray;
    }

    /**
     *
     * @param WeatherDailyOccurrence $daily
     */
    public function getDailyWeatherAsArray(WeatherDailyOccurrence $daily)
    {
        $array = $this->getDailyAsArray($daily);
        $hourlies = $this->getWeatherHourlyOccurrenceMapper()->findByDailyOccurrence($daily, array('time' => 'ASC'));
        $array[] = array();
        foreach ($hourlies as $hourly) {
            $array[][] = $this->getHourlyAsArray($hourly);
        }
        return $array;
    }

    public function getWeatherCodeMapper()
    {
        if (null === $this->weatherCodeMapper) {
            $this->weatherCodeMapper = $this->getServiceManager()->get('playgroundweather_weathercode_mapper');
        }
        return $this->weatherCodeMapper;
    }

    public function getWeatherLocationMapper()
    {
        if (null === $this->weatherLocationMapper) {
            $this->weatherLocationMapper = $this->getServiceManager()->get('playgroundweather_weatherlocation_mapper');
        }
        return $this->weatherLocationMapper;
    }

    public function setWeatherLocationMapper(WeatherLocationMapper $weatherLocationMapper)
    {
        $this->weatherLocationMapper = $weatherLocationMapper;
        return $this;
    }

    public function getWeatherDailyOccurrenceMapper()
    {
        if ($this->weatherDailyOccurrenceMapper === null) {
            $this->weatherDailyOccurrenceMapper = $this->getServiceManager()->get('playgroundweather_weatherdailyoccurrence_mapper');
        }
        return $this->weatherDailyOccurrenceMapper;
    }

    public function setWeatherDailyOccurrenceMapper(WeatherDailyOccurrenceMapper $weatherDailyOccurrenceMapper)
    {
        $this->weatherDailyOccurrenceMapper = $weatherDailyOccurrenceMapper;
        return $this;
    }

    public function getWeatherHourlyOccurrenceMapper()
    {
        if ($this->weatherHourlyOccurrenceMapper === null) {
            $this->weatherHourlyOccurrenceMapper = $this->getServiceManager()->get('playgroundweather_weatherhourlyoccurrence_mapper');
        }
        return $this->weatherHourlyOccurrenceMapper;
    }

    public function setWeatherHourlyOccurrenceMapper(WeatherHourlyOccurrenceMapper $weatherHourlyOccurrenceMapper)
    {
        $this->weatherHourlyOccurrenceMapper = $weatherHourlyOccurrenceMapper;
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

    public function getWeatherDataYieldService()
    {
        if ($this->weatherDataYieldService === null) {
            $this->weatherDataYieldService = $this->getServiceManager()->get('playgroundweather_weatherdatayield_service');
        }
        return $this->weatherDataYieldService;
    }

    public function setWeatherDataYieldService($weatherDataYieldService)
    {
        $this->weatherDataYieldService = $weatherDataYieldService;

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