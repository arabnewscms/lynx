<?php
namespace Lynx\Base;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Lynx\Base\Traits\Appendable;
use Lynx\Base\Traits\Policy;
use Lynx\Base\Traits\Queryable;
use Lynx\Base\Traits\Validation;

abstract

class Api extends Controller {
	use Validation, Appendable, Queryable, Policy;

	protected $entity;
	protected $policy_key;
	protected $policy;
	protected $guard;
	protected $data;
	protected $indexGuest = false;// this if user not logged-in

	// show or hide full json data when !
	protected $FullJsonInStore   = false;
	protected $FullJsonInUpdate  = false;
	protected $FullJsonInDestroy = false;

	protected $paginateIndex = true;
	protected $withTrashed   = false;// get Data withTrashed

	protected $resourcesJson;

	public function __construct() {
		$this->definePolicy();
	}

	protected function can($fn) {
		if (class_exists($this->policy)) {
			return !auth()       ->guard($this->guard)->user()->can($fn.'-'.$this->policy_key, $this->entity)?lynx()->status(403)
			->message(__('lynx.need_permission'))
			->response():true;
		} else {
			return true;
		}
	}

	/**
	 * Display a listing of the resource.
	 * @return Renderable
	 */
	public function indexAny() {
		if (!$this->indexGuest) {
			return lynx()
			->status(422)
			->message(__('lynx.no_data_in_guest'))
			->response();
		}

		$data = $this->appendQuery();
		$data = $this->paginateIndex?$data->paginate(request('per_page', 15)):
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
	 * Display a listing of the resource.
	 * @return Renderable
	 */
	public function index() {
		$can = $this->can('viewAny');
		if ($can !== true) {
			return $can;
		}

		$data = $this->appendQuery();
		$data = $this->paginateIndex?$data->paginate(request('per_page', 15)):
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
	public function store() {

		$can = $this->can('create');
		if ($can !== true) {
			return $can;
		}

		if (count($this->rules('store')) == 0) {
			$messageForDeveloper = __('lynx.must_add_rules', [
					'columns' => implode(',', (new $this->entity)->getFillable()),
				]);
			return lynx()->status(422)
			             ->message($messageForDeveloper)
			             ->response();
		}

		$this->data = $this->validate(request(),
			$this->rules('store'), [],
			method_exists($this, 'niceName')?
			$this->niceName():[]
		);

		$this->data = $this->beforeStore($this->data);

		$store = $this->entity::create($this->allInputsWithoutFiles());
		$this->afterStore($store);
		return lynx()->data($this->FullJsonInStore?$store:['id' => $store->id])
		->status(200)
		->message(__('lynx.recored_added'))
		->response();
	}

	/**
	 * Show the specified resource.
	 * @param int $id
	 * @return Renderable
	 */
	public function show($id) {
		$can = $this->can('view');
		if ($can !== true) {
			return $can;
		}

		$data = $this->appendShowQuery()->where('id', $id)->first();
		if (is_null($data)) {
			return lynx()->status(404)
			             ->message(__('lynx.not_found'))
			             ->response();
		} else {
			return lynx()->data($data)->response();
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param int $id
	 * @return Renderable
	 */
	public function update($id) {

		$can = $this->can('update');
		if ($can !== true) {
			return $can;
		}

		if (count($this->rules('update', $id)) == 0) {
			$messageForDeveloper = 'must be add rules method on your parent class or following fillable columns '.implode(',', (new $this->entity)->getFillable());
			return lynx()->status(422)
			             ->message($messageForDeveloper)
			             ->response();
		}

		// Check Record is exist
		if (is_null($data = $this->entity::find($id))) {
			return lynx()           ->status(404)
			                        ->message(__('lynx.not_found'))
			                        ->response();
		}

		// Validation Errors
		$this->data = $this->validate(request(),
			$this->rules('update'), [],
			method_exists($this->entity, 'niceName')?
			$this->entity::niceName():[]
		);

		$this->beforeUpdate($data);

		$update = $this->entity::where('id', $id)->update($this->allInputsWithoutFiles());
		$this->afterUpdate($data = $this->entity::find($id));
		return lynx()->data($this->FullJsonInUpdate?$data:['id' => $data->id])
		->status(200)
		->message(__('lynx.recored_updated'))
		->response();
	}

	/**
	 * Remove the specified resource from storage.
	 * @param int $id
	 * @return Renderable
	 */
	public function destroy($id) {

		$can = $this->can('delete');
		if ($can !== true) {
			return $can;
		}

		if ($this->withTrashed) {
			$data = $this->entity::withTrashed()->find($id);
		} else {
			$data = $this->entity::find($id);
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
		return lynx()->data($this->FullJsonInDestroy?$data:['id' => $data->id])
		->status(200)
		->message(__('lynx.deleted'))
		->response();
	}

	/**
	 * Remove the specified resource from storage. force
	 * @param int $id
	 * @return Renderable
	 */
	public function forceDelete($id) {

		$can = $this->can('forceDelete');
		if ($can !== true) {
			return $can;
		}

		if ($this->withTrashed) {
			$data = $this->entity::withTrashed()->find($id);
		} else {
			$data = $this->entity::find($id);
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
		return lynx()->data($this->FullJsonInDestroy?$data:['id' => $data->id])
		->status(200)
		->message(__('lynx.deleted'))
		->response();
	}

	/**
	 * Remove the specified resource from storage. force
	 * @param int $id
	 * @return Renderable
	 */
	public function restore($id) {

		$can = $this->can('restore');
		if ($can !== true) {
			return $can;
		}

		if ($this->withTrashed) {
			$data = $this->entity::withTrashed()->find($id);
		} else {
			$data = $this->entity::find($id);
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

		return lynx()->data($this->FullJsonInDestroy?$data:['id' => $data->id])
		->status(200)
		->message(__('lynx.restored'))
		->response();
	}

}
