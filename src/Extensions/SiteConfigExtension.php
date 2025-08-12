<?php

namespace NSWDPC\AnalyticsChooser\Extensions;

use NSWDPC\AnalyticsChooser\Services\AbstractAnalyticsService;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use Symbiote\MultiValueField\Fields\KeyValueField;

/**
 * Provide administration selection options for choosing an Analytics service
 * For historical reasons the fields are prefixed Google*
 * @property ?string $GoogleTagManagerCode
 * @property ?string $GoogleImplementation
 * @property mixed $AnalyticsKeyValue
 * @extends \SilverStripe\Core\Extension<\SilverStripe\SiteConfig\SiteConfig&static>
 */
class SiteConfigExtension extends \SilverStripe\Core\Extension
{
    /**
     * @config
     */
    private static array $db = [
        'GoogleTagManagerCode' => 'Varchar(255)',
        'GoogleImplementation' => 'Varchar(16)',
        'AnalyticsKeyValue' => 'MultiValueField'
    ];

    /**
     * @inheritdoc
     */
    public function updateCMSFields(FieldList $fields)
    {

        $fields->addFieldsToTab(
            'Root.Analytics',
            [
                CompositeField::create(
                    DropdownField::create(
                        'GoogleImplementation',
                        _t('AnalyticsChooser.ANALYTICS_IMPLEMENTATION_FIELD_TITLE', 'Choose an Analytics service'),
                        $this->getAnalyticsImplementations()
                    )->setEmptyString(
                        _t('AnalyticsChooser.SELECT_ONE', '(select)')
                    ),
                    TextField::create(
                        'GoogleTagManagerCode',
                        _t('AnalyticsChooser.ANALYTICS_CODE_FIELD_TITLE', 'Enter the analytics service code')
                    )->setDescription(
                        _t(
                            'AnalyticsChooser.ANALYTICS_CODE_FIELD_EXAMPLE',
                            'Example: {example}',
                            [
                                'example' => 'Eg. GTM-XXXX (GTM), G-XXXX (GA4)'
                            ]
                        )
                    ),
                    KeyValueField::create(
                        'AnalyticsKeyValue',
                        _t('AnalyticsChooser.ANALYTICS_KEYVALUE_TITLE', 'Provide optional key/value configuration for the analytics implementation.')
                    )->setRightTitle(
                        _t('AnalyticsChooser.ANALYTICS_KEYVALUE_EXAMPLE', 'Example: add a variable name on the left and the value of the variable on the right. Use double quote characters for a literal string value e.g. "4".')
                    )
                )->setTitle(
                    _t('AnalyticsChooser.MAIN_FIELD_TITLE', 'Analytics')
                )
            ]
        );

    }

    /**
     * Get the current analytics implementation
     */
    public function getAnalyticsImplementation(): ?AbstractAnalyticsService
    {
        $inst = null;
        if ($implementationCode = $this->getOwner()->GoogleImplementation) {
            $inst = AbstractAnalyticsService::getImplementation($implementationCode);
        }

        return $inst;
    }

    /**
     * Template method to provide implementation of analytics
     */
    public function ProvideAnalyticsImplementation(): ?DBHTMLText
    {
        if (($inst = $this->getAnalyticsImplementation()) instanceof \NSWDPC\AnalyticsChooser\Services\AbstractAnalyticsService) {
            $siteConfig = $this->getOwner();
            $context = [
                'SiteConfig' => $siteConfig
            ];
            return $inst->provide($siteConfig->GoogleTagManagerCode ?? '', $context);
        } else {
            return null;
        }
    }

    /**
     * Get all available implementations
     */
    public function getAnalyticsImplementations(): array
    {
        return AbstractAnalyticsService::getImplementations();
    }

}
