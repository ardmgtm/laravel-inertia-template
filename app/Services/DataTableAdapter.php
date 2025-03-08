<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

class DataTableAdapter
{
    protected Builder|QueryBuilder $query;
    protected Request $request;

    public function __construct(Builder|QueryBuilder $query, Request $request = null)
    {
        $this->query = $query;
        $this->request = $request ?? request();
    }

    public static function load(Builder|QueryBuilder $query, Request $request = null): array
    {
        $instance = new self($query, $request);
        return $instance->process();
    }

    public function process(): array
    {
        $this->applySorting();
        $this->applyFiltering();

        $totalRecords = $this->getTotalRecords();
        $this->applyPagination();

        return [
            'data' => $this->query->get(),
            'totalRecords' => $totalRecords,
            'sql' => $this->query->toSql(),
            'bindings' => $this->query->getBindings(),
        ];
    }

    protected function applySorting(): self
    {
        if ($this->request->has('sorts') && is_array($this->request->sorts)) {
            $sorts = $this->request->get('sorts');
            if (is_string($sorts[0])) {
                $sorts = [$sorts];
            }
            collect($sorts)->each(function ($value) {
                [$field, $direction] = $value;
                if (in_array($direction, ['asc', 'desc'])) {
                    if (strpos($field, '.') !== false) {
                        $relation = explode('.', $field);
                        $fieldName = array_pop($relation);
                        $relationName = implode('.', $relation);

                        $model = $this->query->getModel();

                        if (method_exists($model, $relationName)) {
                            $relationInstance = $model->{$relationName}();
                            $relationTable = $relationInstance->getRelated()->getTable();

                            if ($relationInstance instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                                $foreignKey = $relationInstance->getQualifiedForeignKeyName();
                                $ownerKey = $relationInstance->getQualifiedOwnerKeyName();
                            } elseif (
                                $relationInstance instanceof \Illuminate\Database\Eloquent\Relations\HasOne ||
                                $relationInstance instanceof \Illuminate\Database\Eloquent\Relations\HasMany ||
                                $relationInstance instanceof \Illuminate\Database\Eloquent\Relations\HasManyThrough
                            ) {
                                $foreignKey = $relationInstance->getQualifiedForeignKeyName();
                                $localKey = $relationInstance->getQualifiedParentKeyName();
                            }

                            if (!collect($this->query->getQuery()->joins)->pluck('table')->contains($relationTable)) {
                                $this->query->join($relationTable, $foreignKey, '=', $ownerKey ?? $localKey);
                            }

                            $this->query->orderBy("{$relationTable}.{$fieldName}", $direction);
                        }
                    } else {
                        $this->query->orderBy($field, $direction);
                    }
                }
            });
        }

        return $this;
    }

    protected function applyFiltering(): self
    {
        if (!$this->hasValidFilters()) {
            return $this;
        }

        foreach ($this->request->filters as $filter) {
            if (count($filter) !== 3) {
                continue;
            }
            [$field, $matchMode, $value] = $filter;
            if (strpos($field, ".") !== false) {
                $this->applyRelationFilter($filter);
            } else {
                $this->applyFilter($filter);
            }
        }

        return $this;
    }

    protected function applyFilter(array $filter): void
    {
        if (count($filter) !== 3) {
            return;
        }

        [$field, $matchMode, $value] = $filter;

        if (is_array($value)) {
            $this->query->whereIn($field, $value);
        } elseif (is_numeric($value)) {
            $this->applyNumericFilter($field, $matchMode, $value);
        } elseif (is_bool($value)) {
            $this->applyBooleanFilter($field, $matchMode, $value);
        } elseif ($this->isDateFormat($value)) {
            $this->applyDateFilter($field, $matchMode, $value);
        } else {
            $this->applyStringFilter($field, $matchMode, $value);
        }
    }

    protected function isDateFormat($value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        $patterns = [
            'Y-m-d',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d\TH:i:sP',
            'Y-m-d\TH:i:s',
            'Y-m-d\TH:i',
        ];

        foreach ($patterns as $pattern) {
            $parsedDate = \DateTime::createFromFormat($pattern, $value);
            if ($parsedDate && $parsedDate->format($pattern) === $value) {
                return true;
            }
        }

        return false;
    }

    protected function applyStringFilter(string $field, string $matchMode, $value): void
    {
        $conditions = [
            'contains' => fn() => $this->query->where($field, 'LIKE', "%{$value}%"),
            'notContains' => fn() => $this->query->where($field, 'NOT LIKE', "%{$value}%"),
            'startsWith' => fn() => $this->query->where($field, 'LIKE', "{$value}%"),
            'endsWith' => fn() => $this->query->where($field, 'LIKE', "%{$value}"),
            'equals' => fn() => $this->query->where($field, '=', $value),
            'notEquals' => fn() => $this->query->where($field, '!=', $value),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        } else {
            throw new \InvalidArgumentException("Invalid match mode: {$matchMode}");
        }
    }

    protected function applyNumericFilter(string $field, string $matchMode, $value): void
    {
        $conditions = [
            'equals' => fn() => $this->query->where($field, '=', $value),
            'notEquals' => fn() => $this->query->where($field, '!=', $value),
            'lt' => fn() => $this->query->where($field, '<', $value),
            'lte' => fn() => $this->query->where($field, '<=', $value),
            'gt' => fn() => $this->query->where($field, '>', $value),
            'gte' => fn() => $this->query->where($field, '>=', $value),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        } else {
            $this->applyStringFilter($field, $matchMode, $value);
        }
    }

