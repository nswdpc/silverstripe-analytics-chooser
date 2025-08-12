<?php

namespace NSWDPC\AnalyticsChooser\Services;

use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\HTML;

/**
 * GA4 implementation
 * Refer https://developers.google.com/analytics/devguides/collection/ga4/reference/config for configuration options
 * @author James
 */
class GA4 extends AbstractAnalyticsService
{
    /**
     * Return a string value for the implementation
     */
    public static function getCode(): string
    {
        return "GA4";
    }

    /**
     * @inheritdoc
     */
    public static function getDescription(): string
    {
        return _t('AnalyticsChooser.GOOGLE_ANALYTICS_4', 'Google Analytics v4 (gtag.js)');
    }

    /**
     * Add requirements or similar to the current request
     */
    public function provide(string $code = '', array $context = []): ?DBHTMLText
    {
        if ($code === '') {
            // a code is required
            return null;
        }

        $configEncoded = $this->getAnalyticsConfigForScript($context);
        $configCode = $this->getAnalyticsServiceCodeForScript($code);

        $script =
<<<JAVASCRIPT
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$configCode}', {$configEncoded});
JAVASCRIPT;

        // GA4 requires gtag.js
        $gtag = HTML::createTag(
            'script',
            [
                'src' => "https://www.googletagmanager.com/gtag/js?id=" . $code,
                'async' => 'async'
            ]
        );

        //set up the config script, with an optional nonce attribute value added
        if (($configScript = parent::applyNonce($script)) instanceof \SilverStripe\ORM\FieldType\DBHTMLText) {
            return parent::getProviderScript($gtag . "\n" . $configScript->getValue());
        } else {
            return null;
        }

    }
}
