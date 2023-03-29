<?php

namespace Prolaxu\EasyCrud\Traits;


use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Prolaxu\EasyCrud\Exceptions\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

trait RowsPerPage
{
    public function getResourceCollection($class, $builder, $additional = [], bool $simplePaginate = false)
    {
        try {
            return $class::collection($this->paginateData($builder, $simplePaginate))->additional($additional);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $exception->getMessage(),
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function paginateData($builder, $simplePaginate = false)
    {
        try {
            if ($simplePaginate) {
                return $builder->simplePaginate($this->rowsPerPage($builder));
            }

            return $builder->paginate($this->rowsPerPage($builder));
        } catch (Throwable $exception) {
            //            dd(' Exception', $exception);
        }
    }

    public function rowsPerPage($builder, $request = null): int
    {
        $this->validate(request(), [
            'rowsPerPage' => 'nullable|numeric|gte:0|lte:1000000000000000000',
        ]);

        return request()->query('rowsPerPage') == 0 ? $builder->count() : request()->query('rowsPerPage', 20);
    }

    public function getResourceObject($class, $builder, $additional = [])
    {
        try {
            return $class::make($builder)->additional($additional);
        } catch (Throwable $exception) {
            dd($exception);
        }
    }

    /**
     * @throws AccessDeniedException
     */
    public function checkPermission(...$permission): void
    {
        $permissions = collect($permission)->flatten()->toArray();

        if (! auth('admin-api')->user()->can($permissions)) {
            throw new AccessDeniedException('unauthorized_access');
        }
    }

    /**
     * @throws AccessDeniedException
     */
    public function checkRole(...$role): void
    {
        $roles = collect($role)->flatten()->toArray();
        if (! auth('admin-api')->user()->hasRole($roles)) {
            throw new AccessDeniedException('unauthorized_access');
        }
    }

    public function deletedAtString(): string
    {
        return '_deleted_'.Str::slug(Carbon::now()->toDateTimeString(), '_');
    }

    public function deleted($message = 'Deleted Successfully'): JsonResponse
    {
        return response()->json(['message' => $message], ResponseAlias::HTTP_NO_CONTENT);
    }
}
