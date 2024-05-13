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
            $model->relation['belongsToMany']['tax_classes'] = ['CupNoodles\TaxClasses\Models\TaxClasses', 'table' => 'menu_tax_classes'];            
        });

        Event::listen('admin.form.extendFieldsBefore', function (Form $form) {

            if($form->model instanceof Menus_model){
                
                $tax_classes = ['tax_classes' => [
                    'label' => 'lang:cupnoodles.taxclasses::default.label_tax_classes',
                    'type' => 'relation',
                    'span' => 'right'
                ]];

                $form->tabs['fields'] = $this->array_insert_after($form->tabs['fields'], 'menu_priority', $tax_classes);
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
        $cartConditions = [];
        $taxClasses =  \CupNoodles\TaxClasses\Models\TaxClasses::all();

        $tax_classes = [];
        foreach($taxClasses as $taxClass){
            // leaving a documentation link here since class_alias() is not a very well-known PHP function
            // https://www.php.net/manual/en/function.class-alias.php
            $dynamic_class_name = \CupNoodles\TaxClasses\CartConditions\VariableTax::class . '_' . $taxClass->tax_class_id;
            class_alias(\CupNoodles\TaxClasses\CartConditions\VariableTax::class, $dynamic_class_name);
            $tax_classes[$dynamic_class_name] = [
                'name' => $taxClass->name,
                'label' => $taxClass->label,
                'description' => $taxClass->description,
                'tax_class_id' => $taxClass->tax_class_id,
                'include_in_price' => $taxClass->include_in_price,
                'limit_to_order_type' => $taxClass->limit_to_order_type
            ];
        }

        return $tax_classes;
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
