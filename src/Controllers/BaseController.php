<?php

namespace Prolaxu\EasyCrud\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Inventory\Traits\RowsPerPage;
use Prolaxu\EasyCrud\Exceptions\AccessDeniedException;
use ReflectionClass;
use Throwable;

class BaseController extends Controller
{

    //tobe continue with testing and its implementation( anil)
    use RowsPerPage;

    public array $withAll = [];

    public array $withCount = [];

    public array $withAggregate = [];

    public array $scopes = [];

    public array $scopeWithValue = [];


    public function __construct(public $model, public $resource, public $storeRequest, public $updateRequest)
    {
        $constants = new ReflectionClass($this->model);
        try {
            $permissionSlug = $constants->getConstant('PERMISSIONSLUG');
        } catch (Exception $e) {
            $permissionSlug = NULL;
        }
        if ($permissionSlug) {
            $this->middleware('permission:view-' . $this->model::PERMISSIONSLUG)->only(['index', 'show']);
            $this->middleware('permission:alter-' . $this->model::PERMISSIONSLUG)->only(['store', 'update', 'changeStatus']);
            $this->middleware('permission:delete-' . $this->model::PERMISSIONSLUG)->only(['delete']);
        }
    }


    /**
     * @throws AccessDeniedException
     */
    public function index(): JsonResource
    {
        $model = $this->model::initializer()
            ->when(property_exists($this, 'withAll') && count($this->withAll), function ($query) {
                return $query->with($this->withAll);
            })
            ->when(property_exists($this, 'withCount') && count($this->withCount), function ($query) {
                return $query->withCount($this->withCount);
            })
            ->when(property_exists($this, 'withAggregate') && count($this->withAggregate), function ($query) {
                foreach ($this->withAggregate as $key => $value) {
                    $query->withAggregate($key, $value);
                }
            })
            ->when(property_exists($this, 'scopes') && count($this->scopes), function ($query) {
                foreach ($this->scopes as $value) {
                    $query->$value();
                }
            })
            ->when(property_exists($this, 'scopeWithValue') && count($this->scopeWithValue), function ($query) {
                foreach ($this->scopeWithValue as $key => $value) {
                    $query->$key($value);
                }
            });

        return $this->resource::collection($model->paginates());

        //        return $model->get();

        //        return $this->getResourceCollection($this->resource, $model);
    }


    /**
     * @throws Throwable
     * @throws AccessDeniedException
     */
    public function store()
    {
        $data = resolve($this->storeRequest)->safe()->only((new $this->model())->getFillable());
        try {
            DB::beginTransaction();
            $model = $this->model::create($data);
            if (method_exists(new $this->model(), 'afterCreateProcess')) {
                $model->afterCreateProcess();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->reportError($e->getMessage());
        }

        return $this->getResourceObject($this->resource, $model);
    }


    public function delete(Request $request): JsonResponse
    {
        $this->validate($request, [
            'delete_rows' => ['required', 'array'],
            'delete_rows.*' => ['required', 'exists:' . (new  $this->model())->getTable() . ',id'],
        ]);

        foreach ((array) $request->input('delete_rows') as $item) {
            $model = $this->model::find($item);
            if (method_exists(new $this->model(), 'afterDeleteProcess') && $model) {
                $model->afterDeleteProcess();
            }
            if ($model) {
                $model->delete();
            }
        }

        return $this->reportSuccess(message: 'Data deleted successfully');
    }


    public function show($id)
    {
        $model = $this->model::initializer()
            ->when(property_exists($this, 'withAll') && count($this->withAll), function ($query) {
                return $query->with($this->withAll);
            })
            ->when(property_exists($this, 'withCount') && count($this->withCount), function ($query) {
                return $query->withCount($this->withCount);
            })
            ->when(property_exists($this, 'withAggregate') && count($this->withAggregate), function ($query) {
                foreach ($this->withAggregate as $key => $value) {
                    $query->withAggregate($key, $value);
                }
            })
            ->when(property_exists($this, 'scopes') && count($this->scopes), function ($query) {
                foreach ($this->scopes as $value) {
                    $query->$value();
                }
            })
            ->when(property_exists($this, 'scopeWithValue') && count($this->scopeWithValue), function ($query) {
                foreach ($this->scopeWithValue as $key => $value) {
                    $query->$key($value);
                }
            })
            ->findOrFail($id);

        return $this->getResourceObject($this->resource, $model);
    }



    public function destroy($id)
    {
        $model = $this->model::findOfFail($id);
        if (method_exists(new $this->model(), 'afterDeleteProcess')) {
            $model->afterDeleteProcess();
        }
        $model->delete();
        return $this->reportSuccess('Data deleted successfully',code: 204);
    }


    /**
     * @throws Throwable
     */
    public function changeStatus($id)
    {
        $model = $this->model::findOrFail($id);
        try {
            DB::beginTransaction();
            if (method_exists(new $this->model(), 'beforeChangeStatusProcess')) {
                $model->beforeChangeStatusProcess();
            }
            if (!$this->checkFillable($model, ['status'])) {
                DB::rollBack();
                throw new Exception('Status column not found in fillable');
            }
            $model->update(['status' => $model->status === 1 ? 0 : 1]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->reportError($e->getMessage());
        }

        return $this->resource::make($model);
    }


    private function checkFillable($model, $columns): bool
    {
        $fillableColumns = $this->fillableColumn($model);

        $diff = array_diff($columns, $fillableColumns);

        return count($diff) > 0 ? FALSE : TRUE;
    }


    /**
     * @throws Throwable
     * @throws AccessDeniedException
     */
    public function update($id)
    {
        $data = resolve($this->updateRequest)->safe()->only((new $this->model())->getFillable());

        $model = $this->model::findOrFail($id);

        try {
            DB::beginTransaction();
            $model->update($data);
            if (method_exists(new $this->model(), 'afterUpdateProcess')) {
                $model->afterUpdateProcess();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->reportError($e->getMessage());
        }

        return $this->getResourceObject($this->resource, $model);
    }


    private function fillableColumn($model): array
    {
        return Schema::getColumnListing($this->tableName($model));
    }


    private function tableName($model): string
    {
        return $model->getTable();
    }
}
