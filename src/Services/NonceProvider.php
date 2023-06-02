<?php

namespace NSWDPC\AnalyticsChooser\Services;

use SilverStripe\Core\Injector\Injectable;
use NSWDPC\Utilities\ContentSecurityPolicy\Nonce;

/**
 * Provides nonce attribute value for scripts.
 * Use Injector to provide a value from a different implementation
 * @author James
 */
class NonceProvider {

    use Injectable;

    /**
     * Return the value for the nonce attribute
     */
    public function getNonceValue() : string {
        if(class_exists(Nonce::class) && ($nonce = Nonce::getNonce())) {
            return $nonce;
        } else {
            return "";
        }
    }

}
