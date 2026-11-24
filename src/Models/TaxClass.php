<?php

declare(strict_types=1);

namespace CupNoodles\TaxClasses\Models;

use Igniter\Cart\Classes\OrderTypes;
use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Menu;
use Igniter\Flame\Database\Model;

/**
 * A tax rate menu items and categories can belong to, eg. "Sales Tax 8.875%".
 *
 * @property int $tax_class_id
 * @property string $name Admin name
 * @property string|null $label Shown to customers on the order totals
 * @property string|null $description
 * @property float $rate Percentage, eg. 8.875
 * @property bool $include_in_price Prices already include this tax
 * @property array|null $limit_to_order_type Order type codes this tax applies to; empty for all
 * @property bool $tax_delivery Also tax the delivery charge
 * @property bool $tax_payment_fee Also tax payment fees
 */
class TaxClass extends Model
{
    protected $table = 'tax_classes';

    protected $primaryKey = 'tax_class_id';

    protected $fillable = [
        'name', 'label', 'description', 'rate', 'include_in_price', 'limit_to_order_type',
        'tax_delivery', 'tax_payment_fee',
    ];

    protected $casts = [
        'rate' => 'float',
        'include_in_price' => 'boolean',
        'limit_to_order_type' => 'array',
        'tax_delivery' => 'boolean',
        'tax_payment_fee' => 'boolean',
    ];

    public $relation = [
        'belongsToMany' => [
            'menus' => [Menu::class, 'table' => 'menu_tax_classes', 'foreignKey' => 'tax_class_id', 'otherKey' => 'menu_id'],
            'categories' => [Category::class, 'table' => 'category_tax_classes', 'foreignKey' => 'tax_class_id', 'otherKey' => 'category_id'],
        ],
    ];

    public static function getDropdownOptions(): array
    {
        return static::query()->orderBy('name')->get()
            ->mapWithKeys(fn(self $class): array => [$class->getKey() => $class->name.' ('.$class->formattedRate().')'])
            ->all();
    }

    public static function getOrderTypeOptions(): array
    {
        return collect(resolve(OrderTypes::class)->listOrderTypes())->pluck('name', 'code')->all();
    }

    /** "8.875%", without trailing zeros */
    public function formattedRate(): string
    {
        return rtrim(rtrim(number_format($this->rate, 4, '.', ''), '0'), '.').'%';
    }

    /** The line customers see, eg. "Sales Tax 8.875%" or "VAT 20% (included)" */
    public function customerLabel(): string
    {
        $label = $this->label ?: $this->name.' '.$this->formattedRate();

        return $this->include_in_price
            ? sprintf(lang('cupnoodles.taxclasses::default.text_included'), $label)
            : $label;
    }

    public function appliesToOrderType(?string $orderType): bool
    {
        $orderTypes = array_filter((array)$this->limit_to_order_type);

        return $orderTypes === [] || in_array($orderType, $orderTypes, true);
    }

    /** Config for TaxCalculator */
    public function toCalculatorConfig(): array
    {
        return [
            'rate' => $this->rate,
            'inclusive' => $this->include_in_price,
            'delivery' => $this->tax_delivery,
            'payment_fee' => $this->tax_payment_fee,
        ];
    }
}
