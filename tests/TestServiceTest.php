<?php

namespace NSWDPC\AnalyticsChooser\Tests;

use NSWDPC\AnalyticsChooser\Services\AbstractAnalyticsService;
use NSWDPC\AnalyticsChooser\Services\NonceProvider;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

/**
 * Test the TestService
 */
class TestServiceTest extends SapphireTest
{
    protected $usesDatabase = true;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        Injector::inst()->registerService(TestNonceProvider::create(), NonceProvider::class);
    }

    public function testTestService(): void
    {
        $code = 'test-service-abcd1234';
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = TestService::getCode();
        $siteConfig->GoogleTagManagerCode = $code;
        $siteConfig->AnalyticsKeyValue = [
            'key1' => 'value1',
            'key2' =>'"value2"',
            'key3' => '3',
            'key4' => '"4"',
            'key5' => "false",
            'key6' => '"false"'
        ];
        $siteConfig->write();

        $this->assertInstanceOf(TestService::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $expected = "<script nonce=\"test-only\">testFunction('config','test-service-abcd1234', {\"key1\":\"value1\",\"key2\":\"value2\",\"key3\":3,\"key4\":\"4\",\"key5\":false,\"key6\":\"false\"});</script>";
        $this->assertEquals($expected, $htmlTemplate);
    }

    public function testConfigValues(): void
    {
        // literal => expected
        $values = [
            "3" => 3,
            "false" => false,
            "true" => true,
            "null" => null,
            "\"false\"" => "false",
            "\"true\"" => "true",
            "\"null\"" => "null",
            "\"7\"" => "7",
            "7" => 7,
            "name" => "name",
            "\"name\"" => "name",
            "3.09" => 3.09,
            "\"4.12\"" => "4.12",
            "'foo'" => "'foo'"
        ];

        $testService = AbstractAnalyticsService::getImplementation(TestService::getCode());
        foreach($values as $input => $expectedOutput) {
            $this->assertSame($expectedOutput, $testService->getAnalyticsConfigValue($input), "Failed to validate '{$input}'");
        }
    }

}
