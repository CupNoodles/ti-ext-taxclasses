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

    protected $tax_class_id;

    public function __construct($config = [])
    {
        parent::__construct();
        $this->tax_class_id = $config['tax_class_id'];
        $this->name = 'VariableTax_' . $config['tax_class_id'];
        $this->label = $config['label'];
    }

    public function getLabel(){
        return $this->label;
    }


    public function getActions()
    {
        $this->taxAmount = 0;

        $tax_class = TaxClasses::where('tax_class_id', $this->tax_class_id)->first();

        $cart = CartManager::instance()->getCart();

        foreach($cart->content() as $ix=>$menu){

            $has_class = $menu->model::has('tax_classes', $this->tax_class_id)->count();

            if($has_class){
                $this->taxAmount += $menu->subtotal() * ($tax_class->rate / 100);
            }
            
        }

        return [
            [
                'value' => "+{$this->taxAmount}"
            ],
        ];
    }


}
