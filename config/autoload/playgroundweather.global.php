<?php
$playgroundweather = array(

    /**
     * Drive path to the directory where game media files will be stored
     */
    'media_path' => 'public' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'playgroundweather',

    /**
     * Url relative path to the directory where weather media files will be stored
     */
    'media_url' => 'media/playgroundweather',

    /**
     * Url relative path to the directory where weather maps will be saved
     */

    /**
     * Weather Provider API User key
     */
    // Free account
    // 'userKey' => '',


    // Premium account
    'userKey' => '',

    /**
     * URL on which we can query for weather forecasts
     */
    'forecastURL' => ' http://api.worldweatheronline.com/premium/v1/weather.ashx',

    /**
     * URL on which we can query for real past weather data
     */
    'pastURL' => 'http://api.worldweatheronline.com/premium/v1/past-weather.ashx',

    /**
     * URL on which we can query for locations
     */
    'locationURL' => 'http://api.worldweatheronline.com/premium/v1/search.ashx ',

    /**
     * Template for the weather table widget
     */
    'tableWidgetTemplate' => 'playground-weather/weather-table-widget/base-template.phtml',

    /**
     * Template for the weather map widget
     */
    'mapWidgetTemplate' => 'playground-weather/weather-map-widget/base-template.phtml',
);

/**
 * You do not need to edit below this line
*/
return array(
    'playgroundweather' => $playgroundweather,
);