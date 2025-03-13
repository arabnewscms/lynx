<?php
namespace Lynx\Base;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Lynx\Base\Traits\Appendable;
use Lynx\Base\Traits\Policy;
use Lynx\Base\Traits\Queryable;
use Lynx\Base\Traits\Validation;

abstract class Api extends Controller
{
    use Validation, Appendable, Queryable, Policy;

    protected $entity;
    protected $policy_key;
    protected $spatieQueryBuilder = false;
    protected $policy;
    protected $guard;
    protected $data;
    protected $indexGuest = false; // this if user not logged-in

    // show or hide full json data when !
    protected $FullJsonInStore   = false;
    protected $FullJsonInUpdate  = false;
    protected $FullJsonInDestroy = false;

    protected $paginateIndex = true;
    protected $withTrashed   = false; // get Data withTrashed

    protected $resourcesJson;

    public function __construct()
    {
        if ($this->spatieQueryBuilder === true) {
            if (!class_exists(\Spatie\QueryBuilder\QueryBuilder::class)) {
                throw new \Exception('You need to add "spatie/laravel-query-builder" as a Composer dependency. visit https://github.com/spatie/laravel-query-builder');
            }
        }
        $this->definePolicy();
    }



    protected function can($fn, $model)
    {
        if (class_exists($this->policy)) {
            //.'-'.$this->policy_key
            // dd(\auth()       ->guard($this->guard)->user()->can($fn, $model));

            return !auth()->guard($this->guard)->user()->can($fn, $model) ? lynx()->status(403)
                ->message(__('lynx.need_permission'))
                ->response() : true;
        } else {
            return true;
        }
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function indexAny()
    {

        if (!$this->indexGuest) {
            return lynx()
                ->status(422)
                ->message(__('lynx.no_data_in_guest'))
                ->response();
        }

        $data = $this->appendQuery();
        $data = $this->paginateIndex ? $data->paginate(request('per_page', 15)) : $data->get();
        if (!empty($this->resourcesJson)) {
            // Resource Collect every json field and can reuse in resource
            $collection = $this->resourcesJson::collection($data)->toResponse(app('request'))->getData();
        } else {
            $collection = $data;
        }

        return lynx()->data($collection)
            ->status(200)
            ->message(__('lynx.successfully'))
            ->response();
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {


        $can = $this->can('viewAny', $this->entity);
        if ($can !== true) {
            return $can;
        }

        $data = $this->appendQuery();
        $data = $this->paginateIndex ? $data->paginate(request('per_page', 15)) :
            $data->get();
            if (!empty($this->resourcesJson)) {
                // Resource Collect every json field and can reuse in resource
                $collection = $this->resourcesJson::collection($data)->toResponse(app('request'))->getData();

        } else {
            $collection = $data;
        }

        return lynx()->data($collection)
            ->status(200)
            ->message(__('lynx.successfully'))
            ->response();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Renderable
     */
    public function store(Request $request)
    {

        $can = $this->can('create', $this->entity);
        if ($can !== true) {
            return $can;
        }

        $this->data = $request->validate(
            $this->rules('store'),
            [],
            method_exists($this, 'niceName') ?
                $this->niceName() : []
        );

        $this->data = $this->beforeStore($this->data);
        $entity     = $this->entity;
        $storeSave = $entity::create($this->allInputsWithoutFiles());
        $this->afterStore($storeSave);
        return lynx()->data($this->FullJsonInStore ? $storeSave : ['id' => $storeSave->id])
            ->status(200)
            ->message(__('lynx.recored_added'))
            ->response();
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {

        $data = $this->appendShowQuery()->where('id', $id)->first();
        $can = $this->can('view', $data);
        if ($can !== true) {
            return $can;
        }


        if (is_null($data)) {
            return lynx()->status(404)
                ->message(__('lynx.not_found'))
                ->response();
        } else {
            $data = $this->afterShow($data);
            return lynx()->data($data)->response();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request,$id)
    {
        $data = $this->entity::find($id);
        $can = $this->can('update', $data);
        if ($can !== true) {
            return $can;
        }

        // Check Record is exist
        if (is_null($data)) {
            return lynx()->status(404)
                ->message(__('lynx.not_found'))
                ->response();
        }

        // Validation Errors

        $this->data = $request->validate(
            $this->rules('update',$id),
            [],
            method_exists($this, 'niceName') ?
                $this->niceName() : []
        );

        $this->beforeUpdate($data);
        $fillability = [];

        foreach ($this->allInputsWithoutFiles() as $key => $val) {
            if (in_array($key, app($this->entity)->getFillable())) {
                $fillability[$key] = request($key, $val);
            }
        }


        $this->entity::where('id', $id)->update($fillability);
        $data = $this->entity::find($id);

        $this->afterUpdate($data);
        return lynx()->data(
            $this->FullJsonInUpdate ?
                $data
                :
                [
                    'id' => $data->id,
                ]
        )
            ->status(200)
            ->message(__('lynx.recored_updated'))
            ->response();
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {

        if ($this->withTrashed) {
            $data = $this->entity::withTrashed()->find($id);
        } else {
            $data = $this->entity::find($id);
        }

        $can = $this->can('delete', $data);
        if ($can !== true) {
            return $can;
        }



        // Check Record is exist
        if (is_null($data)) {
            return lynx()
                ->status(404)
                ->message(__('lynx.not_found'))
                ->response();
        }

        $this->beforeDestroy($data);

        $data->delete();

        $this->afterDestroy($data);
        return lynx()->data($this->FullJsonInDestroy ? $data : ['id' => $data->id])
            ->status(200)
            ->message(__('lynx.deleted'))
            ->response();
    }

    /**
     * Remove the specified resource from storage. force
     * @param int $id
     * @return Renderable
     */
    public function forceDelete($id)
    {

        if ($this->withTrashed) {
            $data = $this->entity::withTrashed()->find($id);
        } else {
            $data = $this->entity::find($id);
        }

        $can = $this->can('forceDelete', $data);
        if ($can !== true) {
            return $can;
        }


        // Check Record is exist
        if (is_null($data)) {
            return lynx()
                ->status(404)
                ->message(__('lynx.not_found'))
                ->response();
        }

        $this->beforeDestroy($data);
        $data->forceDelete();
        return lynx()->data($this->FullJsonInDestroy ? $data : ['id' => $data->id])
            ->status(200)
            ->message(__('lynx.deleted'))
            ->response();
    }

    /**
     * Remove the specified resource from storage. force
     * @param int $id
     * @return Renderable
     */
    public function restore($id)
    {
        if ($this->withTrashed) {
            $data = $this->entity::withTrashed()->find($id);
        } else {
            $data = $this->entity::find($id);
        }

        $can = $this->can('restore', $data);
        if ($can !== true) {
            return $can;
        }



        // Check Record is exist
        if (is_null($data)) {
            return lynx()
                ->status(404)
                ->message(__('lynx.not_found'))
                ->response();
        }
        $this->beforeRestore($data);
        $data->restore();
        $this->afterRestore($data);

        return lynx()->data($this->FullJsonInDestroy ? $data : ['id' => $data->id])
            ->status(200)
            ->message(__('lynx.restored'))
            ->response();
    }
}
