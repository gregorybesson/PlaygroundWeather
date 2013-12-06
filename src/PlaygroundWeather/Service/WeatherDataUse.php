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
use \DateTime;
use \DateInterval;

class WeatherDataUse extends EventProvider implements ServiceManagerAwareInterface
{
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
            $date->add($interval);
            $dates[] = $date;
        }
        $results = array();
        foreach ($dates as $day) {
            // If the day searched is over, we query on REAL weather data and not forecasts
            $daily = $this->getWeatherDailyOccurrenceMapper()->findOneBy($location, $day, !$this->isPastDate($day));
            if (!$daily) {
                // Query WWO
                $this->getWeatherDataYieldService()->getLocationWeather($location, $day);
                $daily = $this->getWeatherDailyOccurrenceMapper()->findOneBy($location, $day, !$this->isPastDate($day));
                if (!$daily) {
                    continue;
                }
            }
            $results[] = $this->getDailyWeatherAsJson($daily);
        }
        return $results;
    }

    /**
     * Tell us if the given day is over or not
     * @param DateTime $date
     * @return boolean
     */
    public function isPastDate(DateTime $date)
    {
        $today = new DateTime();
        $today->setTime(0,0);

        $diff = $today->diff($date);
        return ($diff->invert) ? true : false ;
    }

    public function getCloserHourlyOccurrence(WeatherDailyOccurrence $dailyOccurrence, DateTime $time)
    {
        $hourlies = $this->getWeatherHourlyOccurrenceMapper()->findByDailyOccurrence($dailyOccurrence, array('time' => 'ASC'));
        if (!$hourlies) {
            return null;
        }

        $lower = $bigger = null;
        for ($i=0; $i<count($hourlies)-1; $i++) {
            if (current($hourlies)->getTime()<$time && next($hourlies)->getTime()>$time) {
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

    /**
     *
     * @param WeatherDailyOccurrence $daily
     */
    public function getDailyWeatherAsJson(WeatherDailyOccurrence $daily)
    {
        $array = $daily->getAsArray();
        $hourlies = $this->getWeatherHourlyOccurrenceMapper()->findByDailyOccurrence($daily, array('time' => 'ASC'));
        $array[] = array();
        foreach ($hourlies as $hourly) {
            $lastAssociatedCode = $this->getWeatherCodeMapper()->findLastAssociatedCode($hourly->getWeatherCode());
            $array[][] = array(
                'id' => $hourly->getId(),
                'dailyOccurrence' => $hourly->getDailyOccurrence()->getId(),
                'time' => $hourly->getTime(),
                'temperature' => $hourly->getTemperature(),
                'weatherCode' => $lastAssociatedCode->getForJson(),
            );
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
}