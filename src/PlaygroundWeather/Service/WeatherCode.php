<?php

namespace PlaygroundWeather\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundWeather\Entity\WeatherCode as WeatherCodeEntity;
use PlaygroundCore\Filter\Slugify;
use Zend\Stdlib\ErrorHandler;
use PlaygroundWeather\Options\ModuleOptions;

class WeatherCode extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var WeatherCodeMapper
     */
    protected $weatherCodeMapper;

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
        $weatherCode = new WeatherCodeEntity();
        $weatherCode->populate($data);
        $weatherCode = $this->getWeatherCodeMapper()->insert($weatherCode);
        if (!$weatherCode) {
            return false;
        }
        return $this->update($weatherCode->getId(), $data);
    }

    public function edit($codeId, array $data)
    {
        // find by Id the corresponding weatherCode
        $weatherCode = $this->getWeatherCodeMapper()->findById($codeId);
        if (!$weatherCode) {
            return false;
        }
        return $this->update($weatherCode->getId(), $data);
    }

    public function update($codeId, array $data)
    {
        $code = $this->getWeatherCodeMapper()->findById($codeId);
        $code->populate($data);
        $associatedCode = null;
        // handle association with an other code
        if (isset($data['associatedCode'])) {
            $associatedCode = $this->getWeatherCodeMapper()->findById($data['associatedCode']);
        }

        // Handle Icon loading
        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $media_url = $this->getOptions()->getMediaUrl() . '/';

        if (!empty($data['icon']['tmp_name'])) {
            $oldIcon = $code->getIconURL();
            ErrorHandler::start();
            $data['icon']['name'] = $codeId . "-" . $data['icon']['name'];
            move_uploaded_file($data['icon']['tmp_name'], $path . $data['icon']['name']);
            $code->setIconURl($media_url . $data['icon']['name']);
            ErrorHandler::stop(true);
            if ($oldIcon) {
                unlink($oldIcon);
            }
        }
        $code->setAssociatedCode($associatedCode);
        $this->getWeatherCodeMapper()->update($code);

        return $code;
    }

    public function remove($codeId) {
        $weatherCodeMapper = $this->getWeatherCodeMapper();
        $weatherCode = $weatherCodeMapper->findById($codeId);
        if (!$weatherCode) {
            return false;
        }
        if ($weatherCode->getIconURL()) {
            $media_url = $this->getOptions()->getMediaUrl() . '/';
            unlink($media_url . $weatherCode->getIconURL());
        }
        $weatherCodeMapper->remove($weatherCode);
        return true;
    }

    public function import($fileData) {
        if (!empty($fileData['tmp_name'])) {
            $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
            $real_media_path = realpath($path) . DIRECTORY_SEPARATOR;

            // use the xml data as object
            ErrorHandler::start();
            move_uploaded_file($fileData['tmp_name'], $path . $fileData['name']);
            ErrorHandler::stop(true);
            $xmlContent = simplexml_load_file($real_media_path.$fileData['name']);

            if ($xmlContent) {
                foreach ($xmlContent->condition as $code) {
                    $this->create(array(
                        'code' => (int) $code->code,
                        'description' => (string) $code->description,
                        'isDefault' => 1,
                    ));
                }
                // remove the csv file from folder
                unlink($real_media_path.$fileData['name']);
                return true;
            }
        }
        return false;
    }

    public function getWeatherCodeMapper()
    {
        if (null === $this->weatherCodeMapper) {
            $this->weatherCodeMapper = $this->getServiceManager()->get('playgroundweather_weathercode_mapper');
        }
        return $this->weatherCodeMapper;
    }

    public function setWeatherCodeMapper(WeatherCodeMapper $weatherCodeMapper)
    {
        $this->weatherCodeMapper = $weatherCodeMapper;
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