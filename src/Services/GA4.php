<?php

namespace NSWDPC\AnalyticsChooser\Services;

use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\HTML;

/**
 * GA4 implementation
 * @author James
 */
class GA4 extends AbstractAnalyticsService {

    /**
     * Return a string value for the implementation
     */
    public static function getCode() : string {
        return "GA4";
    }

    /**
     * @inheritdoc
     */
    public static function getDescription() : string {
        return _t('AnalyticsChooser.GOOGLE_ANALYTICS_4', 'Google Analytics v4 (gtag.js)');
    }

    /**
     * Add requirements or similar to the current request
     */
    public function provide(string $code = '', array $context = []) : ?DBHTMLText {
        if(!$code) {
            // a code is required
            return null;
        }
        // Set up inline script
        $gtagCode = $code;
        $code = json_encode(htmlspecialchars($code));
        $script =
<<<JAVASCRIPT
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', {$code});
JAVASCRIPT;
        $script = parent::applyNonce($script);
        // GA4 requires gtag.js
        $preScript = HTML::createTag(
            'script',
            [
                'src' => "https://www.googletagmanager.com/gtag/js?id=" . $gtagCode,
                'async' => true
            ]
        );
        $script->setValue( $preScript . "\n" . $script->getValue() );
        return $script;

    }
}
