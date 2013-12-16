<?php

namespace PlaygroundWeather\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PlaygroundWeather\Service\Location as LocationService;

use Zend\Paginator\Paginator;
use PlaygroundCore\ORM\Pagination\LargeTablePaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use PlaygroundWeather\Entity\Location;

class LocationController extends AbstractActionController
{
    /**
     * @var LocationService
     */
    protected $locationService;

    public function addAction()
    {
        $locations = array();
        $form = $this->getServiceLocator()->get('playgroundweather_location_form');
        $form->get('submit')->setLabel('Ajouter');
        $location = new Location();
        $form->bind($location);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $locations = $this->getLocationService()->retrieve($location->getArrayCopy());
            } else {
                foreach ($form->getMessages() as $field => $errMsg) {
                    $this->flashMessenger()->addMessage($field . ' - ' . current($errMsg));
                }
                return $this->redirect()->toRoute('admin/weather/locations/add');
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
            return $this->redirect()->toRoute('admin/weather/locations/add');
        }
        $location = $this->getLocationService()->create($params);
        if (!$location) {
            $this->flashMessenger()->addMessage('Une erreur est survenue durant l\'ajout du lieu');
            return $this->redirect()->toRoute('admin/weather/locations/add');
        }
        return $this->redirect()->toRoute('admin/weather/locations/list');
    }

    public function removeAction()
    {
        $locationId = $this->getEvent()->getRouteMatch()->getParam('locationId');
        if (!$locationId) {
            return $this->redirect()->toRoute('admin/weather/locations/list');
        }
        $result = $this->getLocationService()->remove($locationId);
        if (!$result) {
            $this->flashMessenger()->addMessage('Une erreur est survenue pendant la suppression du lieu');
        } else {
            $this->flashMessenger()->addMessage('Le lieu a bien été supprimé');
        }
        return $this->redirect()->toRoute('admin/weather/locations/list');
    }

    public function listAction()
    {
        $adapter = new DoctrineAdapter(
                        new LargeTablePaginator(
                            $this->getLocationService()->getLocationMapper()->queryAll(array('country' => 'ASC'))
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

    public function getLocationService()
    {
        if ($this->locationService === null) {
            $this->locationService = $this->getServiceLocator()->get('playgroundweather_location_service');
        }
        return $this->locationService;
    }

    public function setLocationService($locationService)
    {
        $this->locationService = $locationService;

        return $this;
    }

}