<?php

namespace PlaygroundWeather\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundWeather\Options\ModuleOptions;

use PlaygroundWeather\Entity\WeatherDailyOccurrence;
use PlaygroundWeather\Entity\WeatherHourlyOccurrence;
use PlaygroundWeather\Entity\WeatherLocation;

use PlaygroundWeather\Mapper\WeatherCode as WeatherDailyCodeMapper;
use PlaygroundWeather\Mapper\WeatherDailyOccurrence as WeatherDailyOccurrenceMapper;
use PlaygroundWeather\Mapper\WeatherHourlyOccurrence as WeatherHourlyOccurrenceMapper;
use PlaygroundWeather\Service\WeatherLocation  as WeatherLocationService;
use \DateTime;

class WeatherDataYield extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var WeatherCodeMapper
     */
    protected $weatherCodeMapper;

    /**
     * @var WeatherLocationService
     */
    protected $weatherLocationService;

    /**
     * @var WeatherDailyOccurrenceMapper
     */
    protected $weatherDailyOccurrenceMapper;

    /**
     * @var WeatherHourlyOccurrenceMapper
     */
    protected $weatherHourlyOccurrenceMapper;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     *
     * @param array $locationData
     * @param DateTime $date beginning date
     * @param number $numDays number of days to query from beginning date
     * @param number $tp 3 ,6 ,12 or 24
     * @param string $includeLocation
     * @param boolean $fx forecasts
     * @param boolean $cc current condition
     * @param boolean $showComments
     * @return string url
     */
    public function request(array $locationData, DateTime $date=null, $numDays=1, $tp=3, $includeLocation=true, $fx=true, $cc=true, $showComments=false )
    {
        $location = $this->getWeatherLocationService()->createQueryString($locationData);
        if (!$location) {
            return '';
        }
        if (!in_array($tp, array(3, 6, 12, 24))) {
            $tp = 3;
        }
        if(!(int)$numDays || (int)$numDays < 1 || (int)$numDays > 16) {
            $numDays = 1;
        }
        // Set Optional Parameters Value (default)
        $includeLocation = ($includeLocation) ? 'yes' : 'no';
        $fx = ($fx) ? 'yes' : 'no';
        $cc = ($cc) ? 'yes' : 'no';
        $showComments = ($showComments) ? 'yes' : 'no';

        $dateStr = '';
        if ($date) {
            $today = new DateTime("now");
            $today->setTime(0,0);
            $diff = $today->diff($date);
            if ($diff->invert) {
                $WWOstart = new DateTime("2008-07-01");
                if ($date < $WWOstart) {
                    return '';
                }
                $endDate = '';
                if ($numDays > 1) {
                    $end = new Datetime();
                    $end->setTimestamp($date->getTimestamp()+($numDays*86400));
                    // Beginning and ending dates must have the same month and same year
                    if ($date->format('Y-m') == $end->format('Y-m')) {
                        $endDate = $end->format('Y-m-d');
                    }
                }
                return $this->requestPast($location, $date->format('Y-m-d'), $endDate, $includeLocation);
            } else {
                $dateStr = $date->format('Y-m-d');
            }
        }
        return $this->requestForecast($location, $dateStr, $numDays, $fx, $cc, $includeLocation, $showComments);
    }

    public function requestPast($location, $date, $endDate, $includeLocation) {
        return $this->getOptions()->getPastURL()
        . '?q=' . $location
        . '&date=' . $date
        . '&enddate=' . $endDate
        . '&includeLocation' . $includeLocation
        . '&format=xml'
        . '&key=' . $this->getOptions()->getUserKey();
    }

    public function requestForecast($location, $date, $numDays, $fx, $cc, $includeLocation, $showComments) {
        return $this->getOptions()->getForecastURL()
        . '?q=' . $location
        . '&num_of_days=' . $numDays
        . '&date=' . $date
        . '&fx=' . $fx
        . '&cc=' . $cc
        . '&includeLocation' . $includeLocation
        . '&showComments=' . $showComments
        . '&format=xml'
        . '&key=' . $this->getOptions()->getUserKey();
    }

    /**
     *
     * @param WeatherLocation $location
     * @param DateTime $date
     */
    public function getLocationWeather(WeatherLocation $location, DateTime $date, $numDays=1)
    {
        return $this->parseForecastsToObjects($location, $this->request($location->getQueryArray(), $date, $numDays));
    }

    public function parseForecastsToObjects(WeatherLocation $location, $xmlFileURL)
    {
        $xmlContent = simplexml_load_file($xmlFileURL, null, LIBXML_NOCDATA);
        foreach ($xmlContent->weather as $daily) {
            $dailyOcc = $this->createDaily(array(
                'date' => new Datetime((string) $daily->date),
                'location' => $location,
                'minTemperature' => (float) $daily->mintempC,
                'maxTemperature' => (float) $daily->maxtempC,
                'forecast' => true,));
            foreach ($daily->hourly as $hourly) {
                $hourlyOcc = $this->createHourly(array(
                    'time' => (string) $hourly->time,
                    'dailyOccurrence' => $dailyOcc,
                    'temperature' => (float) $hourly->tempC,
                    'weatherCode' => (int) $hourly->weatherCode,
                ));
            }
        }
        return true;
    }
    public function createDaily(array $data)
    {
        $weatherDaily = new WeatherDailyOccurrence();
        $weatherDaily->populate($data);
        if ($data['location'] instanceof \PlaygroundWeather\Entity\WeatherLocation) {
            $weatherDaily->setLocation($data['location']);
        }
        if ($data['date'] instanceof \DateTime) {
            $weatherDaily->setDate($data['date']);
        }
        $weatherDaily = $this->getWeatherDailyOccurrenceMapper()->insert($weatherDaily);
        if (!$weatherDaily) {
            return false;
        }
        return $weatherDaily;
    }

    public function createHourly(array $data)
    {
        $weatherHourly = new WeatherHourlyOccurrence();
        $weatherHourly->populate($data);
        if ($data['dailyOccurrence'] instanceof WeatherDailyOccurrence) {
            $weatherHourly->setDailyOccurrence($data['dailyOccurrence']);
        }
        if ($data['time']) {
            $date = $weatherHourly->getDailyOccurrence()->getDate();
            $time = $date->setTime((int)substr($data['time'], -4, -2), (int)substr($data['time'], 2, 4));
            $weatherHourly->setTime($time);
        }
        $weatherCode = $this->getWeatherCodeMapper()->findDefaultByCode((int) $data['weatherCode']);
        if ($weatherCode) {
            $weatherHourly->setWeatherCode($weatherCode);
        }

        $weatherHourly = $this->getWeatherDailyOccurrenceMapper()->insert($weatherHourly);
        if (!$weatherHourly) {
            return false;
        }
        return $weatherHourly;
    }

    public function getWeatherCodeMapper()
    {
        if (null === $this->weatherCodeMapper) {
            $this->weatherCodeMapper = $this->getServiceManager()->get('playgroundweather_weathercode_mapper');
        }
        return $this->weatherCodeMapper;
    }

    public function setWeatherCodeMapper(WeatherCodeMapper $weatherCodeMapper)
    {
        $this->weatherCodeMapper = $weatherCodeMapper;
        return $this;
    }

    public function getWeatherLocationService()
    {
        if (null === $this->weatherLocationService) {
            $this->weatherLocationService = $this->getServiceManager()->get('playgroundweather_weatherlocation_service');
        }
        return $this->weatherLocationService;
    }

    public function setWeatherLocationService(WeatherLocationService $weatherLocationService)
    {
        $this->weatherLocationService = $weatherLocationService;
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