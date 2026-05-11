<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

trait PaginatesApi
{
    /**
     * @template TResource of JsonResource
     * @param LengthAwarePaginator $paginator
     * @param class-string<TResource> $resourceClass
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, string $resourceClass)
    {
        return $resourceClass::collection($paginator)->additional([
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * @template TResource of JsonResource
     * @param LengthAwarePaginator $paginator
     * @param class-string<TResource> $resourceClass
     * @return array{data: array<int, mixed>, meta: array<string, int>}
     */
    protected function paginatedData(LengthAwarePaginator $paginator, string $resourceClass): array
    {
        return [
            'data' => $resourceClass::collection($paginator)->resolve(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }
}
