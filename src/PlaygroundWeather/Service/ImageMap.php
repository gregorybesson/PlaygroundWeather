<?php

namespace PlaygroundWeather\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundWeather\Entity\ImageMap as ImageMapEntity;
use PlaygroundWeather\Mapper\ImageMap as ImageMapMapper;
use PlaygroundWeather\Mapper\Location as LocationMapper;
use Zend\Stdlib\ErrorHandler;
use PlaygroundWeather\Options\ModuleOptions;
use PlaygroundWeather\Entity\Location;

class ImageMap extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var LocationMapper
     */
    protected $locationMapper;

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
        return $this->update($imageMapId, $data);
    }

    public function update($imageMapId, array $data)
    {
        // find by Id the corresponding imageMap
        $imageMap = $this->getImageMapMapper()->findById($imageMapId);
        if (!$imageMap) {
            return false;
        }
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

        $values = $imageMap->getLocations()->getValues();
        if (!empty($values)) {
            $imageMap->getLocations()->clear();
        }
        foreach ($data['locationsCheckboxes'] as $locationId) {
            $location = $this->getLocationMapper()->findById($locationId);
            if ($location) {
                $imageMap->addLocation($location);
            }
        }
        $imageMap->populate($data);
        $this->getImageMapMapper()->update($imageMap);

        return $imageMap;
    }

    public function remove($imageMapId)
    {
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

    public function getMercatorCoordinates($lat, $lon)
    {
        $mercatorX = ((float) $lon + 180.0) / 360;
        $mercatorY = ((float) $lat / 180.0 * pi());
        $mercatorY = 0.5 - log( (1. + sin($mercatorY)) / (1. - sin($mercatorY))) / (4. * pi());
        return array($mercatorX, $mercatorY);
    }

    public function getPosition($imageMap, $lat, $lon)
    {
        $coorPt1 = $this->getMercatorCoordinates($imageMap->getLatitude1(), $imageMap->getLongitude1());
        $coorPt2 = $this->getMercatorCoordinates($lat, $lon);

        $diffX = (float) (current($coorPt2) - current($coorPt1));
        $diffY = (float) (end($coorPt2) - end($coorPt1));

        $scale = $this->getScale($imageMap);

        $coorX = round($diffX * $imageMap->getImageWidth() / current($scale));
        $coorY = round($diffY * $imageMap->getImageHeight() / end($scale));

        return array($coorX, $coorY);
    }

    public function getScale($imageMap)
    {
        $coorPt1 = $this->getMercatorCoordinates($imageMap->getLatitude1(), $imageMap->getLongitude1());
        $coorPt2 = $this->getMercatorCoordinates($imageMap->getLatitude2(), $imageMap->getLongitude2());

        $diffX = (float) (current($coorPt2) - current($coorPt1));
        $diffY = (float) (end($coorPt2) - end($coorPt1));

        return array($diffX, $diffY);
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

    public function getLocationMapper()
    {
        if (null === $this->locationMapper) {
            $this->locationMapper = $this->getServiceManager()->get('playgroundweather_location_mapper');
        }
        return $this->locationMapper;
    }

    public function setLocationMapper(LocationMapper $locationMapper)
    {
        $this->locationMapper = $locationMapper;
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