<?php

namespace NSWDPC\AnalyticsChooser\Services;

use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * GTM implementation
 * @author James
 */
class GTM extends AbstractAnalyticsService {

    /**
     * @inheritdoc
     */
    public static function getCode() : string {
        return "GTM";
    }

    /**
     * @inheritdoc
     */
    public static function getDescription() : string {
        return _t('AnalyticsChooser.GOOGLE_TAG_MANAGER', 'Google Tag Manager (gtm.js)');
    }

    /**
     * Add requirements or similar to the current request
     */
    public function provide(string $code = '', array $context = []) : ?DBHTMLText {
        if($code === '' || $code === '0') {
            // a code is required
            return null;
        }

        $code = json_encode(htmlspecialchars($code));
        $script =
<<<JAVASCRIPT
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer', {$code});
JAVASCRIPT;
        return parent::applyNonce($script);
    }
}
