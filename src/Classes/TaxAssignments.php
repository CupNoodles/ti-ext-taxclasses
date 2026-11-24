<?php

declare(strict_types=1);

namespace CupNoodles\TaxClasses\Classes;

use Illuminate\Support\Facades\DB;

/**
 * Which tax classes apply to which menu items. An item's own classes win; an item without any
 * gets every class of every category it's in.
 */
class TaxAssignments
{
    /** @var array<int, int[]> menu ID => tax class IDs, for this request */
    protected static array $cache = [];

    /**
     * @param int[] $menuIds
     * @return array<int, int[]> menu ID => tax class IDs (possibly empty)
     */
    public static function forMenus(array $menuIds): array
    {
        $menuIds = array_values(array_unique(array_map('intval', $menuIds)));
        $missing = array_values(array_diff($menuIds, array_keys(static::$cache)));

        if ($missing !== []) {
            $own = DB::table('menu_tax_classes')->whereIn('menu_id', $missing)->get(['menu_id', 'tax_class_id'])
                ->groupBy('menu_id')->map(fn($rows) => $rows->pluck('tax_class_id')->map('intval')->unique()->values()->all());

            $withoutOwn = array_values(array_diff($missing, $own->keys()->map('intval')->all()));
            $fromCategories = collect();
            if ($withoutOwn !== []) {
                $fromCategories = DB::table('menu_categories')
                    ->join('category_tax_classes', 'category_tax_classes.category_id', '=', 'menu_categories.category_id')
                    ->whereIn('menu_categories.menu_id', $withoutOwn)
                    ->get(['menu_categories.menu_id', 'category_tax_classes.tax_class_id'])
                    ->groupBy('menu_id')->map(fn($rows) => $rows->pluck('tax_class_id')->map('intval')->unique()->values()->all());
            }

            foreach ($missing as $menuId) {
                static::$cache[$menuId] = $own->get($menuId) ?? $fromCategories->get($menuId) ?? [];
            }
        }

        return array_intersect_key(static::$cache, array_flip($menuIds));
    }

    public static function clearCache(): void
    {
        static::$cache = [];
    }
}
