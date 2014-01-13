<?php

namespace PlaygroundWeather\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;

use PlaygroundWeather\Entity\DailyOccurrence;
use PlaygroundWeather\Entity\HourlyOccurrence;
use PlaygroundWeather\Entity\Location as LocationEntity;

use PlaygroundWeather\Service\DataYield;
use PlaygroundWeather\Mapper\DailyOccurrence as DailyOccurrenceMapper;
use PlaygroundWeather\Mapper\HourlyOccurrence as HourlyOccurrenceMapper;
use PlaygroundWeather\Mapper\Location  as LocationMapper;
use PlaygroundWeather\Mapper\Code  as CodeMapper;
use PlaygroundWeather\Options\ModuleOptions;
use \DateTime;
use \DateInterval;

class DataUse extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var ModuleOptions
     */
    protected $options;
    /**
     * @var CodeMapper
     */
    protected $codeMapper;

    /**
     * @var LocationMapper
     */
    protected $locationMapper;

    /**
     * @var DailyOccurrenceMapper
     */
    protected $dailyOccurrenceMapper;

    /**
     * @var HourlyOccurrenceMapper
     */
    protected $hourlyOccurrenceMapper;

    /**
     * @var DataYieldService
     */
    protected $dataYieldService;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     *
     * @param LocationEntity $location
     * @param DateTime $date
     */
    public function getLocationWeather(LocationEntity $location, DateTime $date, $numDays=1)
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
            $past = $this->getDataYieldService()->isPastDate($day);
            $daily = $this->getDailyOccurrenceMapper()->findOneBy($location, $day, !$past);
            if (!$daily) {
                // Query WWO
                $this->getDataYieldService()->getLocationWeather($location, $day);
                $daily = $this->getDailyOccurrenceMapper()->findOneBy($location, $day, !$past);
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
        $lastAssociatedCode = $this->getCodeMapper()->findLastAssociatedCode($hourly->getCode());
        return array(
            'id' => $hourly->getId(),
            'dailyOccurrence' => $hourly->getDailyOccurrence()->getId(),
            'time' => $time,
            'temperature' => $hourly->getTemperature(),
            'code' => $lastAssociatedCode->getForJson(),
        );
    }

    public function getDailyAsArray($daily)
    {
        $data = array(
            'id' => $daily->getId(),
            'date' => $daily->getDate(),
            'location' => $daily->getLocation()->getForJson(),
            'minTemperature' => $daily->getMinTemperature(),
            'maxTemperature' => $daily->getMaxTemperature(),
            'code' => null,
        );
        if ($daily->getCode()) {
            $lastAssociatedCode = $this->getCodeMapper()->findLastAssociatedCode($daily->getCode());
            $data['code'] = $lastAssociatedCode->getForJson();
        }
        return $data;
    }

    public function getCloserHourlyOccurrence(DailyOccurrence $dailyOccurrence, DateTime $time)
    {
        $hourlies = $this->getHourlyOccurrenceMapper()->findByDailyOccurrence($dailyOccurrence, array('time' => 'ASC'));
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

    public function getDailyWeatherForTimesAsArray(LocationEntity $location, Datetime $day, $numDays, array $hours)
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

    public function getDailyWeatherForLocationsAsArray($locations = array(), Datetime $day)
    {
        $resultArray = array();
        foreach ($locations as $location) {
            $daily = $this->getLocationWeather($location, $day, 1);
            if (!empty($daily)) {
                $arrayLoc = array();
                $arrayLoc['location'] = $location;
                $arrayLoc['weather'] = $this->getDailyAsArray(current($daily));
                array_push($resultArray, $arrayLoc);
            }
        }
        return $resultArray;
    }

    /**
     *
     * @param DailyOccurrence $daily
     */
    public function getDailyWeatherAsArray(DailyOccurrence $daily)
    {
        $array = $this->getDailyAsArray($daily);
        $hourlies = $this->getHourlyOccurrenceMapper()->findByDailyOccurrence($daily, array('time' => 'ASC'));
        $array[] = array();
        foreach ($hourlies as $hourly) {
            $array[][] = $this->getHourlyAsArray($hourly);
        }
        return $array;
    }

    public function getCodeMapper()
    {
        if (null === $this->codeMapper) {
            $this->codeMapper = $this->getServiceManager()->get('playgroundweather_code_mapper');
        }
        return $this->codeMapper;
    }

    public function setCodeMapper(CodeMapper $codeMapper)
    {
        $this->codeMapper = $codeMapper;
        return $this;
    }

    public function getLocationMapper()
    {
        if (null === $this->locationMapper) {
            $this->locationMapper = $this->getServiceManager()->get('playgroundweather_location_mapper');
        }
        return $this->locationMapper;
    }

    public function setLocationMapper(LocationMapper $locationMapper)
    {
        $this->locationMapper = $locationMapper;
        return $this;
    }

    public function getDailyOccurrenceMapper()
    {
        if ($this->dailyOccurrenceMapper === null) {
            $this->dailyOccurrenceMapper = $this->getServiceManager()->get('playgroundweather_dailyoccurrence_mapper');
        }
        return $this->dailyOccurrenceMapper;
    }

    public function setDailyOccurrenceMapper(DailyOccurrenceMapper $dailyOccurrenceMapper)
    {
        $this->dailyOccurrenceMapper = $dailyOccurrenceMapper;
        return $this;
    }

    public function getHourlyOccurrenceMapper()
    {
        if ($this->hourlyOccurrenceMapper === null) {
            $this->hourlyOccurrenceMapper = $this->getServiceManager()->get('playgroundweather_hourlyoccurrence_mapper');
        }
        return $this->hourlyOccurrenceMapper;
    }

    public function setHourlyOccurrenceMapper(HourlyOccurrenceMapper $hourlyOccurrenceMapper)
    {
        $this->hourlyOccurrenceMapper = $hourlyOccurrenceMapper;
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

    public function getDataYieldService()
    {
        if ($this->dataYieldService === null) {
            $this->dataYieldService = $this->getServiceManager()->get('playgroundweather_datayield_service');
        }
        return $this->dataYieldService;
    }

    public function setDataYieldService($dataYieldService)
    {
        $this->dataYieldService = $dataYieldService;

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