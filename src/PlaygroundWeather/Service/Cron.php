<?php

namespace PlaygroundWeather\Service;

use ZfcBase\EventManager\EventProvider;
use DateTime;

class Cron
{

    public static function refreshWeatherData()
    {
        $configuration = require 'config/application.config.php';
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        $sm = new \Zend\ServiceManager\ServiceManager(new \Zend\Mvc\Service\ServiceManagerConfig($smConfig));
        $sm->setService('ApplicationConfig', $configuration);
        $sm->get('ModuleManager')->loadModules();
        $sm->get('Application')->bootstrap();

        $options = $sm->get('playgroundweather_module_options');
        $locationMapper = $sm->get('playgroundweather_location_mapper');
        $weatherDataYieldService = $sm->get('playgroundweather_datayield_service');

        $locations = $locationMapper->findAll();

        $queryRanges = $options->getQueryRanges();
        $missing = '';

        $i = 0;
        $start = new \DateTime();

        // start CRON
        if ($queryRanges['pastStart']!= null) {
            foreach($locations as $location) {
                $res = $weatherDataYieldService->getLocationWeather($location, $queryRanges['pastStart'], $queryRanges['pastNb']);
                if ($res) {
                    $i ++;
                } else {
                    $missing .= 'real data for ' . $location->getCity() . ' ' . $location->getCountry() . "\n";
                }
            }
        }
        if ($queryRanges['forecastStart']!= null) {
            foreach($locations as $location) {
                $res = $weatherDataYieldService->getLocationWeather($location, $queryRanges['forecastStart'], $queryRanges['forecastNb']);
                if ($res) {
                    $i ++;
                } else {
                    $missing .= 'forecasts for ' . $location->getCity() . ' ' . $location->getCountry() . "\n";
                }
            }
        }

        // end of CRON

        $end = new \DateTime();
        $diff = $end->getTimestamp() - $start->getTimestamp();

        $txt = 'locations : '. count($locations) . "\n"
            . 'past days : '. count($queryRanges['pastNb']) . "\n"
            . 'forecast days : '. count($queryRanges['forecastNb']) . "\n"
            . 'successful queries : '. $i . "\n"
            . 'executed at ' . $start->format('Y-m-d H:i:s') . "\n"
            . 'last : '. $diff . ' s' . "\n";
        if ($missing) {
            $txt .= "\n" . 'Query failed for the following : ' . "\n"
                . $missing;
        }
        $txt .=  '---------------------------' . "\n";

        file_put_contents('public/media/cron.txt', $txt, FILE_APPEND);
    }

}