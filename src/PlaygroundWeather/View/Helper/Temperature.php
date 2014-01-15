<?php

namespace PlaygroundWeather\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Locale;

class Temperature extends AbstractHelper
{
    /**
     * Locale to use instead of the default
     *
     * @var string
     */
    protected $locale;

    //locales des pays utilisant le degré Fahrenheit :
    // Etats-Unis, Belize, Iles Caïmans
    protected $localesFahrenheit = array('en_US', 'en_BZ', 'en_KY');

    public function __invoke($temperatureC, $locale = null)
    {
        if (!is_float($temperatureC) && !is_int($temperatureC)) {
            return '';
        }
        if ($locale === null) {
            $locale = $this->getLocale();
        }
        if (in_array($locale, $this->localesFahrenheit)) {
            return $this->celsiusToFahrenheit($temperatureC) . '°F';
        }
        return strval($temperatureC) . '°C';
    }

    public function celsiusToFahrenheit($tempC)
    {
        return (1.8 * $tempC)+32;
    }

    public function getLocale()
    {
        if ($this->locale === null) {
            $this->locale = Locale::getDefault();
        }

        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = (string) $locale;
        return $this;
    }
}