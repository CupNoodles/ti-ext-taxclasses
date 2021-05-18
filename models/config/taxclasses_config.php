<?php


$config['list']['toolbar'] = [
    'buttons' => [
        'create' => [
            'label' => 'lang:admin::lang.button_new',
            'class' => 'btn btn-primary',
            'href' => admin_url('cupnoodles/taxclasses/taxclasses/create'),
        ],
        'delete' => [
            'label' => 'lang:admin::lang.button_delete',
            'class' => 'btn btn-danger',
            'data-attach-loading' => '',
            'data-request' => 'onDelete',
            'data-request-form' => '#list-form',
            'data-request-data' => "_method:'DELETE'",
            'data-request-confirm' => 'lang:admin::lang.alert_warning_confirm',
        ],
    ],
];

$config['list']['columns'] = [
    'edit' => [
        'type' => 'button',
        'iconCssClass' => 'fa fa-pencil',
        'attributes' => [
            'class' => 'btn btn-edit',
            'href' => admin_url('cupnoodles/taxclasses/taxclasses/edit/{tax_class_id}'),
        ],
    ],
    'name' => [
        'label' => 'lang:cupnoodles.taxclasses::default.name',
        'type' => 'text'
    ],
    'rate' => [
        'label' => 'lang:cupnoodles.taxclasses::default.rate',
        'type' => 'text'
    ],
    'tax_class_id' => [
        'label' => 'lang:admin::lang.column_id',
        'invisible' => TRUE,
    ],

];

$config['form']['toolbar'] = [
    'buttons' => [
        'save' => [
            'label' => 'lang:admin::lang.button_save',
            'class' => 'btn btn-primary',
            'data-request' => 'onSave',
            'data-progress-indicator' => 'admin::lang.text_saving',
        ],
        'saveClose' => [
            'label' => 'lang:admin::lang.button_save_close',
            'class' => 'btn btn-default',
            'data-request' => 'onSave',
            'data-request-data' => 'close:1',
            'data-progress-indicator' => 'admin::lang.text_saving',
        ],
        'delete' => [
            'label' => 'lang:admin::lang.button_icon_delete',
            'class' => 'btn btn-danger',
            'data-request' => 'onDelete',
            'data-request-data' => "_method:'DELETE'",
            'data-request-confirm' => 'lang:admin::lang.alert_warning_confirm',
            'data-progress-indicator' => 'admin::lang.text_deleting',
            'context' => ['edit'],
        ],
    ],
];

$config['form']['tabs'] = [
    'defaultTab' => 'lang:cupnoodles.taxclasses::default.text_tab_general',
    'fields' => [
        'name' => [
            'label' => 'lang:cupnoodles.taxclasses::default.name',
            'type' => 'text',
            'span' => 'left',
        ],
        'rate' => [
            'label' => 'lang:cupnoodles.taxclasses::default.rate',
            'type' => 'number',
            'span' => 'right',
        ],
        'apply_to_delivery' => [
            'label' => 'lang:cupnoodles.taxclasses::default.apply_to_delivery',
            'type' => 'switch'
        ]
    ],
];

return $config;
