<?php

namespace CupNoodles\TaxClasses\CartConditions;

use Igniter\Flame\Cart\CartCondition;
use Igniter\Local\Facades\Location;

use CupNoodles\TaxClasses\Models\TaxClasses;
use Igniter\Flame\Cart\Cart;
use Igniter\Cart\Classes\CartManager;
use Admin\Models\Menus_model;

class VariableTax extends CartCondition
{

    public $priority = 300;

    protected $taxAmount;

    public function getLabel()
    {
        return 'Tax';
    }

    public function getActions()
    {
        $this->taxAmount = 0;

        $tax_rates = TaxClasses::all();
        $cart = CartManager::instance()->getCart();

        

        foreach($cart->content() as $ix=>$menu){
            
            if($menu->model->tax_class_id && isset($menu->model->tax_classes->rate)){
                $this->taxAmount += $menu->subtotal() * ($menu->model->tax_classes->rate / 100);
            }
            
        }

        $cart_subtotal = $cart->subtotal();
        foreach($tax_rates as $ix=>$tax_rate){

            if($tax_rate->apply_to_delivery){
                $deliveryCharge = Location::coveredArea()->deliveryAmount($cart_subtotal);
                $this->taxAmount += $deliveryCharge * ($tax_rate->rate / 100);
            }

        }

        return [
            [
                'value' => "+{$this->taxAmount}"
            ],
        ];
    }


}
