<?php

use CupNoodles\TaxClasses\Models\TaxClass;

$config['list']['filter'] = [
    'search' => [
        'prompt' => 'lang:cupnoodles.taxclasses::default.text_filter_search',
        'mode' => 'all',
    ],
];

$config['list']['toolbar'] = [
    'buttons' => [
        'create' => [
            'label' => 'lang:igniter::admin.button_new',
            'class' => 'btn btn-primary',
            'href' => 'cupnoodles/taxclasses/tax_classes/create',
        ],
        'conditions' => [
            'label' => 'lang:cupnoodles.taxclasses::default.button_cart_conditions',
            'class' => 'btn btn-default',
            'href' => 'extensions/edit/igniter/cart/settings',
        ],
    ],
];

$config['list']['bulkActions'] = [
    'delete' => [
        'label' => 'lang:igniter::admin.button_delete',
        'class' => 'btn btn-light text-danger',
        'data-request-confirm' => 'lang:cupnoodles.taxclasses::default.alert_delete_confirm',
    ],
];

$config['list']['columns'] = [
    'edit' => [
        'type' => 'button',
        'iconCssClass' => 'fa fa-pencil',
        'attributes' => [
            'class' => 'btn btn-edit',
            'href' => 'cupnoodles/taxclasses/tax_classes/edit/{tax_class_id}',
        ],
    ],
    'name' => [
        'label' => 'lang:cupnoodles.taxclasses::default.name',
        'type' => 'text',
        'searchable' => true,
    ],
    'label' => [
        'label' => 'lang:cupnoodles.taxclasses::default.label',
        'type' => 'text',
        'searchable' => true,
    ],
    'rate' => [
        'label' => 'lang:cupnoodles.taxclasses::default.rate',
        'type' => 'text',
        'formatter' => fn($record, $column, $value): string => e($record->formattedRate()),
    ],
    'include_in_price' => [
        'label' => 'lang:cupnoodles.taxclasses::default.column_included',
        'type' => 'switch',
        'onText' => 'lang:igniter::admin.text_yes',
        'offText' => 'lang:igniter::admin.text_no',
    ],
    'menus_count' => [
        'label' => 'lang:cupnoodles.taxclasses::default.column_menus',
        'type' => 'number',
        'sortable' => false,
        'searchable' => false,
    ],
    'categories_count' => [
        'label' => 'lang:cupnoodles.taxclasses::default.column_categories',
        'type' => 'number',
        'sortable' => false,
        'searchable' => false,
    ],
    'tax_class_id' => [
        'label' => 'lang:igniter::admin.column_id',
        'invisible' => true,
    ],
];

$config['form']['toolbar'] = [
    'buttons' => [
        'save' => [
            'label' => 'lang:igniter::admin.button_save',
            'context' => ['create', 'edit'],
            'partial' => 'form/toolbar_save_button',
            'class' => 'btn btn-primary',
            'data-request' => 'onSave',
            'data-progress-indicator' => 'igniter::admin.text_saving',
        ],
        'delete' => [
            'label' => 'lang:igniter::admin.button_icon_delete',
            'class' => 'btn btn-danger',
            'data-request' => 'onDelete',
            'data-request-data' => "_method:'DELETE'",
            'data-request-confirm' => 'lang:cupnoodles.taxclasses::default.alert_delete_confirm',
            'data-progress-indicator' => 'igniter::admin.text_deleting',
            'context' => ['edit'],
        ],
    ],
];

$config['form']['fields'] = [
    'name' => [
        'label' => 'lang:cupnoodles.taxclasses::default.name',
        'type' => 'text',
        'span' => 'left',
        'comment' => 'lang:cupnoodles.taxclasses::default.help_name',
    ],
    'rate' => [
        'label' => 'lang:cupnoodles.taxclasses::default.rate',
        // Not 'number': core's form widget casts number fields to integers
        'type' => 'text',
        'span' => 'right',
        'attributes' => ['inputmode' => 'decimal'],
        'comment' => 'lang:cupnoodles.taxclasses::default.help_rate',
    ],
    'label' => [
        'label' => 'lang:cupnoodles.taxclasses::default.label',
        'type' => 'text',
        'span' => 'left',
        'comment' => 'lang:cupnoodles.taxclasses::default.help_label',
    ],
    'description' => [
        'label' => 'lang:cupnoodles.taxclasses::default.description',
        'type' => 'text',
        'span' => 'right',
        'comment' => 'lang:cupnoodles.taxclasses::default.help_description',
    ],
    'include_in_price' => [
        'label' => 'lang:cupnoodles.taxclasses::default.include_in_price',
        'type' => 'switch',
        'span' => 'left',
        'comment' => 'lang:cupnoodles.taxclasses::default.help_include_in_price',
    ],
    'limit_to_order_type' => [
        'label' => 'lang:cupnoodles.taxclasses::default.limit_to_order_type',
        'type' => 'checkboxtoggle',
        'span' => 'right',
        'options' => [TaxClass::class, 'getOrderTypeOptions'],
        'comment' => 'lang:cupnoodles.taxclasses::default.help_limit_to_order_type',
    ],
    'tax_delivery' => [
        'label' => 'lang:cupnoodles.taxclasses::default.tax_delivery',
        'type' => 'switch',
        'span' => 'left',
    ],
    'tax_payment_fee' => [
        'label' => 'lang:cupnoodles.taxclasses::default.tax_payment_fee',
        'type' => 'switch',
        'span' => 'right',
    ],
];

return $config;
