<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * DataTableAdapter - Handles server-side data table operations
 *
 * Provides filtering, sorting, and pagination for Eloquent queries
 * with support for nested relations and various data types.
 */
class DataTableAdapter
{
    protected Builder|QueryBuilder $query;

    protected Request $request;

    /** @var array<string> Cache for joined tables */
    protected array $joinedTables = [];

    /** @var int Maximum records per page */
    protected int $maxPageSize = 100;

    /** @var int Default records per page */
    protected int $defaultPageSize = 10;

    public function __construct(Builder|QueryBuilder $query, Request $request)
    {
        $this->query = $query;
        $this->request = $request ?? request();
        $this->initializeJoinedTables();
    }

    /**
     * Initialize cache of already joined tables
     */
    protected function initializeJoinedTables(): void
    {
        $joins = $this->query->getQuery()->joins ?? [];
        $this->joinedTables = collect($joins)->pluck('table')->toArray();
    }

    /**
     * Static factory method to process data table request
     *
     * @return array{data: Collection, totalRecords: int}
     */
    public static function load(Builder|QueryBuilder $query, Request $request): array
    {
        $instance = new self($query, $request);

        return $instance->process();
    }

    /**
     * Process the data table request
     *
     * @return array{data: Collection, totalRecords: int}
     */
    public function process(): array
    {
        try {
            $this->applySorting();
            $this->applyFiltering();

            // Clone query before counting to avoid counting issues with pagination
            $totalRecords = $this->getTotalRecords();
            $this->applyPagination();

            return [
                'data' => $this->query->get(),
                'totalRecords' => $totalRecords,
            ];
        } catch (\Exception $e) {
            Log::error('DataTableAdapter process error', [
                'message' => $e->getMessage(),
                'request' => $this->request->all(),
            ]);
            throw $e;
        }
    }

    /**
     * Apply sorting to the query
     */
    protected function applySorting(): self
    {
        if ($this->request->has('sorts') && is_array($this->request->sorts)) {
            $sorts = $this->request->get('sorts');
            if (isset($sorts[0]) && is_string($sorts[0])) {
                $sorts = [$sorts];
            }
            collect($sorts)->each(function ($value) {
                if (! is_array($value) || count($value) !== 2) {
                    return;
                }

                [$field, $direction] = $value;

                // Validate direction
                if (! in_array($direction, ['asc', 'desc'], true)) {
                    return;
                }

                // Sanitize field name
                $field = $this->sanitizeFieldName($field);

                if (strpos($field, '.') !== false) {
                    $this->applyRelationSorting($field, $direction);
                } else {
                    $this->query->orderBy($field, $direction);
                }
            });
        }

        return $this;
    }

