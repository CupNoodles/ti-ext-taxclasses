<?php

declare(strict_types=1);

use CupNoodles\TaxClasses\Classes\TaxCartCalculator;
use CupNoodles\TaxClasses\Models\TaxClass;
use SamPoyigi\Testbench\TestCase;

uses(TestCase::class)->in(__DIR__);

// Each test gets a fresh event dispatcher, but Flame remembers which models already had their
// hooks attached. Reset that so they bind to the new one, and forget cached tax classes.
uses()->beforeEach(function(): void {
    TaxClass::flushEventListeners();
    TaxCartCalculator::clearCache();
})->in(__DIR__);
