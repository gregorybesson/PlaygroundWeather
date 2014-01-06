<?php

namespace PlaygroundWeather\Service;

use ZfcBase\EventManager\EventProvider;

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

        $i = 0;
        $missing = '';
        $start = new \DateTime();
        foreach($locations as $location) {
            foreach($options->getCronDates() as $date) {
                $res = $weatherDataYieldService->getLocationWeather($location, $date);
                if ($res) {
                    $i ++;
                } else {
                    $missing .= $location->getCity() . ' ' . $location->getCountry() . ' on the ' . $date->format('Y-m-d') . "\n";
                }
            }
        }
        $end = new \DateTime();
        $diff = $end->getTimestamp() - $start->getTimestamp();

        $txt = 'locations : '. count($locations) . "\n"
            . 'days : '. count($options->getCronDates()) . "\n"
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