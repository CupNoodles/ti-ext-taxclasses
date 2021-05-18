<?php

namespace CupNoodles\TaxClasses\Models;

use Model;

/**
 * UOM Model Class
 */
class TaxClasses extends Model
{
    /**
     * @var string The database table name
     */
    protected $table = 'tax_classes';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'tax_class_id';

    public $relation = [];

    public static function getDropdownOptions()
    {
        return static::allClasses()->dropdown('name');
    }


    public function scopeAllClasses($query)
    {
        return $query->all();
    }

    public static function getTaxRateForMenuId($menu_id){
        return self::getTaxRateForMenu($menu_id);

    }

    public function scopeGetTaxRateForMenu($query, $menu_id){
        return $query
        ->join('menus', 'tax_classes.tax_class_id', '=', 'menus.tax_class_id')
        ->where('menu_id', $menu_id)
        ->first();
    } 
}
