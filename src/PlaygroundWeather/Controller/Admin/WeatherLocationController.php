<?php

namespace PlaygroundWeather\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PlaygroundWeather\Service\WeatherLocation as WeatherLocationService;

use Zend\Paginator\Paginator;
use PlaygroundCore\ORM\Pagination\LargeTablePaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use PlaygroundWeather\Entity\WeatherLocation;

class WeatherLocationController extends AbstractActionController
{
    /**
     * @var WeatherLocationService
     */
    protected $weatherLocationService;

    public function addAction()
    {
        $locations = array();
        $form = $this->getServiceLocator()->get('playgroundweather_weatherlocation_form');
        $form->get('submit')->setLabel('Ajouter');
        $location = new WeatherLocation();
        $form->bind($location);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $locations = $this->getWeatherLocationService()->retrieve($location->getArrayCopy());
            } else {
                foreach ($form->getMessages() as $field => $errMsg) {
                    $this->flashMessenger()->addMessage($field . ' - ' . current($errMsg));
                }
                return $this->redirect()->toRoute('admin/meteo/weather-locations/add');
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'locations' => $locations,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));
    }

    public function createAction()
    {
        $params = $this->getEvent()->getRouteMatch()->getParams();
        if (!$params || !$params['city'] || !$params['country'] || !$params['latitude'] || !$params['longitude']) {
            $this->flashMessenger()->addMessage('Des informations sont manquantes, le lieu ne peu pas être ajouté');
            return $this->redirect()->toRoute('admin/meteo/weather-locations/add');
        }
        $location = $this->getWeatherLocationService()->create($params);
        if (!$location) {
            $this->flashMessenger()->addMessage('Une erreur est survenue durant l\'ajout du lieu');
            return $this->redirect()->toRoute('admin/meteo/weather-locations/add');
        }
        return $this->redirect()->toRoute('admin/meteo/weather-locations/list');
    }


    public function removeAction()
    {
        $locationId = $this->getEvent()->getRouteMatch()->getParam('locationId');
        if (!$locationId) {
            return $this->redirect()->toRoute('admin/meteo/weather-locations/list');
        }
        $result = $this->getWeatherLocationService()->remove($locationId);
        if (!$result) {
            $this->flashMessenger()->addMessage('Une erreur est survenue pendant la suppression du lieu');
        } else {
            $this->flashMessenger()->addMessage('Le lieu a bien été supprimé');
        }
        return $this->redirect()->toRoute('admin/meteo/weather-locations/list');
    }

    public function listAction()
    {
        $adapter = new DoctrineAdapter(
                        new LargeTablePaginator(
                            $this->getWeatherLocationService()->getWeatherLocationMapper()->queryAll(array('country' => 'ASC'))
                        )
                    );
        $paginator = new Paginator($adapter);

        $paginator->setItemCountPerPage(25);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return new ViewModel(array(
            'locations' => $paginator,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));
    }

    public function getWeatherLocationService()
    {
        if ($this->weatherLocationService === null) {
            $this->weatherLocationService = $this->getServiceLocator()->get('playgroundweather_weatherlocation_service');
        }
        return $this->weatherLocationService;
    }

    public function setWeatherLocationService($weatherLocationService)
    {
        $this->weatherLocationService = $weatherLocationService;

        return $this;
    }

}