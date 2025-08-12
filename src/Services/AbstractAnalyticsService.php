<?php

namespace NSWDPC\AnalyticsChooser\Services;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\HTML;
use Symbiote\MultiValueField\ORM\FieldType\MultiValueField;

/**
 * Provides an abstract implementation for analytics services
 * @author James
 */
abstract class AbstractAnalyticsService
{
    use Configurable;

    /**
     * Allow a service to be enabled (default) or not in configuration
     */
    private static bool $enabled = true;

    /**
     * Determine whether service is enabled or not
     */
    public static function isEnabled(): bool
    {
        return (bool) static::config()->get('enabled');
    }

    /**
     * Return a string value for the implementation unique identifier
     */
    abstract public static function getCode(): string;

    /**
     * Return a string value to describe to a CMS admin what this does
     */
    abstract public static function getDescription(): string;

    /**
     * Add requirements or similar to the current request, return template variable
     * for inclusion in template, or null
     * @param string $code the analytics indentification code e.g. the GA4 config value
     * @param array $context an array of custom context values to assist the service
     */
    abstract public function provide(string $code = '', array $context = []): ?DBHTMLText;

    /**
     * Return an instance of the implementation based on the code provided
     */
    final public static function getImplementation(string $code): ?AbstractAnalyticsService
    {
        $implementations = ClassInfo::subclassesFor(AbstractAnalyticsService::class, false);
        foreach ($implementations as $implementation) {
            if ($implementation::isEnabled() && $implementation::getCode() == $code) {
                return Injector::inst()->get($implementation);
            }
        }

        return null;
    }

    /**
     * Get all supported implementations
     */
    final public static function getImplementations(): array
    {
        $implementations = ClassInfo::subclassesFor(AbstractAnalyticsService::class, false);
        $selections = [];
        foreach ($implementations as $implementation) {
            if (!$implementation::isEnabled()) {
                continue;
            }

            $selections[ $implementation::getCode() ] = $implementation::getDescription();
        }

        asort($selections);
        return $selections;
    }

    /**
     * Try to apply a nonce to a script
     */
    final public function applyNonce(string $script, array $attributes = []): ?DBHTMLText
    {
        $nonceProvider = Injector::inst()->get(NonceProvider::class);
        if ($nonceProvider && $nonceValue = $nonceProvider->getNonceValue()) {
            $attributes['nonce'] = $nonceValue;
        }

        $html = HTML::createTag(
            'script',
            $attributes,
            trim($script) // the script contents
        );
        $field = DBField::create_field('HTMLFragment', $html);
        if ($field instanceof DBHTMLText) {
            return $field;
        } else {
            return null;
        }

    }

    /**
     * Give a context passed to a provider, return the SiteConfig value if it is a SiteConfig instance
     */
    public function getSiteConfigFromContext(array $context): ?SiteConfig
    {
        if (isset($context['SiteConfig']) && $context['SiteConfig'] instanceof SiteConfig) {
            return $context['SiteConfig'];
        } else {
            return null;
        }
    }

    /**
     * Return the analytics config value based on how the value is formatted
     * As the KeyValueField stores all values as strings, allow for certain strings
     * to represent certain config values e.g false, true, null, ints and floats
     * If a value is quoted e.g "foo" return it as the string foo
     * The fallback is to return the string as-is
     */
    final public function getAnalyticsConfigValue(string $configValue): mixed
    {
        $pattern = "/^\"[^\"]+\"$/";
        if(preg_match($pattern, $configValue) == 1) {
            // literal quoted string, return as a string with quotes removed
            return trim($configValue, "\"");
        } elseif($configValue === "false") {
            return false;
        } elseif($configValue === "true") {
            return true;
        } else if($configValue === "null") {
            return null;
        } else if(($intValue = filter_var($configValue, FILTER_VALIDATE_INT)) !== false) {
            return $intValue;
        } else if(($floatValue = filter_var($configValue, FILTER_VALIDATE_FLOAT)) !== false) {
            return $floatValue;
        } else {
            return $configValue;
        }
    }

    /**
     * Given a site config, return the AnalyticsKeyValue data as an array of keys and values
     * Allows for boolean and null special values, if quoted these are treated as strings
     */
    public function getAnalyticsConfig(array $context): array
    {
        try {
            if (($siteConfig = $this->getSiteConfigFromContext($context)) instanceof \SilverStripe\SiteConfig\SiteConfig) {
                $config = $siteConfig->dbObject('AnalyticsKeyValue');
                if ($config instanceof MultiValueField) {
                    $keyValue = $config->getValue();
                    $analyticsConfig = [];
                    if(is_array($keyValue)) {
                        foreach($keyValue as $configKey => $configValue) {
                            $analyticsConfig[$configKey] = $this->getAnalyticsConfigValue($configValue);
                        }
                    }
                    return $analyticsConfig;
                }
            }
        } catch (\Exception) {
        }

        return [];
    }

    /**
     * Return a JSON encoded representation of the configuration for use in a script tag
     */
    final public function getAnalyticsConfigForScript(array $context): string
    {
        $config = $this->getAnalyticsConfig($context);
        return json_encode($config, JSON_FORCE_OBJECT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Get the service code, for use in a <script> tag
     */
    final public function getAnalyticsServiceCodeForScript(string $code): string
    {
        return \SilverStripe\Core\Convert::raw2js($code);
    }

    /**
     * Return the provider script
     */
    final public function getProviderScript($value): ?DBHTMLText
    {
        $field = DBField::create_field('HTMLFragment', $value);
        if ($field instanceof DBHTMLText) {
            return $field;
        } else {
            return null;
        }
    }

}
