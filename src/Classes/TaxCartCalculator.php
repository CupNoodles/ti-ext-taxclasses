<?php

declare(strict_types=1);

namespace CupNoodles\TaxClasses\Classes;

use CupNoodles\TaxClasses\Models\TaxClass;
use Igniter\Cart\Cart;
use Igniter\Cart\CartCondition;
use Igniter\Local\Facades\Location;
use Igniter\System\Models\Currency;
use Illuminate\Support\Collection;

/**
 * Feeds the cart into TaxCalculator: its items, and the discounts and charges applied before the
 * tax lines (Cart Settings > Cart Conditions order).
 */
class TaxCartCalculator
{
    /** @var Collection<int, TaxClass>|null for this request */
    protected static ?Collection $taxClasses = null;

    /**
     * @return array<int, float> Tax per class ID for the classes that apply to the current order type
     */
    public static function amounts(Cart $cart, int $beforePriority): array
    {
        $classes = static::taxClasses()
            ->filter(fn(TaxClass $class): bool => $class->appliesToOrderType(Location::orderType()))
            ->mapWithKeys(fn(TaxClass $class): array => [$class->getKey() => $class->toCalculatorConfig()])
            ->all();

        if ($classes === []) {
            return [];
        }

        $items = $cart->content()->all();
        $assignments = TaxAssignments::forMenus(array_map(fn($item) => $item->id, $items));

        $lines = [];
        foreach ($items as $item) {
            $lines[] = [
                // Includes item-level discounts (eg. coupons limited to some items)
                'amount' => (float)$item->subtotal(),
                'classes' => $assignments[(int)$item->id] ?? [],
            ];
        }

        [$discount, $delivery, $paymentFee] = static::chargesBefore($cart, $beforePriority);

        return (new TaxCalculator($classes, static::precision()))
            ->calculate($lines, $discount, $delivery, $paymentFee);
    }

    /**
     * Discounts and charges from the conditions applied before the tax lines.
     *
     * @return array{float, float, float} [cart-wide discount, delivery charge, payment fee]
     */
    protected static function chargesBefore(Cart $cart, int $beforePriority): array
    {
        $discount = $delivery = $deliveryDiscount = $paymentFee = 0.0;

        /** @var CartCondition $condition */
        foreach ($cart->conditionsWithoutApplied()->sorted() as $condition) {
            if ($condition->getPriority() >= $beforePriority || !$condition->isValid() || $condition->isInclusive()) {
                continue;
            }

            $value = $condition->getValue();
            $value = is_numeric($value) ? (float)$value : 0.0; // delivery says "Free"

            if ($condition->name === 'delivery') {
                $delivery += $value;
            } elseif ($condition->name === 'paymentFee') {
                $paymentFee += $value;
            } elseif (static::isDiscount($condition)) {
                $value = abs($value); // Coupon reports its value as negative
                if (method_exists($condition, 'getModel') && $condition->getModel()?->appliesOnDelivery()) {
                    $deliveryDiscount += $value;
                } else {
                    $discount += $value;
                }
            }
            // Anything else (tips, other taxes) isn't taxed
        }

        return [$discount, max(0.0, $delivery - $deliveryDiscount), $paymentFee];
    }

    protected static function isDiscount(CartCondition $condition): bool
    {
        foreach ($condition->getActions() as $action) {
            if (str_starts_with(trim((string)array_get($action, 'value', '')), '-')) {
                return true;
            }
        }

        return false;
    }

    /** @return Collection<int, TaxClass> */
    public static function taxClasses(): Collection
    {
        return static::$taxClasses ??= TaxClass::query()->orderBy('tax_class_id')->get()->keyBy('tax_class_id');
    }

    public static function precision(): int
    {
        return (int)(optional(Currency::getDefault())->decimal_position ?? 2);
    }

    public static function clearCache(): void
    {
        static::$taxClasses = null;
        TaxAssignments::clearCache();
    }
}
