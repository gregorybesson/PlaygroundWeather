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
        if (!$params || !array_key_exists('city', $params)
            || !array_key_exists('country', $params)
            || !array_key_exists('latitude', $params)
            || !array_key_exists('longitude', $params) ) {
            $this->flashMessenger()->addMessage('Des informations sont manquantes, le lieu ne peut pas être ajouté');
            return $this->redirect()->toRoute('admin/weather/locations/add');
        }
        $location = $this->getLocationService()->create($params);
        if (!$location) {
            $this->flashMessenger()->addMessage('We could not create this location, verify that is does not exists already');
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
            $this->flashMessenger()->addMessage('Le lieu n\'a pas pu être supprimé');
        } else {
            $this->flashMessenger()->addMessage('Le lieu a bien été supprimé');
        }
        return $this->redirect()->toRoute('admin/weather/locations/list');
    }

    public function listAction()
    {
        $filterCriteria = $this->getEvent()->getRouteMatch()->getParam('criteria');
        $filterWay = $this->getEvent()->getRouteMatch()->getParam('filterWay');

        $sortOptions = array();
        if ($filterCriteria && $filterWay) {
            $sortOptions[$filterCriteria] = $filterWay;
        }

        $filter = array();
        $formFilter = $this->getServiceLocator()->get('playgroundweather_adminfilter_form');
        $formFilter->get('submit')->setValue('Filter');
        $formFilter->get('columns')->setCount(5);
        $data = array('columns' => array(
                array('columnName' =>'city'),
                array('columnName' =>'country'),
                array('columnName' =>'region'),
                array('columnName' =>'latitude'),
                array('columnName' =>'longitude'),
        ));
        $formFilter->setData($data);
        if($this->getRequest()->isPost()) {
            $formFilter->setData($this->getRequest()->getPost());
            if ($formFilter->isValid()) {
                $data = $formFilter->getData();
                foreach ($data['columns'] as $column) {
                    if ($column['columnFilter']) {
                        $filter[] = array($column['columnName'] => $column['columnFilter']);
                    }
                }
            }
        }
        $adapter = new DoctrineAdapter(
                        new LargeTablePaginator(
                            $this->getLocationService()->getLocationMapper()->queryCustom($filter,$sortOptions)
                        )
                    );
        $paginator = new Paginator($adapter);

        $paginator->setItemCountPerPage(25);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return new ViewModel(array(
            'formFilter' =>$formFilter,
            'locations' => $paginator,
            'flashMessages' => $this->flashMessenger()->getMessages(),
            'filterCriteria' => $filterCriteria,
            'filterWay' => $filterWay,
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