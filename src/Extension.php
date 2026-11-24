<?php

declare(strict_types=1);

namespace CupNoodles\TaxClasses;

use CupNoodles\TaxClasses\CartConditions\ClassTax;
use CupNoodles\TaxClasses\Classes\TaxCartCalculator;
use CupNoodles\TaxClasses\Models\TaxClass;
use Igniter\Admin\Widgets\Form;
use Igniter\Cart\Http\Controllers\Categories;
use Igniter\Cart\Http\Controllers\Menus;
use Igniter\Cart\Http\Requests\CategoryRequest;
use Igniter\Cart\Http\Requests\MenuRequest;
use Igniter\Cart\Models\Category;
use Igniter\Cart\Models\Menu;
use Igniter\System\Classes\BaseExtension;
use Igniter\System\Classes\FormRequest;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Override;
use Throwable;

class Extension extends BaseExtension
{
    #[Override]
    public function boot(): void
    {
        Menu::extend(function(Menu $model): void {
            $model->relation['belongsToMany']['tax_classes'] = [
                TaxClass::class, 'table' => 'menu_tax_classes', 'foreignKey' => 'menu_id', 'otherKey' => 'tax_class_id',
            ];
        });

        Category::extend(function(Category $model): void {
            $model->relation['belongsToMany']['tax_classes'] = [
                TaxClass::class, 'table' => 'category_tax_classes', 'foreignKey' => 'category_id', 'otherKey' => 'tax_class_id',
            ];
        });

        // Tax classes, assignments and order types can change between cart calculations in one
        // request (eg. tests, queue workers)
        TaxClass::saved(fn() => TaxCartCalculator::clearCache());
        TaxClass::deleted(fn() => TaxCartCalculator::clearCache());

        $this->extendMenuForm();
        $this->extendCategoryForm();
        $this->extendValidation();
    }

    /**
     * One cart condition (one line on the order totals) per tax class. Cart conditions are
     * registered by class name, so each class gets an alias of ClassTax.
     */
    public function registerCartConditions(): array
    {
        try {
            if (!Schema::hasTable('tax_classes')) {
                return [];
            }

            $taxClasses = TaxCartCalculator::taxClasses();
        } catch (Throwable) {
            return []; // no database yet, eg. during installation
        }

        $conditions = [];
        foreach ($taxClasses->values() as $index => $taxClass) {
            $alias = ClassTax::class.'_'.$taxClass->getKey();
            if (!class_exists($alias, false)) {
                class_alias(ClassTax::class, $alias);
            }

            $conditions[$alias] = [
                'name' => 'taxclass_'.$taxClass->getKey(),
                'label' => $taxClass->customerLabel(),
                'description' => sprintf(lang('cupnoodles.taxclasses::default.help_condition'), $taxClass->name),
                'tax_class_id' => $taxClass->getKey(),
                'priority' => 700 + $index,
            ];
        }

        return $conditions;
    }

    #[Override]
    public function registerNavigation(): array
    {
        return [
            'restaurant' => [
                'child' => [
                    'taxclasses' => [
                        'priority' => 60,
                        'class' => 'taxclasses',
                        'href' => admin_url('cupnoodles/taxclasses/tax_classes'),
                        'title' => lang('cupnoodles.taxclasses::default.side_menu'),
                        'permission' => 'CupNoodles.TaxClasses.Manage',
                    ],
                ],
            ],
        ];
    }

    #[Override]
    public function registerPermissions(): array
    {
        return [
            'CupNoodles.TaxClasses.Manage' => [
                'description' => 'lang:cupnoodles.taxclasses::default.permission_manage',
                'group' => 'igniter::admin.permissions.name',
            ],
        ];
    }

    protected function extendMenuForm(): void
    {
        Menus::extendFormFields(function(Form $form, $model): void {
            if (!$model instanceof Menu || TaxClass::query()->doesntExist()) {
                return;
            }

            $form->addTabFields([
                'tax_classes' => static::taxClassField(
                    'lang:cupnoodles.taxclasses::default.label_tax_classes',
                    static::menuFieldComment($model),
                ),
            ]);

            static::setFieldValue($form, $model);
        });
    }

    protected function extendCategoryForm(): void
    {
        Categories::extendFormFields(function(Form $form, $model): void {
            if (!$model instanceof Category || TaxClass::query()->doesntExist()) {
                return;
            }

            $form->addFields([
                'tax_classes' => static::taxClassField(
                    'lang:cupnoodles.taxclasses::default.label_tax_classes',
                    lang('cupnoodles.taxclasses::default.help_category_tax_classes'),
                ),
            ]);

            static::setFieldValue($form, $model);
        });
    }

    /**
     * Menu and category forms only save the fields their requests validate.
     */
    protected function extendValidation(): void
    {
        Event::listen('system.formRequest.extendValidator', function(FormRequest $request, object $dataHolder): void {
            if (!$request instanceof MenuRequest && !$request instanceof CategoryRequest) {
                return;
            }

            $dataHolder->rules['tax_classes'] = ['nullable', 'array'];
            $dataHolder->rules['tax_classes.*'] = ['integer', 'exists:tax_classes,tax_class_id'];
            $dataHolder->attributes['tax_classes'] = lang('cupnoodles.taxclasses::default.label_tax_classes');
            $dataHolder->attributes['tax_classes.*'] = lang('cupnoodles.taxclasses::default.label_tax_classes');
        });
    }

    protected static function taxClassField(string $label, string $comment): array
    {
        return [
            'label' => $label,
            // Checkboxes rather than a select: a select's open list is drawn under the fields below it
            'type' => 'checkboxlist',
            'options' => [TaxClass::class, 'getDropdownOptions'],
            'span' => 'left',
            'comment' => $comment,
        ];
    }

    /** The checkbox list needs IDs, not the related models */
    protected static function setFieldValue(Form $form, Menu|Category $model): void
    {
        if ($field = $form->getField('tax_classes')) {
            $field->value = $model->exists ? $model->tax_classes->pluck('tax_class_id')->map('intval')->all() : [];
        }
    }

    protected static function menuFieldComment(Menu $menu): string
    {
        $comment = lang('cupnoodles.taxclasses::default.help_menu_tax_classes');
        if (!$menu->exists) {
            return $comment;
        }

        $fromCategories = $menu->categories()->with('tax_classes')->get()
            ->flatMap(fn(Category $category) => $category->tax_classes)
            ->unique('tax_class_id')
            ->map(fn(TaxClass $class): string => $class->name.' '.$class->formattedRate());

        return $comment.' '.($fromCategories->isEmpty()
            ? lang('cupnoodles.taxclasses::default.help_menu_no_category_classes')
            : sprintf(lang('cupnoodles.taxclasses::default.help_menu_category_classes'), e($fromCategories->implode(', '))));
    }
}
