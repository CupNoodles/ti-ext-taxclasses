<?php

declare(strict_types=1);

namespace CupNoodles\TaxClasses\Classes;

/**
 * Works out how much each tax class charges on an order. Pure arithmetic, no database or cart
 * access, so it can be tested on its own (see TaxCartCalculator for the cart side).
 *
 * Rules:
 * - Each line (cart item) is taxed by the classes it belongs to.
 * - A cart-wide discount is spread over the lines in proportion to their amounts, so tax is charged
 *   on what the customer actually pays.
 * - Inclusive classes are already in the price. If a line has several, they're taken out together:
 *   a 100.00 item with inclusive 5% and 10% contains 100 * 5 / 115 and 100 * 10 / 115.
 * - Exclusive classes are added on top of the price without its inclusive taxes.
 * - The delivery charge and payment fee are taxed only by classes that opt in.
 * - Amounts are rounded once per class, at the end.
 */
final class TaxCalculator
{
    /**
     * @param array<int, array{rate: float|int|string, inclusive?: bool, delivery?: bool, payment_fee?: bool}> $classes
     *                                                                                                              keyed by tax class ID
     */
    public function __construct(
        protected array $classes,
        protected int $precision = 2,
    ) {}

    /**
     * @param array<int, array{amount: float|int, classes: int[]}> $lines
     * @param float $discount Cart-wide discount, as a positive amount
     * @param float $delivery Delivery charge, after any delivery discount
     * @param float $paymentFee Payment fee
     * @return array<int, float> Tax per class ID, rounded; classes with nothing to charge are 0.0
     */
    public function calculate(array $lines, float $discount = 0, float $delivery = 0, float $paymentFee = 0): array
    {
        $totals = array_fill_keys(array_keys($this->classes), 0.0);

        $linesTotal = array_sum(array_map(fn(array $line): float => max(0.0, (float)$line['amount']), $lines));
        $discountRatio = $linesTotal > 0 ? min(max($discount, 0), $linesTotal) / $linesTotal : 0.0;

        foreach ($lines as $line) {
            $base = max(0.0, (float)$line['amount']) * (1 - $discountRatio);
            $this->taxAmount($base, $line['classes'], $totals);
        }

        if ($delivery > 0) {
            $this->taxAmount($delivery, $this->classesWhere('delivery'), $totals);
        }

        if ($paymentFee > 0) {
            $this->taxAmount($paymentFee, $this->classesWhere('payment_fee'), $totals);
        }

        return array_map(fn(float $amount): float => round($amount, $this->precision), $totals);
    }

    /**
     * Adds the tax on $amount for each of $classIds to $totals (unrounded).
     */
    protected function taxAmount(float $amount, array $classIds, array &$totals): void
    {
        $classIds = array_values(array_unique(array_filter($classIds, fn($id): bool => isset($this->classes[$id]))));
        if ($amount <= 0 || $classIds === []) {
            return;
        }

        $inclusiveRates = 0.0;
        foreach ($classIds as $id) {
            if ($this->isInclusive($id)) {
                $inclusiveRates += $this->rate($id);
            }
        }

        // The price without its inclusive taxes
        $net = $amount * 100 / (100 + $inclusiveRates);

        foreach ($classIds as $id) {
            // Inclusive: the part of the price that is this tax. Exclusive: added on top of the net price.
            $totals[$id] += $net * $this->rate($id) / 100;
        }
    }

    protected function classesWhere(string $flag): array
    {
        return array_keys(array_filter($this->classes, fn(array $class): bool => !empty($class[$flag])));
    }

    protected function rate(int $id): float
    {
        return max(0.0, (float)$this->classes[$id]['rate']);
    }

    protected function isInclusive(int $id): bool
    {
        return !empty($this->classes[$id]['inclusive']);
    }
}