    /**
     * Apply sorting for relation fields
     */
    protected function applyRelationSorting(string $field, string $direction): void
    {
        $relation = explode('.', $field);
        $fieldName = array_pop($relation);
        $relationName = implode('.', $relation);

        $model = $this->query->getModel();

        if (! method_exists($model, $relationName)) {
            return;
        }

        try {
            $relationInstance = $model->{$relationName}();
            $relationTable = $relationInstance->getRelated()->getTable();

            $foreignKey = null;
            $ownerKey = null;

            if ($relationInstance instanceof BelongsTo) {
                $foreignKey = $relationInstance->getQualifiedForeignKeyName();
                $ownerKey = $relationInstance->getQualifiedOwnerKeyName();
            } elseif (
                $relationInstance instanceof HasOne ||
                $relationInstance instanceof HasMany ||
                $relationInstance instanceof HasManyThrough
            ) {
                $foreignKey = $relationInstance->getQualifiedForeignKeyName();
                $ownerKey = $relationInstance->getQualifiedParentKeyName();
            }

            // Only join if we have valid keys and table not already joined
            if ($foreignKey && $ownerKey && ! in_array($relationTable, $this->joinedTables, true)) {
                $this->query->join($relationTable, $foreignKey, '=', $ownerKey);
                $this->joinedTables[] = $relationTable;
            }

            $this->query->orderBy("{$relationTable}.{$fieldName}", $direction);
        } catch (\Exception $e) {
            Log::warning('DataTableAdapter relation sorting error', [
                'field' => $field,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sanitize field name to prevent SQL injection
     */
    protected function sanitizeFieldName(string $field): string
    {
        // Remove potentially dangerous characters, keep only alphanumeric, underscore, dot
        return preg_replace('/[^a-zA-Z0-9_.]/', '', $field);
    }

    /**
     * Apply filters to the query
     */
    protected function applyFiltering(): self
    {
        if (! $this->hasValidFilters()) {
            return $this;
        }

        foreach ($this->request->filters as $filter) {
            if (! is_array($filter) || count($filter) !== 3) {
                continue;
            }
            [$field, $matchMode, $value] = $filter;

            // Sanitize field name
            $field = $this->sanitizeFieldName($field);

            if (strpos($field, '.') !== false) {
                $this->applyRelationFilter([$field, $matchMode, $value]);
            } else {
                $this->applyFilter([$field, $matchMode, $value]);
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
        } elseif ($this->isBoolean($value)) {
            $this->applyBooleanFilter($field, $matchMode, $value);
        } elseif ($this->isDateFormat($value)) {
            $this->applyDateFilter($field, $matchMode, $value);
        } else {
            $this->applyStringFilter($field, $matchMode, $value);
        }
    }

    protected function isBoolean($value): bool
    {
        return is_bool($value) || in_array(strtolower($value), ['true', 'false', '1', '0'], true);
    }

    protected function isDateFormat($value): bool
    {
        if (! is_string($value)) {
            return false;
        }
        $patterns = [
            'Y-m-d',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d\TH:i',
            'Y-m-d\TH:i:s',
            'Y-m-d\TH:i:sP',
            'Y-m-d\TH:i:s.v\Z',
        ];

        foreach ($patterns as $pattern) {
            $parsedDate = \DateTime::createFromFormat($pattern, $value);
            if ($parsedDate && $parsedDate->format($pattern) === $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply string filter with escaped LIKE wildcards
     */
    protected function applyStringFilter(string $field, string $matchMode, $value): void
    {
        // Escape LIKE wildcards in user input
        $escapedValue = $this->escapeLikeWildcards((string) $value);

        $conditions = [
            'contains' => fn () => $this->query->where($field, 'LIKE', "%{$escapedValue}%"),
            'notContains' => fn () => $this->query->where($field, 'NOT LIKE', "%{$escapedValue}%"),
            'startsWith' => fn () => $this->query->where($field, 'LIKE', "{$escapedValue}%"),
            'endsWith' => fn () => $this->query->where($field, 'LIKE', "%{$escapedValue}"),
            'equals' => fn () => $this->query->where($field, '=', $value),
            'notEquals' => fn () => $this->query->where($field, '!=', $value),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        } else {
            throw new \InvalidArgumentException("Invalid match mode: {$matchMode}");
        }
    }

    /**
     * Escape LIKE wildcards in user input
     */
    protected function escapeLikeWildcards(string $value): string
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $value);
    }

    protected function applyNumericFilter(string $field, string $matchMode, $value): void
    {
        $conditions = [
            'equals' => fn () => $this->query->where($field, '=', $value),
            'notEquals' => fn () => $this->query->where($field, '!=', $value),
            'lt' => fn () => $this->query->where($field, '<', $value),
            'lte' => fn () => $this->query->where($field, '<=', $value),
            'gt' => fn () => $this->query->where($field, '>', $value),
            'gte' => fn () => $this->query->where($field, '>=', $value),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        } else {
            $this->applyStringFilter($field, $matchMode, $value);
        }
    }

    /**
     * Apply date filter using Carbon for reliable parsing
     */
    protected function applyDateFilter(string $field, string $matchMode, $value): void
    {
        try {
            $date = Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning('Invalid date format', ['value' => $value, 'error' => $e->getMessage()]);

            return;
        }

        $conditions = [
            'equals' => fn () => $this->query->whereDate($field, '=', $date),
            'notEquals' => fn () => $this->query->whereDate($field, '!=', $date),
            'lt' => fn () => $this->query->whereDate($field, '<', $date),
            'lte' => fn () => $this->query->whereDate($field, '<=', $date),
            'gt' => fn () => $this->query->whereDate($field, '>', $date),
            'gte' => fn () => $this->query->whereDate($field, '>=', $date),
            'dateIs' => fn () => $this->query->whereDate($field, '=', $date),
            'dateIsNot' => fn () => $this->query->whereDate($field, '!=', $date),
            'dateBefore' => fn () => $this->query->whereDate($field, '<', $date),
            'dateAfter' => fn () => $this->query->whereDate($field, '>', $date),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        } else {
            $this->applyStringFilter($field, $matchMode, $value);
        }
    }

    protected function applyBooleanFilter(string $field, string $matchMode, $value): void
    {
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        if ($matchMode === 'equals') {
            $this->query->where($field, '=', $value ? 1 : 0);
        } else {
            $this->applyStringFilter($field, $matchMode, $value);
        }
    }

    protected function applyArrayFilter(string $field, string $matchMode, array $value): void
    {
        $conditions = [
            'equals' => fn () => $this->query->whereIn($field, $value),
            'between' => fn () => $this->query->whereBetween($field, $value),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        } else {
            throw new \InvalidArgumentException("Invalid match mode: {$matchMode}");
        }
    }

    /**
     * Apply relation filter - refactored to reduce code duplication
     */
    protected function applyRelationFilter(array $filter): void
    {
        if (count($filter) !== 3) {
            return;
        }

        [$field, $matchMode, $value] = $filter;

        $relation = explode('.', $field);
        $fieldName = array_pop($relation);
        $relationName = implode('.', $relation);

        if (is_array($value)) {
            $this->applyWhereHasFilter($relationName, $fieldName, $matchMode, $value, 'array');
        } elseif (is_numeric($value)) {
            $this->applyWhereHasFilter($relationName, $fieldName, $matchMode, $value, 'numeric');
        } elseif (is_bool($value)) {
            $this->applyWhereHasFilter($relationName, $fieldName, $matchMode, $value, 'boolean');
        } elseif ($this->isDateFormat($value)) {
            $this->applyWhereHasFilter($relationName, $fieldName, $matchMode, $value, 'date');
        } else {
            $this->applyWhereHasFilter($relationName, $fieldName, $matchMode, $value, 'string');
        }
    }

    /**
     * Unified whereHas filter application to reduce code duplication
     */
    protected function applyWhereHasFilter(string $relation, string $field, string $matchMode, $value, string $type): void
    {
        $this->query->whereHas($relation, function ($query) use ($field, $matchMode, $value, $type) {
            switch ($type) {
                case 'string':
                    $escapedValue = $this->escapeLikeWildcards((string) $value);
                    $this->applyStringCondition($query, $field, $matchMode, $value, $escapedValue);
                    break;
                case 'numeric':
                    $this->applyNumericCondition($query, $field, $matchMode, $value);
                    break;
                case 'boolean':
                    $query->where($field, '=', $value ? 1 : 0);
                    break;
                case 'date':
                    try {
                        $date = Carbon::parse($value)->format('Y-m-d');
                        $this->applyDateCondition($query, $field, $matchMode, $date);
                    } catch (\Exception $e) {
                        Log::warning('Invalid date in relation filter', ['value' => $value]);
                    }
                    break;
                case 'array':
                    $this->applyArrayCondition($query, $field, $matchMode, $value);
                    break;
            }
        });
    }

    /**
     * Apply string condition to query
     */
    protected function applyStringCondition($query, string $field, string $matchMode, $value, string $escapedValue): void
    {
        $conditions = [
            'contains' => fn () => $query->where($field, 'LIKE', "%{$escapedValue}%"),
            'notContains' => fn () => $query->where($field, 'NOT LIKE', "%{$escapedValue}%"),
            'startsWith' => fn () => $query->where($field, 'LIKE', "{$escapedValue}%"),
            'endsWith' => fn () => $query->where($field, 'LIKE', "%{$escapedValue}"),
            'equals' => fn () => $query->where($field, '=', $value),
            'notEquals' => fn () => $query->where($field, '!=', $value),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        }
    }

    /**
     * Apply numeric condition to query
     */
    protected function applyNumericCondition($query, string $field, string $matchMode, $value): void
    {
        $conditions = [
            'equals' => fn () => $query->where($field, '=', $value),
            'notEquals' => fn () => $query->where($field, '!=', $value),
            'lt' => fn () => $query->where($field, '<', $value),
            'lte' => fn () => $query->where($field, '<=', $value),
            'gt' => fn () => $query->where($field, '>', $value),
            'gte' => fn () => $query->where($field, '>=', $value),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        }
    }

    /**
     * Apply date condition to query
     */
    protected function applyDateCondition($query, string $field, string $matchMode, string $date): void
    {
        $conditions = [
            'equals' => fn () => $query->whereDate($field, '=', $date),
            'notEquals' => fn () => $query->whereDate($field, '!=', $date),
            'lt' => fn () => $query->whereDate($field, '<', $date),
            'lte' => fn () => $query->whereDate($field, '<=', $date),
            'gt' => fn () => $query->whereDate($field, '>', $date),
            'gte' => fn () => $query->whereDate($field, '>=', $date),
            'dateIs' => fn () => $query->whereDate($field, '=', $date),
            'dateIsNot' => fn () => $query->whereDate($field, '!=', $date),
            'dateBefore' => fn () => $query->whereDate($field, '<', $date),
            'dateAfter' => fn () => $query->whereDate($field, '>', $date),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        }
    }

    /**
     * Apply array condition to query
     */
    protected function applyArrayCondition($query, string $field, string $matchMode, array $value): void
    {
        $conditions = [
            'equals' => fn () => $query->whereIn($field, $value),
            'between' => fn () => $query->whereBetween($field, $value),
        ];

        if (isset($conditions[$matchMode])) {
            $conditions[$matchMode]();
        }
    }

    protected function hasValidFilters(): bool
    {
        return $this->request->has('filters') && is_array($this->request->filters);
    }

    /**
     * Apply pagination with validation
     */
    protected function applyPagination(): self
    {
        $page = max(1, (int) $this->request->input('page', 1));
        $size = min(
            $this->maxPageSize,
            max(1, (int) $this->request->input('size', $this->defaultPageSize))
        );

        $this->query->skip(($page - 1) * $size)->take($size);

        return $this;
    }

    /**
     * Get total records by cloning query to avoid pagination interference
     */
    protected function getTotalRecords(): int
    {
        return (clone $this->query)->count();
    }
}
