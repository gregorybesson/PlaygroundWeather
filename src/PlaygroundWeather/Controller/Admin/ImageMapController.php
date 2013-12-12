<?php
namespace PlaygroundWeather\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PlaygroundWeather\Service\ImageMap as ImageMapService;

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
        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $form->setData($data);
            if ($form->isValid()) {
                $imageMap = $this->getImageMapService()->create($form->getData());
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
        // Display
        return new ViewModel(
            array(
                'form' => $form,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );
    }

    public function editAction()
    {
        $viewModel = new ViewModel();
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
                $this->getImageMapService()->getWeatherImageMapMapper()->queryAll(array('country' => 'ASC'))
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