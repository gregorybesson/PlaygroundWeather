<?php

namespace PlaygroundWeather\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundWeather\Options\ModuleOptions;

use PlaygroundWeather\Entity\DailyOccurrence;
use PlaygroundWeather\Entity\HourlyOccurrence;

use PlaygroundWeather\Mapper\Code as CodeMapper;
use PlaygroundWeather\Mapper\DailyOccurrence as DailyOccurrenceMapper;
use PlaygroundWeather\Mapper\HourlyOccurrence as HourlyOccurrenceMapper;
use PlaygroundWeather\Service\Location  as LocationService;
use \DateTime;

class DataYield extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var CodeMapper
     */
    protected $codeMapper;

    /**
     * @var LocationService
     */
    protected $locationService;

    /**
     * @var DailyOccurrenceMapper
     */
    protected $dailyOccurrenceMapper;

    /**
     * @var HourlyOccurrenceMapper
     */
    protected $hourlyOccurrenceMapper;

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
        $location = $this->getLocationService()->createQueryString($locationData);
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
        $today = new DateTime("today");
        $diff_days = 0;
        if ($date) {
            $diff = $today->diff($date);
            $diff_days = $diff->days;
            if ($diff->invert) {
                $WWOstart = new DateTime("2008-07-01");
                if ($date < $WWOstart) {
                    return '';
                }
                $endDate = '';
                if ($numDays > 1) {
                    $end = new Datetime();
                    $end->setTimestamp($date->getTimestamp()+(($numDays-1)*86400));
                    // Beginning and ending dates must have the same month and same year
                    if ($date->format('Y-m') == $end->format('Y-m')) {
                        $endDate = $end->format('Y-m-d');
                    }
                }
                return $this->requestPast($location, $date->format('Y-m-d'), $endDate, $includeLocation);
            } else {
                if ($diff_days == 1) {
                    $dateStr =  'tomorrow';
                } elseif ($diff_days > 1) {
                    $dateStr = $date->format('Y-m-d');
                }
            }
        }
        $premium = ($diff_days + $numDays <= 5) ? false : true;
        return $this->requestForecast($location, $dateStr, $numDays, $fx, $cc, $includeLocation, $showComments, $premium);
    }

    public function requestPast($location, $date, $endDate, $includeLocation) {
        if (!$this->getOptions()->getUserKeyPremium()) {
            return false;
        }
        return $this->getOptions()->getPastURL()
        . '?q=' . $location
        . '&date=' . $date
        . '&enddate=' . $endDate
        . '&includeLocation=' . $includeLocation
        . '&format=xml'
        . '&key=' . $this->getOptions()->getUserKeyPremium();
    }

    public function requestForecast($location, $date, $numDays, $fx, $cc, $includeLocation, $showComments, $premium) {
        if ($premium && !$this->getOptions()->getUserKeyPremium()) {
            return false;
        }
        $key = ($premium) ? $this->getOptions()->getUserKeyPremium() : $this->getOptions()->getUserKeyFree();
        return $this->getOptions()->getForecastURL($premium)
        . '?q=' . $location
        . '&num_of_days=' . $numDays
        . '&date=' . $date
        . '&fx=' . $fx
        . '&cc=' . $cc
        . '&includeLocation=' . $includeLocation
        . '&showComments=' . $showComments
        . '&format=xml'
        . '&key=' . $key;
    }

    /**
     *
     * @param \PlaygroundWeather\Entity\Location $location
     * @param DateTime $date
     */
    public function getLocationWeather(\PlaygroundWeather\Entity\Location $location, DateTime $date, $numDays=1)
    {
        return $this->parseForecastsToObjects($location, $this->request($location->getQueryArray(), $date, $numDays));
    }

    public function parseForecastsToObjects(\PlaygroundWeather\Entity\Location $location, $xmlFileURL)
    {
        $url = curl_init($xmlFileURL);
        curl_setopt($url, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($url, CURLOPT_CONNECTTIMEOUT, 5);
        $data = curl_exec($url);
        $http_code = curl_getinfo($url, CURLINFO_HTTP_CODE);
        while ($http_code != 200) {
            sleep(1);
            $data = curl_exec($url);
            $http_code = curl_getinfo($url, CURLINFO_HTTP_CODE);
        }
        curl_close($url);
        try {
            $xmlContent = simplexml_load_string($data, null, LIBXML_NOCDATA);
        } catch (\Exception $e) {
            return false;
        }
        if (!$xmlContent->weather ) {
            return false;
        }
        foreach ($xmlContent->weather as $daily) {
            $date = new Datetime((string) $daily->date);
            $past = $this->isPastDate($date);
            $dailyOcc = $this->createDaily(array(
                'date' => $date,
                'location' => $location,
                'minTemperature' => ($daily->mintempC) ? (int) $daily->mintempC : (int) $daily->tempMinC,
                'maxTemperature' => ($daily->maxtempC) ? (int) $daily->maxtempC : (int) $daily->tempMaxC,
                'forecast' => ($past) ? 0 : 1,
                'code_value' => ($daily->weatherCode) ? (int) $daily->weatherCode : null,
            ));
            if ($dailyOcc && $daily->hourly) {
                foreach ($daily->hourly as $hourly) {
                    $hourlyOcc = $this->createHourly(array(
                        'time' => (string) $hourly->time,
                        'dailyOccurrence' => $dailyOcc,
                        'temperature' => (int) $hourly->tempC,
                        'code_value' => (int) $hourly->weatherCode,
                    ));
                }
                $this->setDailyCode($dailyOcc);
            }
        }
        return true;
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

    public function createDaily(array $data)
    {
        $past = $this->isPastDate($data['date']);
        $daily = $this->getDailyOccurrenceMapper()->findOneBy($data['location'], $data['date'], !$past);
        if (!$daily) {
            $daily = new DailyOccurrence();
            if ($data['location'] instanceof \PlaygroundWeather\Entity\Location) {
                $daily->setLocation($data['location']);
            }
            if ($data['date'] instanceof \DateTime) {
                $daily->setDate($data['date']);
            }
        }
        $daily->populate($data);
        if (array_key_exists('code_value', $data)) {
            $code = $this->getCodeMapper()->findDefaultByCode((int) $data['code_value']);
            if ($code) {
                $daily->setCode($code);
            }
        }
        $daily = $this->getDailyOccurrenceMapper()->insert($daily);
        return $daily;
    }

    public function createHourly(array $data)
    {
        if (array_key_exists('dailyOccurrence',$data) && $data['dailyOccurrence'] instanceof \PlaygroundWeather\Entity\DailyOccurrence) {
            $date = $data['dailyOccurrence']->getDate();
            $time = $date->setTime((int)substr($data['time'], -4, -2), (int)substr($data['time'], 2, 4));
        }
        $hourly = $this->getHourlyOccurrenceMapper()->findOneBy($data['dailyOccurrence'], $time);
        if (!$hourly) {
            $hourly = new HourlyOccurrence();
            $hourly->setDailyOccurrence($data['dailyOccurrence']);
            $hourly->setTime($time);
        }
        $hourly->populate($data);
        if (array_key_exists('code_value', $data)) {
            $code = $this->getCodeMapper()->findDefaultByCode((int) $data['code_value']);
            if ($code) {
                $hourly->setCode($code);
            }
        }
        $hourly = $this->getHourlyOccurrenceMapper()->insert($hourly);
        if (!$hourly) {
            return false;
        }
        return $hourly;
    }

    public function findDailyCode($dailyOccurrence)
    {
        $codes = $this->getHourlyOccurrenceMapper()->findEveryCodeByDaily($dailyOccurrence);
        if (!$codes) {
            return null;
        }
        $ids = array();
        foreach ($codes as $code) {
            $ids[] = current($code);
        }
        $counts = array_count_values($ids);
        asort($counts);
        $ids = array_keys($counts);
        $code = $this->getCodeMapper()->findById(end($ids));
        return $code;
    }

    public function setDailyCode($dailyOccurrence)
    {
        $dailyOccurrence->setCode($this->findDailyCode($dailyOccurrence));
        $dailyOccurrence = $this->getDailyOccurrenceMapper()->update($dailyOccurrence);
        return $dailyOccurrence;
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

    public function getLocationService()
    {
        if (null === $this->locationService) {
            $this->locationService = $this->getServiceManager()->get('playgroundweather_location_service');
        }
        return $this->locationService;
    }

    public function setLocationService(LocationService $locationService)
    {
        $this->locationService = $locationService;
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