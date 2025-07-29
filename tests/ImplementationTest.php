<?php

namespace NSWDPC\AnalyticsChooser\Tests;

use NSWDPC\AnalyticsChooser\Services\GA3;
use NSWDPC\AnalyticsChooser\Services\GA4;
use NSWDPC\AnalyticsChooser\Services\GTM;
use NSWDPC\AnalyticsChooser\Services\GTMNonce;
use NSWDPC\AnalyticsChooser\Services\NonceProvider;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

/**
 * test various implementations
 */
class ImplementationTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected function setUp(): void
    {
        parent::setUp();
        Injector::inst()->registerService(new TestNonceProvider(), NonceProvider::class);
    }

    public function testGetImplementations(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $list = $siteConfig->getAnalyticsImplementations();
        $this->assertArrayHasKey('GA3', $list);
        $this->assertArrayHasKey('GA4', $list);
        $this->assertArrayHasKey('GTM', $list);
        $this->assertArrayHasKey('GTMNonce', $list);
    }

    public function testGA3(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GA3::getCode();
        $siteConfig->GoogleTagManagerCode = 'ga3-test-code';
        $siteConfig->write();

        $this->assertInstanceOf(GA3::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringContainsString('"ga3-test-code"', $htmlTemplate);
    }

    public function testGA4(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GA4::getCode();
        $siteConfig->GoogleTagManagerCode = 'ga4-test-code';
        $siteConfig->write();

        $this->assertInstanceOf(GA4::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringContainsString('"ga4-test-code"', $htmlTemplate);


    }

    public function testGTM(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GTM::getCode();
        $siteConfig->GoogleTagManagerCode = 'gtm-test-code';
        $siteConfig->write();

        $this->assertInstanceOf(GTM::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringContainsString('"gtm-test-code"', $htmlTemplate);
    }

    public function testGTMWithNoNonce(): void
    {

        Injector::inst()->registerService(new TestNoNonceProvider(), NonceProvider::class);

        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GTM::getCode();
        $siteConfig->GoogleTagManagerCode = 'gtm-test-code';
        $siteConfig->write();

        $this->assertInstanceOf(GTM::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script>', $htmlTemplate);
        $this->assertStringContainsString('"gtm-test-code"', $htmlTemplate);
    }

    public function testGTMNonce(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GTMNonce::getCode();
        $siteConfig->GoogleTagManagerCode = 'gtmnonce-test-code';
        $siteConfig->write();

        $this->assertInstanceOf(GTMNonce::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringContainsString('"gtmnonce-test-code"', $htmlTemplate);
    }

    public function testGA3InvalidCode(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GA3::getCode();
        $siteConfig->GoogleTagManagerCode = 'GA3-;</script><script>console.log(1);';
        $siteConfig->write();

        $this->assertInstanceOf(GA3::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringContainsString(json_encode(htmlspecialchars($siteConfig->GoogleTagManagerCode)), $htmlTemplate);
    }

    public function testGA4InvalidCode(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GA4::getCode();
        $siteConfig->GoogleTagManagerCode = 'GA4-;</script><script>console.log(1);';
        $siteConfig->write();

        $this->assertInstanceOf(GA4::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringContainsString(json_encode(htmlspecialchars($siteConfig->GoogleTagManagerCode)), $htmlTemplate);
    }

    public function testGTMInvalidCode(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GTM::getCode();
        $siteConfig->GoogleTagManagerCode = 'GTM-;</script><script>console.log(1);';
        $siteConfig->write();

        $this->assertInstanceOf(GTM::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringContainsString(json_encode(htmlspecialchars($siteConfig->GoogleTagManagerCode)), $htmlTemplate);
    }

    public function testGTMNonceInvalidCode(): void
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GTMNonce::getCode();
        $siteConfig->GoogleTagManagerCode = 'GTMNonce-;</script><script>console.log(1);';
        $siteConfig->write();

        $this->assertInstanceOf(GTMNonce::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringContainsString(json_encode(htmlspecialchars($siteConfig->GoogleTagManagerCode)), $htmlTemplate);
    }

}
