<?php

declare(strict_types=1);

namespace CupNoodles\TaxClasses\Http\Controllers;

use CupNoodles\TaxClasses\Http\Requests\TaxClassRequest;
use CupNoodles\TaxClasses\Models\TaxClass;
use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Admin\Http\Actions\FormController;
use Igniter\Admin\Http\Actions\ListController;

class TaxClasses extends AdminController
{
    public array $implement = [
        ListController::class,
        FormController::class,
    ];

    public array $listConfig = [
        'list' => [
            'model' => TaxClass::class,
            'title' => 'lang:cupnoodles.taxclasses::default.text_title',
            'emptyMessage' => 'lang:cupnoodles.taxclasses::default.text_empty',
            'defaultSort' => ['name', 'ASC'],
            'configFile' => 'taxclass',
        ],
    ];

    public array $formConfig = [
        'name' => 'lang:cupnoodles.taxclasses::default.text_form_name',
        'model' => TaxClass::class,
        'request' => TaxClassRequest::class,
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'cupnoodles/taxclasses/tax_classes/edit/{tax_class_id}',
            'redirectClose' => 'cupnoodles/taxclasses/tax_classes',
            'redirectNew' => 'cupnoodles/taxclasses/tax_classes/create',
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'cupnoodles/taxclasses/tax_classes/edit/{tax_class_id}',
            'redirectClose' => 'cupnoodles/taxclasses/tax_classes',
            'redirectNew' => 'cupnoodles/taxclasses/tax_classes/create',
        ],
        'delete' => [
            'redirect' => 'cupnoodles/taxclasses/tax_classes',
        ],
        'configFile' => 'taxclass',
    ];

    protected null|string|array $requiredPermissions = 'CupNoodles.TaxClasses.Manage';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('taxclasses', 'restaurant');
    }

    public function listExtendQuery($query): void
    {
        $query->withCount(['menus', 'categories']);
    }
}
