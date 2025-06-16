<?php

namespace NSWDPC\AnalyticsChooser\Services;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\HTML;

/**
 * Provides an abstract implementation for analytics services
 * @author James
 */
abstract class AbstractAnalyticsService
{
    use Configurable;

    /**
     * Allow a service to be enabled (default) or not in configuration
     * @var bool
     */
    private static $enabled = true;

    /**
     * Determine whether service is enabled or not
     */
    public static function isEnabled(): bool
    {
        return static::config()->get('enabled') ? true : false;
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
                $implementation = Injector::inst()->get($implementation);
                return $implementation;
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
    final public function applyNonce(string $script, $attributes = []): DBHTMLText
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
        return DBField::create_field(DBHTMLText::class, $html);

    }
}
