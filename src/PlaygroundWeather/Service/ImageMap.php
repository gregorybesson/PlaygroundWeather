<?php

namespace PlaygroundWeather\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundWeather\Entity\ImageMap as ImageMapEntity;
use PlaygroundWeather\Mapper\ImageMap as ImageMapMapper;
use Zend\Stdlib\ErrorHandler;
use PlaygroundWeather\Options\ModuleOptions;

class ImageMap extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var ImageMapMapper
     */
    protected $imageMapMapper;

    /**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function create(array $data)
    {
        $imageMap = new ImageMapEntity();
        $imageMap->populate($data);
        $imageMap = $this->getImageMapMapper()->insert($imageMap);
        if (!$imageMap) {
            return false;
        }
        return $this->update($imageMap->getId(), $data);
    }

    public function edit($imageMap, array $data)
    {
        // find by Id the corresponding imageMap
        $imageMap = $this->getImageMapMapper()->findById($imageMap);
        if (!$imageMap) {
            return false;
        }
        return $this->update($imageMap->getId(), $data);
    }

    public function update($codeId, array $data)
    {
        $code = $this->getImageMapMapper()->findById($codeId);
        $code->populate($data);
        $associatedCode = null;
        // handle association with an other code
        if (isset($data['associatedCode'])) {
            $associatedCode = $this->getImageMapMapper()->findById($data['associatedCode']);
        }

        // Handle Icon loading
        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $media_url = $this->getOptions()->getMediaUrl() . '/';

        if (!empty($data['icon']['tmp_name'])) {
            $oldIconURL = $code->getIconURL();
            ErrorHandler::start();
            $data['icon']['name'] = $codeId . "-" . $data['icon']['name'];
            move_uploaded_file($data['icon']['tmp_name'], $path . $data['icon']['name']);
            $code->setIconURl($media_url . $data['icon']['name']);
            ErrorHandler::stop(true);
            if ($oldIconURL) {
                $real_media_path = realpath($path) . DIRECTORY_SEPARATOR;
                unlink(str_replace($media_url, $real_media_path,$oldIconURL));
            }
        }
        $code->setAssociatedCode($associatedCode);
        $this->getImageMapMapper()->update($code);

        return $code;
    }

    public function remove($codeId) {
        $imageMapMapper = $this->getImageMapMapper();
        $imageMap = $imageMapMapper->findById($codeId);
        if (!$imageMap) {
            return false;
        }
        if ($imageMap->getIconURL()) {
            $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
            $real_media_path = realpath($path) . DIRECTORY_SEPARATOR;
            $media_url = $this->getOptions()->getMediaUrl() . '/';
            unlink(str_replace($media_url, $real_media_path, $imageMap->getIconURL()));
        }
        $imageMapMapper->remove($imageMap);
        return true;
    }

    public function getImageMapMapper()
    {
        if (null === $this->imageMapMapper) {
            $this->imageMapMapper = $this->getServiceManager()->get('playgroundweather_imagemap_mapper');
        }
        return $this->imageMapMapper;
    }

    public function setImageMapMapper(ImageMapMapper $imageMapMapper)
    {
        $this->imageMapMapper = $imageMapMapper;
        return $this;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('playgroundweather_module_options'));
        }
        return $this->options;
    }
}