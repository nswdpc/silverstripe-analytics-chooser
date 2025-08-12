<?php

namespace NSWDPC\AnalyticsChooser\Services;

use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * GA3 implementation
 * @author James
 * @deprecated Universal Analytics has been replaced by GA4. Will be removed in a future major version release
 */
class GA3 extends AbstractAnalyticsService
{
    /**
     * Return a string value for the implementation
     */
    public static function getCode(): string
    {
        return "GA3";
    }

    /**
     * @inheritdoc
     */
    public static function getDescription(): string
    {
        return _t('AnalyticsChooser.GOOGLE_ANALYTICS_3', 'Google Analytics v3 (analytics.js) - do not use');
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

        $configCode = $this->getAnalyticsServiceCodeForScript($code);
        $script =
<<<JAVASCRIPT
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
ga('create', '{$configCode}', 'auto');
ga('send', 'pageview');
JAVASCRIPT;
        return parent::applyNonce($script);
    }
}
