<?php

namespace NSWDPC\AnalyticsChooser\Tests;

use NSWDPC\AnalyticsChooser\Services\NonceProvider;
use SilverStripe\Dev\TestOnly;

/**
 * Provide nonce values for script tags in tests
 */
class TestNonceProvider extends NonceProvider implements TestOnly
{

    #[\Override]
    public function getNonceValue(): string
    {
        return "test-only";
    }

}
