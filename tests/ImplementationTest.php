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

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        Injector::inst()->registerService(TestNonceProvider::create(), NonceProvider::class);
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
        $code = 'ga3-test-code';
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GA3::getCode();
        $siteConfig->GoogleTagManagerCode = $code;
        $siteConfig->write();

        $this->assertInstanceOf(GA3::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringContainsString("ga('create', '{$code}', 'auto');", $htmlTemplate);
    }

    public function testGA4(): void
    {
        $code = 'ga4-test-code';
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GA4::getCode();
        $siteConfig->GoogleTagManagerCode = $code;
        $siteConfig->write();

        $this->assertInstanceOf(GA4::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringContainsString("gtag('config', '{$code}', {});", $htmlTemplate);


    }

    public function testGTM(): void
    {
        $code = 'gtm-test-code';
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GTM::getCode();
        $siteConfig->GoogleTagManagerCode = $code;
        $siteConfig->write();

        $this->assertInstanceOf(GTM::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringContainsString("(window,document,'script','dataLayer','{$code}');", $htmlTemplate);
    }

    public function testGTMWithNoNonce(): void
    {

        Injector::inst()->registerService(TestNoNonceProvider::create(), NonceProvider::class);

        $code = 'gtm-test-code';
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GTM::getCode();
        $siteConfig->GoogleTagManagerCode = $code;
        $siteConfig->write();

        $this->assertInstanceOf(GTM::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script>', $htmlTemplate);
        $this->assertStringContainsString("(window,document,'script','dataLayer','{$code}');", $htmlTemplate);
    }

    public function testGTMNonce(): void
    {
        $code = 'gtmnonce-test-code';
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GTMNonce::getCode();
        $siteConfig->GoogleTagManagerCode = $code;
        $siteConfig->write();

        $this->assertInstanceOf(GTMNonce::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringContainsString("setAttribute('nonce',n.nonce||n.getAttribute('nonce'));", $htmlTemplate);
        $this->assertStringContainsString("(window,document,'script','dataLayer','{$code}');", $htmlTemplate);
    }

    public function testGA3InvalidCode(): void
    {

        $code = 'GA3-;</script><script>console.log(1);';
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GA3::getCode();
        $siteConfig->GoogleTagManagerCode = $code;
        $siteConfig->write();

        $this->assertInstanceOf(GA3::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringNotContainsString("ga('create', '{$code}', 'auto');", $htmlTemplate);
    }

    public function testGA4InvalidCode(): void
    {
        $code = 'GA4-;</script><script>console.log(1);';
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GA4::getCode();
        $siteConfig->GoogleTagManagerCode = $code;
        $siteConfig->write();

        $this->assertInstanceOf(GA4::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringNotContainsString("gtag('config', '{$code}', {});", $htmlTemplate);
    }

    public function testGTMInvalidCode(): void
    {
        $code = 'GTM-;</script><script>console.log(1);';
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GTM::getCode();
        $siteConfig->GoogleTagManagerCode = $code;
        $siteConfig->write();

        $this->assertInstanceOf(GTM::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringNotContainsString("(window,document,'script','dataLayer','{$code}');", $htmlTemplate);
    }

    public function testGTMNonceInvalidCode(): void
    {
        $code = 'GTMNonce-;</script><script>console.log(1);';
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->GoogleImplementation = GTMNonce::getCode();
        $siteConfig->GoogleTagManagerCode = $code;
        $siteConfig->write();

        $this->assertInstanceOf(GTMNonce::class, $siteConfig->getAnalyticsImplementation());

        $htmlField = $siteConfig->ProvideAnalyticsImplementation();
        $htmlTemplate = $htmlField->forTemplate();

        $this->assertStringContainsString('<script nonce="test-only">', $htmlTemplate);
        $this->assertStringNotContainsString("(window,document,'script','dataLayer','{$code}');", $htmlTemplate);
    }

}
