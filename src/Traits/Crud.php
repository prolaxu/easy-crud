<?php

namespace Prolaxu\EasyCrud\Traits;


use Illuminate\Support\Str;

trait Crud
{
    public static function initializer($orderBy = true)
    {
        $request = request();
        $sortBy = $request->query('sortBy');
        $desc = $request->query('descending');

        $query = $request->query('query');

        $filters = json_decode($request->query('filters'));

        if (method_exists(static::class, 'initializeModel')) {
            $model = static::initializeModel();
        } else {
            $model = static::where((new  static())->getTable().'.id', '>', 0);
        }
        if ($query) {
            if (method_exists(static::class, 'scope'.ucfirst('queryfilter'))) {
                $model = $model->queryfilter($query);
            }
        }
        foreach (collect($filters) as $filter => $value) {
            if (str_contains($filter, '_')) {
                $filter = Str::camel($filter);
            }

            if ($value !== null) {
                if (method_exists(static::class, 'scope'.ucfirst($filter))) {
                    $model->{$filter}($value);
                } elseif (method_exists($model, $filter)) {
                    $model->{$filter}($value);
                }
            }
        }
        if ($orderBy) {
            if ($sortBy == 'null' || ! isset($sortBy)) {
                if (method_exists(static::class, 'sortByDefaults')) {
                    $sortByDefaults = static::sortByDefaults();
                    $sortBy = $sortByDefaults['sortBy'];
                    $desc = $sortByDefaults['sortByDesc'];
                } else {
                    $sortBy = (new  static())->getTable().'.id';
                    $desc = true;
                }
            }

            if ($desc == 'true') {
                $model->orderBy($sortBy, 'DESC');
            } else {
                $model->orderBy($sortBy, 'ASC');
            }
        }

        return $model;
    }
}
