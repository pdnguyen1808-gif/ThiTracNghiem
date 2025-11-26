<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait SortableAndSearchable
{
    /**
     * Apply sorting and searching to a query.
     *
     * @param  string  $modelClass  The model class to query
     * @param  Request  $request  The HTTP request
     * @param  string  $searchColumn  The column to search in
     * @param  string  $notFoundMessage  The message to show when no results are found
     * @return array{data: \Illuminate\Database\Eloquent\Collection, message: string|null, sort: array|null}
     */
    protected function applySortAndSearch(
        string $modelClass,
        Request $request,
        string $searchColumn,
        string $notFoundMessage
    ): array {
        $query = $modelClass::query();

        return $this->applySortAndSearchToQuery($query, $request, $searchColumn, $notFoundMessage);
    }

    /**
     * Apply sorting and searching to a relationship query.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation  $relation  The relationship query
     * @param  Request  $request  The HTTP request
     * @param  string  $searchColumn  The column to search in
     * @param  string  $notFoundMessage  The message to show when no results are found
     * @return array{data: \Illuminate\Database\Eloquent\Collection, message: string|null, sort: array|null}
     */
    protected function applySortAndSearchToRelation(
        $relation,
        Request $request,
        string $searchColumn,
        string $notFoundMessage
    ): array {
        return $this->applySortAndSearchToQuery($relation, $request, $searchColumn, $notFoundMessage);
    }

    /**
     * Apply sorting and searching to any query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation  $query  The query builder
     * @param  Request  $request  The HTTP request
     * @param  string  $searchColumn  The column to search in (must be a valid SQL identifier)
     * @param  string  $notFoundMessage  The message to show when no results are found
     * @return array{data: \Illuminate\Database\Eloquent\Collection, message: string|null, sort: array|null}
     */
    protected function applySortAndSearchToQuery(
        $query,
        Request $request,
        string $searchColumn,
        string $notFoundMessage
    ): array {
        $message = null;

        // Validate search column is a valid SQL identifier (alphanumeric and underscore only)
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $searchColumn)) {
            throw new \InvalidArgumentException('Invalid search column name');
        }

        if ($request->get('sort')['enabel']) {
            $column = $request->get('sort')['column'];
            $type = $request->get('sort')['type'];

            // Validate sort column is a valid SQL identifier
            if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
                $query = $query->orderBy($column, $type);
            }
        }

        if ($request->get('searchKey')) {
            $searchKey = strtolower($request->get('searchKey'));
            $query = $query->whereRaw('LOWER(' . $searchColumn . ') LIKE ?', ['%' . $searchKey . '%']);
            $data = $query->get();
            $message = count($data) == 0 ? $notFoundMessage : null;
        } else {
            $data = $query->get();
        }

        return [
            'data' => $data,
            'message' => $message,
            'sort' => $request->get('sort'),
        ];
    }
}


