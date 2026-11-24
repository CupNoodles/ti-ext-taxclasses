<?php

declare(strict_types=1);

namespace CupNoodles\TaxClasses\CartConditions;

use CupNoodles\TaxClasses\Classes\TaxCartCalculator;
use CupNoodles\TaxClasses\Models\TaxClass;
use Igniter\Cart\CartCondition;
use Override;

/**
 * One tax line for one tax class. Registered once per class under an alias of this class (see
 * Extension::registerCartConditions), because cart conditions are registered by class name.
 */
class ClassTax extends CartCondition
{
    /** After delivery, tips, coupons and payment fees */
    public ?int $priority = 700;

    protected float $amount = 0;

    #[Override]
    public function beforeApply(): ?bool
    {
        $taxClass = $this->taxClass();
        if (!$taxClass) {
            return false;
        }

        $this->amount = TaxCartCalculator::amounts(resolve('cart'), (int)$this->getPriority())[$taxClass->getKey()] ?? 0.0;

        // No line when there's nothing to charge, eg. no items in this class
        return $this->amount > 0 ? null : false;
    }

    #[Override]
    public function getActions(): array
    {
        return [
            [
                'value' => '+'.$this->amount,
                'inclusive' => (bool)$this->taxClass()?->include_in_price,
                'valuePrecision' => TaxCartCalculator::precision(),
            ],
        ];
    }

    #[Override]
    public function getLabel(): string
    {
        return $this->taxClass()?->customerLabel() ?? (string)parent::getLabel();
    }

    public function taxClass(): ?TaxClass
    {
        return TaxCartCalculator::taxClasses()->get((int)array_get($this->config, 'tax_class_id'));
    }
}