    protected function applyDateFilter(string $field, string $matchMode, $value): void
    {
        $value = date('Y-m-d', strtotime($value));

        $conditions = [
            'equals' => fn() => $this->query->whereDate($field, '=', $value),
            'notEquals' => fn() => $this->query->whereDate($field, '!=', $value),
            'lt' => fn() => $this->query->whereDate($field, '<', $value),
            'lte' => fn() => $this->query->whereDate($field, '<=', $value),
            'gt' => fn() => $this->query->whereDate($field, '>', $value),
            'gte' => fn() => $this->query->whereDate($field, '>=', $value),
            'dateIs' => fn() => $this->query->whereDate($field, '=', $value),
            'dateIsNot' => fn() => $this->query->whereDate($field, '!=', $value),
            'dateBefore' => fn() => $this->query->whereDate($field, '<', $value),
            'dateAfter' => fn() => $this->query->whereDate($field, '>', $value),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        } else {
            $this->applyStringFilter($field, $matchMode, $value);
        }
    }

    protected function applyBooleanFilter(string $field, string $matchMode, $value): void
    {
        $this->query->where($field, '=', $value ? 1 : 0);
    }

    protected function applyArrayFilter(string $field, string $matchMode, array $value): void
    {
        $conditions = [
            'equals' => fn() => $this->query->whereIn($field, $value),
            'between' => fn() => $this->query->whereBetween($field, $value),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        } else {
            throw new \InvalidArgumentException("Invalid match mode: {$matchMode}");
        }
    }

    protected function applyRelationFilter(array $filter): void
    {
        if (count($filter) !== 3) {
            return;
        }

        [$field, $matchMode, $value] = $filter;

        if (is_array($value)) {
            $this->applyArrayRelationFilter($field, $matchMode, $value);
        } elseif (is_numeric($value)) {
            $this->applyNumericRelationFilter($field, $matchMode, $value);
        } elseif (is_bool($value)) {
            $this->applyBooleanRelationFilter($field, $matchMode, $value);
        } elseif ($this->isDateFormat($value)) {
            $this->applyDateRelationFilter($field, $matchMode, $value);
        } else {
            $this->applyStringRelationFilter($field, $matchMode, $value);
        }
    }

    protected function applyStringRelationFilter(string $field, string $matchMode, $value): void
    {
        $relation = explode('.', $field);
        $fieldName = array_pop($relation);
        $has = implode(".", $relation);
        $conditions = [
            'contains' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->where($fieldName, 'LIKE', "%{$value}%");
            }),
            'notContains' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->where($fieldName, 'NOT LIKE', "%{$value}%");
            }),
            'startsWith' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->where($fieldName, 'LIKE', "{$value}%");
            }),
            'endsWith' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->where($fieldName, 'LIKE', "%{$value}");
            }),
            'equals' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->where($fieldName, '=', $value);
            }),
            'notEquals' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->where($fieldName, '!=', $value);
            }),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        }
    }

    protected function applyNumericRelationFilter(string $field, string $matchMode, $value): void
    {
        $relation = explode('.', $field);
        $fieldName = array_pop($relation);
        $has = implode(".", $relation);

        $conditions = [
            'equals' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->where($fieldName, '=', $value);
            }),
            'notEquals' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->where($fieldName, '!=', $value);
            }),
            'lt' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->where($fieldName, '<', $value);
            }),
            'lte' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->where($fieldName, '<=', $value);
            }),
            'gt' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->where($fieldName, '>', $value);
            }),
            'gte' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->where($fieldName, '>=', $value);
            }),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        } else {
            $this->applyStringRelationFilter($field, $matchMode, $value);
        }
    }

    protected function applyBooleanRelationFilter(string $field, string $matchMode, $value): void
    {
        $relation = explode('.', $field);
        $fieldName = array_pop($relation);
        $has = implode(".", $relation);

        $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
            $query->where($fieldName, '=', $value ? 1 : 0);
        });
    }

    protected function applyDateRelationFilter(string $field, string $matchMode, $value): void
    {
        $relation = explode('.', $field);
        $fieldName = array_pop($relation);
        $has = implode(".", $relation);

        $value = date('Y-m-d', strtotime($value));

        $conditions = [
            'equals' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->whereDate($fieldName, '=', $value);
            }),
            'notEquals' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->whereDate($fieldName, '!=', $value);
            }),
            'lt' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->whereDate($fieldName, '<', $value);
            }),
            'lte' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->whereDate($fieldName, '<=', $value);
            }),
            'gt' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->whereDate($fieldName, '>', $value);
            }),
            'gte' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->whereDate($fieldName, '>=', $value);
            }),
            'dateIs' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->whereDate($fieldName, '=', $value);
            }),
            'dateIsNot' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->whereDate($fieldName, '!=', $value);
            }),
            'dateBefore' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->whereDate($fieldName, '<', $value);
            }),
            'dateAfter' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->whereDate($fieldName, '>', $value);
            }),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        } else {
            $this->applyStringRelationFilter($field, $matchMode, $value);
        }
    }

    protected function applyArrayRelationFilter(string $field, string $matchMode, array $value): void
    {
        $relation = explode('.', $field);
        $fieldName = array_pop($relation);
        $has = implode(".", $relation);

        $conditions = [
            'equals' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->whereIn($fieldName, $value);
            }),
            'between' => fn() => $this->query->whereHas($has, function ($query) use ($fieldName, $value) {
                $query->whereBetween($fieldName, $value);
            }),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        } else {
            throw new \InvalidArgumentException("Invalid match mode: {$matchMode}");
        }
    }

    protected function hasValidFilters(): bool
    {
        return $this->request->has('filters') && is_array($this->request->filters);
    }

    protected function applyPagination(): self
    {
        $page = $this->request->input('page', 1);
        $size = $this->request->input('size', 10);

        $this->query->skip(($page - 1) * $size)->take($size);

        return $this;
    }

    protected function getTotalRecords(): int
    {
        return $this->query->count();
    }
}
