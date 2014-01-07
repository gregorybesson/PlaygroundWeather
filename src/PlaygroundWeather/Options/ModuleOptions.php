<?php

namespace PlaygroundWeather\Options;

use Zend\Stdlib\AbstractOptions;
use DateTime;
use DateInterval;

class ModuleOptions extends AbstractOptions
{
    /**
     * drive path to weather media files
     */
    protected $media_path = 'public/media/weather';

    /**
     * url path to story media files
     */
    protected $media_url = 'media/weather';

    /**
     * API Premium user key
     */
    protected $userKeyPremium = '';

    /**
     * API Free user key
     */
    protected $userKeyFree = '';

    /**
     * URL on which we will query for weather forecasts
     */
    protected $forecastURL = '';

    /**
     * URL on which we will query for past days real weather data
     */

    protected $pastURL = '';

    /**
     * URL on which we will query for locations
     */
    protected $locationURL = '';

    /**
     * Template for the weather table widget
     */
    protected $tableWidgetTemplate = 'playground-weather/widget/base-template.phtml';

    /**
     * Template for the weather map widget
     */
    protected $imageWidgetTemplate = 'playground-weather/widget/base-template.phtml';

    /**
     * First day for which we want to update weather data using Cron
     * Difference in day(s) from current day
     */
    protected $cronStart = -1;

    /**
     * Last day for which we want to update weather data using Cron
     * Difference in day(s) from current day
     */
    protected $cronEnd = 3;

    /**
     * Set media path
     *
     * @param  string $media_path
     * @return \PlaygroundWeather\Options\ModuleOptions
     */
    public function setMediaPath($media_path)
    {
        $this->media_path = trim($media_path);

        return $this;
    }

    /**
     * @return string
     */
    public function getMediaPath()
    {
        return $this->media_path;
    }

    /**
     *
     * @param  string $media_url
     * @return \PlaygroundWeather\Options\ModuleOptions
     */
    public function setMediaUrl($media_url)
    {
        $this->media_url = trim($media_url);

        return $this;
    }

    /**
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->media_url;
    }

    /**
     * @return $userKeyPremium
     */
    public function getUserKeyPremium()
    {
        return $this->userKeyPremium;
    }

    /**
     * @param string $userKeyPremium
     */
    public function setUserKeyPremium($userKeyPremium)
    {
        $this->userKeyPremium = trim($userKeyPremium);
        return $this;
    }

    /**
     * @return $userKeyFree
     */
    public function getUserKeyFree()
    {
        return $this->userKeyFree;
    }

    /**
     * @param string $userKeyFree
     */
    public function setUserKeyFree($userKeyFree)
    {
        $this->userKeyFree = trim($userKeyFree);
        return $this;
    }

    /**
     * @return $forecastURL
     */
    public function getForecastURL($premium = null)
    {
        if ($premium) {
            return str_replace('free', 'premium', $this->forecastURL);
        }
        return str_replace('premium', 'free', $this->forecastURL);
    }

    /**
     * @param boolean $forecastURL
     */
    public function setForecastURL($forecastURL)
    {
        $this->forecastURL = trim($forecastURL);
        return $this;
    }

    /**
     * @return $pastURL
     */
    public function getPastURL()
    {
        return $this->pastURL;
    }

    /**
     * @param boolean $pastURL
     */
    public function setPastURL($pastURL)
    {
        $this->pastURL = trim($pastURL);
        return $this;
    }

    /**
     * @return $locationURL
     */
    public function getLocationURL($premium = null)
    {
        if ($premium) {
            return str_replace('free', 'premium', $this->locationURL);
        }
        return str_replace('premium', 'free', $this->locationURL);
    }

    /**
     * @param boolean $locationURL
     */
    public function setLocationURL($locationURL)
    {
        $this->locationURL = trim($locationURL);
        return $this;
    }

    /**
     * @return $tableWidgetTemplate
     */
    public function getTableWidgetTemplate()
    {
        return $this->tableWidgetTemplate;
    }

    /**
     * @param boolean $tableWidgetTemplate
     */
    public function setTableWidgetTemplate($tableWidgetTemplate)
    {
        $this->tableWidgetTemplate = trim($tableWidgetTemplate);
        return $this;
    }

    /**
     * @return $mapWidgetTemplate
     */
    public function getImageWidgetTemplate()
    {
        return $this->imageWidgetTemplate;
    }

    /**
     * @param boolean $imageWidgetTemplate
     */
    public function setImageWidgetTemplate($imageWidgetTemplate)
    {
        $this->imageWidgetTemplate = trim($imageWidgetTemplate);
        return $this;
    }

    public function getCronStart()
    {
        return $this->cronStart;
    }

    public function setCronStart($cronStart)
    {
        $this->cronStart = $cronStart;
        return $this;
    }

    public function getCronEnd()
    {
        return $this->cronEnd;
    }

    public function setCronEnd($cronEnd)
    {
        $this->cronEnd = $cronEnd;
        return $this;
    }

    public function getCronDays()
    {
        $days = array();
        for($i = $this->getCronStart(); $i <= $this->getCronEnd(); $i++) {
            $days[] = $i;
        }
        return $days;
    }

    public function getCronDates()
    {
        $cronDays = array();
        foreach ($this->getCronDays() as $interval) {
            $today = new Datetime('today');
            $diff = new DateInterval('P'.abs($interval).'D');
            if ($interval == 0) {
                $cronDays[$interval] = $today;
            } elseif ($interval > 0) {
                $cronDays[$interval] = $today->add($diff);
            }  elseif ($interval < 0) {
                $cronDays[$interval] = $today->sub($diff);
            }
        }
        return $cronDays;
    }

    public function getQueryRanges()
    {
        $cronDays = $this->getCronDays();
        $pastStart = null;
        $pastNb = 1;
        $forecastStart = null;
        $forecastNb = 1;

        if (current($cronDays) < 0) {
            $today = new Datetime('today');
            $diff = new DateInterval('P'.abs(current($cronDays)).'D');
            $pastStart = $today->sub($diff);
            while (next($cronDays) < 0) {
                ++$pastNb;
            }
        }
        if (current($cronDays) >= 0) {
            $today = new Datetime('today');
            $diff = new DateInterval('P'.abs(current($cronDays)).'D');
            $forecastStart = $today->add($diff);
            while (next($cronDays)) {
                ++$forecastNb;
            }
        }
        return array(
            'pastStart'=>$pastStart,
            'pastNb' => $pastNb,
            'forecastStart' => $forecastStart,
            'forecastNb' => $forecastNb
        );
    }

}