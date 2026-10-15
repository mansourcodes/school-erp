<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Settings\app\Http\Controllers\SettingCrudController as BaseSettingCrudController;
use Illuminate\Database\Eloquent\Builder;

/**
 * Extends the backpack/settings CRUD (wired via routes/backpack/settings.php):
 * shows the setting key and lets admins filter by a category derived from the key.
 */
class SettingCrudController extends BaseSettingCrudController
{
    /** Report template keys: `<report>.pre`, `<report>.pro`, `<report>.title`. */
    private const REPORT_SUFFIXES = ['.pre', '.pro', '.title'];

    private const PAYMENT_KEYS = ['payment_types', 'payment_sources'];

    public function setupListOperation()
    {
        parent::setupListOperation();

        CRUD::addColumn([
            'name'     => 'key',
            'label'    => trans('setting.key'),
            'type'     => 'text',
            'priority' => 1,
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhere('key', 'like', '%' . $searchTerm . '%');
            },
        ])->beforeColumn('name');

        CRUD::addColumn([
            'name'     => 'category',
            'label'    => trans('setting.category'),
            'type'     => 'closure',
            'function' => fn ($entry) => trans('setting.categories.' . self::categoryOf($entry->key)),
            'orderable'   => false,
            'searchLogic' => false,
        ])->afterColumn('key');

        CRUD::orderBy('key');

        CRUD::addFilter(
            [
                'name'  => 'category',
                'type'  => 'dropdown',
                'label' => trans('setting.category'),
            ],
            fn () => trans('setting.categories'),
            function ($value) {
                CRUD::addClause('where', fn (Builder $query) => self::scopeCategory($query, $value));
            }
        );
    }

    public function setupUpdateOperation()
    {
        CRUD::addField([
            'name'       => 'key',
            'label'      => trans('setting.key'),
            'type'       => 'text',
            'attributes' => [
                'disabled' => 'disabled',
                'dir'      => 'ltr',
            ],
        ]);

        parent::setupUpdateOperation();
    }

    public static function categoryOf(string $key): string
    {
        if (str_starts_with($key, 'portal.')) {
            return 'portal';
        }
        if (in_array($key, self::PAYMENT_KEYS, true)) {
            return 'payments';
        }
        foreach (self::REPORT_SUFFIXES as $suffix) {
            if (str_ends_with($key, $suffix)) {
                return 'reports';
            }
        }

        return 'general';
    }

    /** SQL equivalent of categoryOf(), keep both in sync. */
    private static function scopeCategory(Builder $query, string $category): void
    {
        if ($category === 'portal') {
            $query->where('key', 'like', 'portal.%');

            return;
        }
        if ($category === 'payments') {
            $query->whereIn('key', self::PAYMENT_KEYS);

            return;
        }

        $query->where('key', 'not like', 'portal.%')->whereNotIn('key', self::PAYMENT_KEYS);

        if ($category === 'reports') {
            $query->where(function (Builder $q) {
                foreach (self::REPORT_SUFFIXES as $suffix) {
                    $q->orWhere('key', 'like', '%' . $suffix);
                }
            });

            return;
        }

        foreach (self::REPORT_SUFFIXES as $suffix) {
            $query->where('key', 'not like', '%' . $suffix);
        }
    }
}
