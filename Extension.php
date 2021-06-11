<?php 

namespace CupNoodles\TaxClasses;

use System\Classes\BaseExtension;
use System\Classes\ExtensionManager;

// Admin-UI
use Event;
use Admin\Models\Menus_model;

use Admin\Widgets\Form;
use Admin\Classes\AdminController;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

/**
 * Butcher Extension Information File
 */
class Extension extends BaseExtension
{
    /**
     * Returns information about this extension.
     *
     * @return array
     */
    public function extensionMeta()
    {
        return [
            'name'        => 'Tax Classes',
            'author'      => 'CupNoodles',
            'description' => 'Allow for different Tax classes per item',
            'icon'        => 'fas fa-percentage',
            'version'     => '1.0.0'
        ];
    }

    /**
     * Register method, called when the extension is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
        Menus_Model::extend(function ($model) {
            $model->relation['belongsTo']['tax_classes'] = ['CupNoodles\TaxClasses\Models\TaxClasses', 'foreignKey' => 'tax_class_id'];
            
        });

        Event::listen('admin.form.extendFieldsBefore', function (Form $form) {

            if($form->model instanceof Menus_model){
                
                $tax_class_id = ['tax_class_id' => [
                    'label' => 'lang:cupnoodles.taxclasses::default.label_tax_classes',
                    'type' => 'relation',
                    'span' => 'right',
                    'relationFrom' => 'tax_classes',
                    'nameFrom' => 'name',
                    'valueFrom' => 'tax_class_id',

                ]];
                $form->tabs['fields'] = $this->array_insert_after($form->tabs['fields'], 'menu_priority', $tax_class_id);
            }

        });

    }

    function array_insert_after( array $array, $key, array $new ) {
        $keys = array_keys( $array );
        $index = array_search( $key, $keys );
        $pos = false === $index ? count( $array ) : $index + 1;
    
        return array_merge( array_slice( $array, 0, $pos ), $new, array_slice( $array, $pos ) );
    }


    /**
     * Registers any front-end components implemented in this extension.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
        ];
    }

    /**
     * Registers any admin permissions used by this extension.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'Admin.TaxClasses' => [
                'label' => 'cupnoodles.taxclasses::default.permissions',
                'group' => 'admin::lang.permissions.name',
            ],
        ];
    }

    public function registerCartConditions()
    {
        return [
            \CupNoodles\TaxClasses\CartConditions\VariableTax::class => [
                'name' => 'variableTax',
                'label' => 'lang:cupnoodles.taxclasses::default.variable_sales_tax_label',
                'description' => 'lang:igniter.coupons::default.variable_sales_tax_description',
            ],
        ];
    }


    public function registerNavigation()
    {
        return [
            'localisation' => [
                'child' => [
                    'taxclasses' => [
                        'priority' => 90,
                        'class' => 'TaxClasses',
                        'href' => admin_url('cupnoodles/taxclasses/taxclasses'),
                        'title' => lang('cupnoodles.taxclasses::default.side_menu'),
                        'permission' => 'Admin.TaxClasses',
                    ],
                ],
            ],
        ];
    }
}
