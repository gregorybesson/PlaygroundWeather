<?php
namespace PlaygroundWeather\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Paginator\Paginator;
use PlaygroundCore\ORM\Pagination\LargeTablePaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;

use PlaygroundWeather\Service\ImageMap as ImageMapService;
use PlaygroundWeather\Entity\ImageMap;

class ImageMapController extends AbstractActionController
{
    /**
     * @var ImageMapService
     */
    protected $imageMapService;

    public function addAction()
    {
        $form = $this->getServiceLocator()->get('playgroundweather_imagemap_form');
        $form->get('submit')->setLabel("Créer");
        $form->setAttribute('action', '');
        $imageMap = new ImageMap();
        $form->bind($imageMap);
        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $form->setData($data);
            if ($form->isValid()) {
                $imageMap = $this->getImageMapService()->create($data);
                if ($imageMap) {
                    return $this->redirect()->toRoute('admin/weather/images');
                }
            } else {
                foreach ($form->getMessages() as $field => $errMsg) {
                    $this->flashMessenger()->addMessage($field . ' - ' . current($errMsg));
                }
                return $this->redirect()->toRoute('admin/weather/images/add');
            }
        }
        return new ViewModel(
            array(
                'form' => $form,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );
    }

    public function editAction()
    {
        $imageMapId = $this->getEvent()->getRouteMatch()->getParam('imageMapId');
        if (!$imageMapId) {
            return $this->redirect()->toRoute('admin/weather/images');
        }
        $imageMap = $this->getImageMapService()->getImageMapMapper()->findById($imageMapId);

        $form = $this->getServiceLocator()->get('playgroundweather_imagemap_form');
        $form->get('submit')->setLabel("Modifier");
        $form->setAttribute('action', '');
        $form->bind($imageMap);
        $locations = array();
        if (!empty($imageMap->getLocations()->getValues())) {
            foreach ($imageMap->getLocations()->getValues() as $location) {
                $locations[] = $location->getId();
            }
        }
        $form->get('locationsCheckboxes')->setValue($locations);

        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            $form->setData($data);
            if ($form->isValid()) {
                $imageMap = $this->getImageMapService()->edit($imageMapId, $data);
                if ($imageMap) {
                    return $this->redirect()->toRoute('admin/weather/images');
                }
            } else {
                foreach ($form->getMessages() as $field => $errMsg) {
                    $this->flashMessenger()->addMessage($field . ' - ' . current($errMsg));
                }
                return $this->redirect()->toRoute('admin/weather/images/edit', array('imageMapId' => $imageMap->getId()));
            }
        }

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-weather/image-map/add');
        $viewModel->setVariables(
            array(
                'form' => $form,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );
        return $viewModel;
    }

    public function removeAction()
    {
        $imageMapId = $this->getEvent()->getRouteMatch()->getParam('imageMapId');
        if (!$imageMapId) {
            return $this->redirect()->toRoute('admin/weather/images');
        }
        $result = $this->getImageMapService()->remove($imageMapId);
        if (!$result) {
            $this->flashMessenger()->addMessage('Une erreur est survenue pendant la suppression de la carte');
        } else {
            $this->flashMessenger()->addMessage('La carte a bien été supprimée');
        }
        return $this->redirect()->toRoute('admin/weather/images');
    }

    public function listAction()
    {
        $adapter = new DoctrineAdapter(
            new LargeTablePaginator(
                $this->getImageMapService()->getImageMapMapper()->queryAll(array('name' => 'ASC'))
            )
        );
        $paginator = new Paginator($adapter);

        $paginator->setItemCountPerPage(25);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return new ViewModel(array(
            'imageMaps' => $paginator,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));
    }

    public function getImageMapService()
    {
        if ($this->imageMapService === null) {
            $this->imageMapService = $this->getServiceLocator()->get('playgroundweather_imagemap_service');
        }
        return $this->imageMapService;
    }

    public function setImageMapService($imageMapService)
    {
        $this->imageMapService = $imageMapService;

        return $this;
    }

}