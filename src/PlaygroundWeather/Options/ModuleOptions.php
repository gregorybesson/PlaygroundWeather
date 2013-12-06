<?php

namespace PlaygroundWeather\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * drive path to weather media files
     */
    protected $media_path = 'public/media/adfabweather';

    /**
     * url path to story media files
     */
    protected $media_url = 'media/adfabweather';

    /**
     * API user key
     */
    protected $userKey = '';

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
    protected $mapWidgetTemplate = 'playground-weather/widget/base-template.phtml';

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
     * @return $userKey
     */
    public function getUserKey()
    {
        return $this->userKey;
    }

    /**
     * @param boolean $userKey
     */
    public function setUserKey($userKey)
    {
        $this->userKey = trim($userKey);
        return $this;
    }

    /**
     * @return $forecastURL
     */
    public function getForecastURL()
    {
        return $this->forecastURL;
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
    public function getLocationURL()
    {
        return $this->locationURL;
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
    public function getMapWidgetTemplate()
    {
        return $this->mapWidgetTemplate;
    }

    /**
     * @param boolean $mapWidgetTemplate
     */
    public function setMapWidgetTemplate($mapWidgetTemplate)
    {
        $this->mapWidgetTemplate = trim($mapWidgetTemplate);
        return $this;
    }

}