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
        $totalRecords = $this->getTotalRecords();
        $this->applySorting()
            ->applyFiltering()
            ->applyPagination();

        return [
            'data' => $this->query->get(),
            'sql' => $this->query->toSql(),
            'binding' => $this->query->getBindings(),
            'totalRecords' => $totalRecords,
        ];
    }

    protected function applySorting(): self
    {
        if ($this->request->has('sorts') && is_array($this->request->sorts) && count($this->request->sorts) === 2) {
            [$field, $direction] = $this->request->sorts;
            if (in_array($direction, ['asc', 'desc'])) {
                $this->query->orderBy($field, $direction);
            }
        }
        return $this;
    }

    protected function applyFiltering(): self
    {
        if (!$this->hasValidFilters()) {
            return $this;
        }

        foreach ($this->request->filters as $filter) {
            $this->applyFilter($filter);
        }

        return $this;
    }

    protected function applyFilter(array $filter): void
    {
        if (count($filter) !== 3) {
            return;
        }

        [$field, $matchMode, $value] = $filter;

        $value = $this->castFilterValue($value);

        if (is_numeric($value)) {
            $this->applyNumericFilter($field, $matchMode, $value);
        } elseif (is_bool($value)) {
            $this->applyBooleanFilter($field, $matchMode, $value);
        } elseif ($this->isDateFormat($value)) {
            $this->applyDateFilter($field, $matchMode, $value);
        } else {
            $this->applyStringFilter($field, $matchMode, $value);
        }
    }

    protected function castFilterValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (in_array(strtolower($value), ['true', 'false', '1', '0'], true)) {
            $result = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        } elseif (is_numeric($value)) {
            $result = str_contains($value, '.') ? (float) $value : (int) $value;
        } elseif ($this->isDateFormat($value)) {
            $result = date('Y-m-d', strtotime($value));
        } else {
            $result = (string) $value;
        }

        return $result;
    }

    protected function isDateFormat($value): bool
    {
        return (bool) strtotime($value);
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
        }else{
            $this->applyStringFilter($field,$matchMode,$value);
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
        }else{
            $this->applyStringFilter($field,$matchMode,$value);
        }
    }

    protected function applyBooleanFilter(string $field, string $matchMode, $value): void
    {
        $this->query->where($field, '=', $value ? 1 : 0);
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
