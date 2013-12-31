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
                    $viewHelper = new View\Helper\TableWidget();
                    $viewHelper->setWidgetTemplate($locator->get('playgroundweather_module_options')->getTableWidgetTemplate());

                    return $viewHelper;
                },
                'weatherImageWidget' => function($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\ImageWidget();
                    $viewHelper->setWidgetTemplate($locator->get('playgroundweather_module_options')->getImageWidgetTemplate());
                    return $viewHelper;
                },
                'temperature' => function($sm) {
                    $viewHelper = new View\Helper\Temperature();
                    return $viewHelper;
                }
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
            ),

            'invokables' => array(
                'playgroundweather_location_service' => 'PlaygroundWeather\Service\Location',
                'playgroundweather_occurrence_service' => 'PlaygroundWeather\Service\Occurrence',
                'playgroundweather_datayield_service' => 'PlaygroundWeather\Service\DataYield',
                'playgroundweather_datause_service' => 'PlaygroundWeather\Service\DataUse',
                'playgroundweather_code_service' => 'PlaygroundWeather\Service\Code',
                'playgroundweather_imagemap_service' => 'PlaygroundWeather\Service\ImageMap',
            ),

            'factories' => array(
                'playgroundweather_module_options' => function ($sm) {
                    $config = $sm->get('Configuration');

                    return new Options\ModuleOptions(isset($config['playgroundweather']) ? $config['playgroundweather'] : array());
                },

                'playgroundweather_code_mapper' => function ($sm) {
                    $mapper = new Mapper\Code(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundweather_location_mapper' => function ($sm) {
                    $mapper = new Mapper\Location(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundweather_dailyoccurrence_mapper' => function ($sm) {
                    $mapper = new Mapper\DailyOccurrence(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundweather_hourlyoccurrence_mapper' => function ($sm) {
                    $mapper = new Mapper\HourlyOccurrence(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundweather_imagemap_mapper' => function ($sm) {
                    $mapper = new Mapper\ImageMap(
                        $sm->get('doctrine.entitymanager.orm_default')
                    );
                    return $mapper;
                },
                'playgroundweather_code_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\Code(null, $sm, $translator);
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
                'playgroundweather_location_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\Location(null, $sm, $translator);
                    $location = new Entity\Location();
                    $form->setInputFilter($location->getInputFilter());
                    return $form;
                },
                'playgroundweather_imagemap_form' => function ($sm) {
                    $translator = $sm->get('translator');
                    $form = new Form\Admin\ImageMap(null, $sm, $translator);
                    $imageMap = new Entity\ImageMap();
                    $form->setInputFilter($imageMap->getInputFilter());
                    return $form;
                },
            ),
        );
    }
}