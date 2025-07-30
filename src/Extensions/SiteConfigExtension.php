<?php

namespace NSWDPC\AnalyticsChooser\Extensions;

use NSWDPC\AnalyticsChooser\Services\AbstractAnalyticsService;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Provide administration selection options for choosing an Analytics service
 * For historical reasons the fields are prefixed Google*
 * @property ?string $GoogleTagManagerCode
 * @property ?string $GoogleImplementation
 * @extends \SilverStripe\Core\Extension<\SilverStripe\SiteConfig\SiteConfig&static>
 */
class SiteConfigExtension extends \SilverStripe\Core\Extension
{
    /**
     * @config
     */
    private static array $db = [
        'GoogleTagManagerCode' => 'Varchar(255)',
        'GoogleImplementation' => 'Varchar(16)'
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
                                'example' => 'Eg. GTM-XXXX (GTM), UA-XXXX (GA3), G-XXXX (GA4)'
                            ]
                        )
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
            $context = [
                'SiteConfig' => $this->getOwner()
            ];
            return $inst->provide($this->getOwner()->GoogleTagManagerCode ?? '', $context);
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
