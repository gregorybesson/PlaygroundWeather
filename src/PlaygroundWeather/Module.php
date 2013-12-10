<?php

namespace PlaygroundWeather;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Validator\AbstractValidator;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();

        $options = $sm->get('playgroundcore_module_options');
        $locale = $options->getLocale();
        $translator = $sm->get('translator');
        if (!empty($locale)) {
            //translator
            $translator->setLocale($locale);

            // plugins
            $translate = $sm->get('viewhelpermanager')->get('translate');
            $translate->getTranslator()->setLocale($locale);
        }
        AbstractValidator::setDefaultTranslator($translator,'playgroundcore');

        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // Here we need to schedule the core cron service

        // If cron is called, the $e->getRequest()->getPost() produces an error so I protect it with
        // this test
        if ((get_class($e->getRequest()) == 'Zend\Console\Request')) {
            return;
        }
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/../../autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoLoader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__.'/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * @return array
     */
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'weatherTableWidget' => function($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\WeatherTableWidget();
                    $viewHelper->setWidgetTemplate($locator->get('playgroundweather_module_options')->getTableWidgetTemplate());

                    return $viewHelper;
                },
                'weatherImageWidget' => function($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\WeatherImageWidget();
                    $viewHelper->setWidgetTemplate($locator->get('playgroundweather_module_options')->getImageWidgetTemplate());
                    return $viewHelper;
                },
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
            ),

            'invokables' => array(
                'playgroundweather_weatherlocation_service' => 'PlaygroundWeather\Service\WeatherLocation',
                'playgroundweather_weatheroccurrence_service' => 'PlaygroundWeather\Service\WeatherOccurrence',
                'playgroundweather_weatherdatayield_service' => 'PlaygroundWeather\Service\WeatherDataYield',
                'playgroundweather_weatherdatause_service' => 'PlaygroundWeather\Service\WeatherDataUse',
                'playgroundweather_weathercode_service' => 'PlaygroundWeather\Service\WeatherCode',
            ),

            'factories' => array(
                'playgroundweather_module_options' => function ($sm) {
                    $config = $sm->get('Configuration');

                    return new Options\ModuleOptions(isset($config['playgroundweather']) ? $config['playgroundweather'] : array());
                },

                'playgroundweather_weathercode_mapper' => function ($sm) {
                    $mapper = new Mapper\WeatherCode(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundweather_weatherlocation_mapper' => function ($sm) {
                    $mapper = new Mapper\WeatherLocation(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundweather_weatherdailyoccurrence_mapper' => function ($sm) {
                    $mapper = new Mapper\WeatherDailyOccurrence(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundweather_weatherhourlyoccurrence_mapper' => function ($sm) {
                    $mapper = new Mapper\WeatherHourlyOccurrence(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundweather_weathercode_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\WeatherCode(null, $sm, $translator);
//                     $codeObject = new Entity\WeatherCode();
//                     $inputFilter = $codeObject->getInputFilter();

//                     $fileFilter = new \Zend\InputFilter\FileInput('icon');
//                     $validatorChain = new \Zend\Validator\ValidatorChain();
//                     $validatorChain->attach(new \Zend\Validator\File\Exists());
//                     $validatorChain->attach(new \Zend\Validator\File\Extension(array('jpg', 'jpeg', 'png')));
//                     $fileFilter->setValidatorChain($validatorChain);

//                     $inputFilter->add($fileFilter);
//                     $form->setInputFilter($inputFilter);
                    return $form;
                },
                'playgroundweather_fileimport_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\FileImport(null, $sm, $translator);
                    return $form;
                },
                'playgroundweather_weatherlocation_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\WeatherLocation(null, $sm, $translator);
                    $location = new Entity\WeatherLocation();
                    $form->setInputFilter($location->getInputFilter());
                    return $form;
                },
            ),
        );
    }
}