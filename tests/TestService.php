<?php

namespace NSWDPC\AnalyticsChooser\Tests;

use NSWDPC\AnalyticsChooser\Services\AbstractAnalyticsService;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Provide nonce values for script tags in tests
 */
class TestService extends AbstractAnalyticsService implements TestOnly
{
    public static function getCode(): string
    {
        return "TestService";
    }

    public static function getDescription(): string
    {
        return "Test Service Description";
    }

    public function provide(string $code = '', array $context = []): ?DBHTMLText
    {
        $configEncoded = $this->getAnalyticsConfigForScript($context);
        $configCode = $this->getAnalyticsServiceCodeForScript($code);
        $script =
<<<JAVASCRIPT
testFunction('config','{$configCode}', {$configEncoded});
JAVASCRIPT;
        return parent::applyNonce($script);
    }
}
