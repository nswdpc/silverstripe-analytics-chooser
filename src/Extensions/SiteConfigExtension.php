<?php

namespace NSWDPC\AnalyticsChooser\Extensions;

use NSWDPC\AnalyticsChooser\Services\AbstractAnalyticsService;
use Silverstripe\ORM\DataExtension;
use Silverstripe\Forms\FieldList;
use Silverstripe\Forms\CompositeField;
use Silverstripe\Forms\DropdownField;
use Silverstripe\Forms\TextField;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Provide administration selection options for choosing an Analytics service
 * For historical reasons the fields are prefixed Google*
 */
class SiteConfigExtension extends DataExtension
{

    /**
     * @var array
     * @config
     */
    private static $db = [
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
    public function getAnalyticsImplementation() : ?AbstractAnalyticsService {
        $inst = null;
        if($implementationCode = $this->owner->GoogleImplementation) {
            $inst = AbstractAnalyticsService::getImplementation( $implementationCode );
        }
        return $inst;
    }

    /**
     * Template method to provide implementation of analytics
     */
    public function ProvideAnalyticsImplementation() : ?DBHTMLText {
        if($this->owner->GoogleTagManagerCode && ($inst = $this->getAnalyticsImplementation())) {
            return $inst->provide($this->owner->GoogleTagManagerCode);
        } else {
            return null;
        }
    }

    /**
     * Get all available implementations
     */
    public function getAnalyticsImplementations() : array {
        return AbstractAnalyticsService::getImplementations();
    }

}
