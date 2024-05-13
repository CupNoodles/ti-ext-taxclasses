<?php

namespace CupNoodles\TaxClasses\CartConditions;

use Igniter\Flame\Cart\CartCondition;
use Igniter\Local\Facades\Location;

use CupNoodles\TaxClasses\Models\TaxClasses;
use Igniter\Flame\Cart\Cart;
use Igniter\Cart\Classes\CartManager;
use Igniter\Cart\Classes\CartConditionManager;
use Admin\Models\Menus_model;
use Illuminate\Support\Facades\App;

class VariableTax extends CartCondition
{

    protected $taxAmount;

    protected $tax_class_id;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->tax_class_id = $config['tax_class_id'];
        $this->name = 'VariableTax_' . $config['tax_class_id'];
        $this->label = $config['label'];
        $this->include_in_price = $config['include_in_price'];
        $this->limit_to_order_type = $config['limit_to_order_type'];
    }

    public function getLabel(){
        return $this->label;
    }


    public function getActions()
    {
        $this->taxAmount = 0;



        $tax_class = TaxClasses::where('tax_class_id', $this->tax_class_id)->first();

        // apply percentage to cart contents
        $cart = CartManager::instance()->getCart();


        if(is_array($this->limit_to_order_type) && count($this->limit_to_order_type)){
            $location = App::make('location');
            if($location->orderTypeIsDelivery() && !in_array("delivery", $this->limit_to_order_type)
            || $location->orderTypeIsCollection() && !in_array("collection",$this->limit_to_order_type)
            ){
                return [
                    [
                        'value' => "+{$this->taxAmount}",
                        "inclusive" => $this->include_in_price
                    ],
                ];
            }
        }





        foreach($cart->content() as $ix=>$menu){
            $has_class = $menu->getModel()::where('menu_id', $menu->id)->whereRelation('tax_classes', 'tax_classes.tax_class_id', $this->tax_class_id)->count();
            if($has_class){
                $this->taxAmount += $menu->subtotal() * ($tax_class->rate / 100);
            }
            
        }

        // if apply to previous is set, apply to previous subtotals

        if($tax_class->apply_to_previous_fees){
            // nb: don't use $cart->conditions() here as it will call getActions() on each condition resulting in infinite loop
            $conditionsManager = CartConditionManager::instance();

            foreach($conditionsManager->listRegisteredConditions() as $condition_name => $condition){

                $condition_obj = $cart->getCondition($condition['name']);

                if($condition_obj && $condition_obj->getValue() > 0 && $condition_obj->getPriority() < $this->priority){
                    $this->taxAmount += (float)$condition_obj->getValue() * ($tax_class->rate / 100);
                }

            }
        }

        return [
            [
                'value' => "+{$this->taxAmount}",
                "inclusive" => $this->include_in_price
            ],
        ];
    }


}
