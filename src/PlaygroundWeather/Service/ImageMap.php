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

    public function edit($imageMapId, array $data)
    {
        // find by Id the corresponding imageMap
        $imageMap = $this->getImageMapMapper()->findById($imageMapId);
        if (!$imageMap) {
            return false;
        }
        return $this->update($imageMap->getId(), $data);
    }

    public function update($imageMapId, array $data)
    {
        $imageMap = $this->getImageMapMapper()->findById($imageMapId);

        // Handle Image upload
        if (!empty($data['image']['tmp_name'])) {
            $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
            $real_media_path = realpath($path) . DIRECTORY_SEPARATOR;
            $media_url = $this->getOptions()->getMediaUrl() . '/';

            $oldImageURL = $imageMap->getImageURL();
            ErrorHandler::start();
            $data['image']['name'] = 'image-map-' . $imageMapId . "-" . $data['image']['name'];
            move_uploaded_file($data['image']['tmp_name'], $path . $data['image']['name']);
            $imageMap->setImageURl($media_url . $data['image']['name']);
            ErrorHandler::stop(true);

            $size = getimagesize(str_replace($media_url, $real_media_path, $imageMap->getImageURl()));
            $data['imageWidth'] = current($size);
            $data['imageHeight'] = next($size);

            if ($oldImageURL) {
                $real_media_path = realpath($path) . DIRECTORY_SEPARATOR;
                unlink(str_replace($media_url, $real_media_path, $oldImageURL));
            }
        }

        $imageMap->populate($data);
        $this->getImageMapMapper()->update($imageMap);

        return $imageMap;
    }

    public function remove($imageMapId) {
        $imageMapMapper = $this->getImageMapMapper();
        $imageMap = $imageMapMapper->findById($imageMapId);
        if (!$imageMap) {
            return false;
        }
        if ($imageMap->getImageURL()) {
            $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
            $real_media_path = realpath($path) . DIRECTORY_SEPARATOR;
            $media_url = $this->getOptions()->getMediaUrl() . '/';
            unlink(str_replace($media_url, $real_media_path, $imageMap->getImageURL()));
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