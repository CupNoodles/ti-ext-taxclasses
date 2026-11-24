<?php

declare(strict_types=1);

namespace CupNoodles\TaxClasses\Tests\Classes;

use CupNoodles\TaxClasses\Classes\TaxCalculator;

// Classes used below
const SALES = 1;    // NYC sales tax, added on top
const GROCERY = 2;  // 0%
const VAT_LOW = 3;  // inclusive 5%
const VAT_HIGH = 4; // inclusive 20%
const CITY = 5;     // another tax added on top, 1%

function calculator(array $overrides = []): TaxCalculator
{
    $classes = [
        SALES => ['rate' => 8.875],
        GROCERY => ['rate' => 0],
        VAT_LOW => ['rate' => 5, 'inclusive' => true],
        VAT_HIGH => ['rate' => 20, 'inclusive' => true],
        CITY => ['rate' => 1],
    ];

    foreach ($overrides as $id => $config) {
        $classes[$id] = array_merge($classes[$id], $config);
    }

    return new TaxCalculator($classes);
}

it('charges a decimal rate on top of the items in the class', function(): void {
    $result = calculator()->calculate([
        ['amount' => 20.00, 'classes' => [SALES]],
        ['amount' => 10.00, 'classes' => [GROCERY]],
    ]);

    // 20 * 8.875% = 1.775
    expect($result[SALES])->toBe(1.78)
        ->and($result[GROCERY])->toBe(0.0)
        ->and($result[CITY])->toBe(0.0);
});

it('charges every class an item belongs to', function(): void {
    $result = calculator()->calculate([
        ['amount' => 100.00, 'classes' => [SALES, CITY]],
    ]);

    expect($result[SALES])->toBe(8.88)
        ->and($result[CITY])->toBe(1.0);
});

it('rounds once per class, not per item', function(): void {
    // Three items of 1.00 at 8.875%: 0.08875 each. Per item that would round to 0.09 * 3 = 0.27.
    $result = calculator()->calculate([
        ['amount' => 1.00, 'classes' => [SALES]],
        ['amount' => 1.00, 'classes' => [SALES]],
        ['amount' => 1.00, 'classes' => [SALES]],
    ]);

    expect($result[SALES])->toBe(0.27); // 0.26625

    $result = calculator()->calculate(array_fill(0, 10, ['amount' => 1.00, 'classes' => [SALES]]));
    expect($result[SALES])->toBe(0.89); // 0.8875, not 10 * 0.09 = 0.90
});

it('takes an inclusive tax out of the price', function(): void {
    $result = calculator()->calculate([
        ['amount' => 12.00, 'classes' => [VAT_HIGH]],
    ]);

    // 12.00 includes 20%: 12 - 12 / 1.2 = 2.00
    expect($result[VAT_HIGH])->toBe(2.0);
});

it('takes several inclusive taxes out of the price together', function(): void {
    $result = calculator()->calculate([
        ['amount' => 125.00, 'classes' => [VAT_LOW, VAT_HIGH]],
    ]);

    // Net 125 / 1.25 = 100, so 5.00 and 20.00
    expect($result[VAT_LOW])->toBe(5.0)
        ->and($result[VAT_HIGH])->toBe(20.0);
});

it('adds a tax on top of the price without its inclusive taxes', function(): void {
    $result = calculator()->calculate([
        ['amount' => 120.00, 'classes' => [VAT_HIGH, CITY]],
    ]);

    // Net 100: 20.00 included, 1% of 100 added
    expect($result[VAT_HIGH])->toBe(20.0)
        ->and($result[CITY])->toBe(1.0);
});

it('taxes what is left after a cart-wide discount, spread by item amount', function(): void {
    $result = calculator()->calculate([
        ['amount' => 30.00, 'classes' => [SALES]],
        ['amount' => 10.00, 'classes' => [GROCERY]],
    ], discount: 10.00);

    // 25% off everything: 22.50 taxable at 8.875% = 1.996875
    expect($result[SALES])->toBe(2.0);
});

it('never taxes below zero when the discount is bigger than the items', function(): void {
    $result = calculator()->calculate([
        ['amount' => 5.00, 'classes' => [SALES]],
    ], discount: 50.00);

    expect($result[SALES])->toBe(0.0);
});

it('taxes the delivery charge only for classes that opt in', function(): void {
    $result = calculator([SALES => ['delivery' => true]])->calculate([
        ['amount' => 20.00, 'classes' => [SALES]],
        ['amount' => 20.00, 'classes' => [CITY]],
    ], delivery: 5.00);

    // Sales: (20 + 5) * 8.875% = 2.21875. City: 20 * 1% only.
    expect($result[SALES])->toBe(2.22)
        ->and($result[CITY])->toBe(0.2);
});

it('taxes delivery even when no item is in the class', function(): void {
    $result = calculator([CITY => ['delivery' => true]])->calculate([
        ['amount' => 20.00, 'classes' => [SALES]],
    ], delivery: 4.00);

    expect($result[CITY])->toBe(0.04);
});

it('takes an inclusive tax out of the delivery charge', function(): void {
    $result = calculator([VAT_HIGH => ['delivery' => true]])->calculate([], delivery: 6.00);

    expect($result[VAT_HIGH])->toBe(1.0);
});

it('taxes payment fees only for classes that opt in', function(): void {
    $result = calculator([SALES => ['payment_fee' => true]])->calculate([
        ['amount' => 10.00, 'classes' => [SALES, CITY]],
    ], paymentFee: 2.00);

    // Sales: 12 * 8.875% = 1.065. City: 10 * 1%.
    expect($result[SALES])->toBe(1.07)
        ->and($result[CITY])->toBe(0.1);
});

it('ignores unknown classes and duplicate assignments', function(): void {
    $result = calculator()->calculate([
        ['amount' => 10.00, 'classes' => [SALES, SALES, 99]],
    ]);

    expect($result)->toHaveKeys([SALES, GROCERY, VAT_LOW, VAT_HIGH, CITY])
        ->and($result)->not->toHaveKey(99)
        ->and($result[SALES])->toBe(0.89);
});

it('charges nothing on an empty cart', function(): void {
    expect(array_sum(calculator()->calculate([])))->toBe(0.0);
});

it('rounds to the currency precision', function(): void {
    $result = (new TaxCalculator([SALES => ['rate' => 8.875]], 0))->calculate([
        ['amount' => 1000, 'classes' => [SALES]],
    ]);

    expect($result[SALES])->toBe(89.0);
});
