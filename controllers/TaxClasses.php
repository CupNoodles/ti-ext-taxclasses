<?php

namespace CupNoodles\TaxClasses\Controllers;

use AdminMenu;

class TaxClasses extends \Admin\Classes\AdminController
{
    public $implement = [
        'Admin\Actions\ListController',
        'Admin\Actions\FormController'
    ];

    public $listConfig = [
        'list' => [
            'model' => 'CupNoodles\TaxClasses\Models\TaxClasses',
            'title' => 'cupnoodles.taxclasses::default.text_title',
            'emptyMessage' => 'cupnoodles.taxclasses::default.text_empty',
            'defaultSort' => ['tax_class_id', 'DESC'],
            'configFile' => 'taxclasses_config',
        ],
    ];

    public $formConfig = [
        'name' => 'cupnoodles.taxclasses::default.text_form_name',
        'model' => 'CupNoodles\TaxClasses\Models\TaxClasses',
        'request' => 'CupNoodles\TaxClasses\Requests\TaxClasses',
        'create' => [
            'title' => 'lang:admin::lang.form.create_title',
            'redirect' => 'cupnoodles/taxclasses/taxclasses/edit/{tax_class_id}',
            'redirectClose' => 'cupnoodles/taxclasses/taxclasses',
        ],
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'cupnoodles/taxclasses/taxclasses/edit/{tax_class_id}',
            'redirectClose' => 'cupnoodles/taxclasses/taxclasses',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'redirect' => 'cupnoodles/taxclasses/taxclasses',
        ],
        'delete' => [
            'redirect' => 'cupnoodles/taxclasses/taxclasses',
        ],
        'configFile' => 'taxclasses_config',
    ];

}
