<?php

namespace PlaygroundWeather\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundWeather\Entity\Code as CodeEntity;
use PlaygroundWeather\Mapper\Code as CodeMapper;
use Zend\Stdlib\ErrorHandler;
use PlaygroundWeather\Options\ModuleOptions;

class Code extends EventProvider implements ServiceManagerAwareInterface
{
    /**
     * @var CodeMapper
     */
    protected $codeMapper;

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
        $code = new CodeEntity();
        $code->populate($data);
        if (!$this->getCodeMapper()->assertNoOther($code)) {
            return false;
        }
        $code = $this->getCodeMapper()->insert($code);
        if (!$code) {
            return false;
        }
        return $this->update($code->getId(), $data);
    }

    public function edit($codeId, array $data)
    {
        return $this->update($codeId, $data);
    }

    public function update($codeId, array $data)
    {
        // find by Id the corresponding code
        $code = $this->getCodeMapper()->findById($codeId);
        if (!$code) {
            return false;
        }
        $code->populate($data);
        $associatedCode = null;
        // handle association with an other code
        if (isset($data['associatedCode'])) {
            $associatedCode = $this->getCodeMapper()->findById($data['associatedCode']);
        }

        if (!empty($data['icon']['tmp_name'])) {
            // Handle Icon loading
            $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
            $media_url = $this->getOptions()->getMediaUrl() . '/';

            $oldIconURL = $code->getIconURL();
            ErrorHandler::start();
            $data['icon']['name'] = 'code-icon-' . $codeId . "-" . $data['icon']['name'];
            move_uploaded_file($data['icon']['tmp_name'], $path . $data['icon']['name']);
            $code->setIconURl($media_url . $data['icon']['name']);
            ErrorHandler::stop(true);
            if ($oldIconURL) {
                $real_media_path = realpath($path) . DIRECTORY_SEPARATOR;
                unlink(str_replace($media_url, $real_media_path,$oldIconURL));
            }
        }
        $code->setAssociatedCode($associatedCode);
        $this->getCodeMapper()->update($code);

        return $code;
    }

    public function remove($codeId) {
        $codeMapper = $this->getCodeMapper();
        $code = $codeMapper->findById($codeId);
        if (!$code) {
            return false;
        }

        if ($code->getIconURL()) {
            $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
            $real_media_path = realpath($path) . DIRECTORY_SEPARATOR;
            $media_url = $this->getOptions()->getMediaUrl() . '/';
            $iconPath = str_replace($media_url, $real_media_path, $code->getIconURL());
            if (file_exists($iconPath)) {
                unlink($iconPath);
            }
        }
        $codeMapper->remove($code);
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

            if (!file_exists($real_media_path.$fileData['name'])) {
                return false;
            }

            $xmlContent = simplexml_load_file($real_media_path.$fileData['name']);

            if ($xmlContent) {
                foreach ($xmlContent->condition as $code) {
                    $this->create(array(
                        'value' => (int) $code->code,
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

    public function getCodeMapper()
    {
        if (null === $this->codeMapper) {
            $this->codeMapper = $this->getServiceManager()->get('playgroundweather_code_mapper');
        }
        return $this->codeMapper;
    }

    public function setCodeMapper(CodeMapper $codeMapper)
    {
        $this->codeMapper = $codeMapper;
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