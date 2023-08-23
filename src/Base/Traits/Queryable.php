<?php
namespace Lynx\Base\Traits;

trait Queryable {

    /**
     * replace_entity
     * to check if use Spatie QueryBuilder Package
     * https://github.com/spatie/laravel-query-builder
     * @return object
     */
    protected function replace_entity(){

        return $this->spatieQueryBuilder?
            \Spatie\QueryBuilder\QueryBuilder::for($this->entity) : new $this->entity;

    }

	/**
	 * master of query method
	 * @return query methods
	 */
	public function query($entity):Object {
		return $entity;
	}

	/**
	 * this method can do more with model or entity before store
	 * @return object
	 */
	public function beforeStore(array $data):array{
		return $data;
	}

	/**
	 * this method can do more with model or entity after store
	 * @return object
	 */
	public function afterStore($entity):void {

	}

	/**
	 * this method can do more with model or entity before Update
	 * @return object
	 */
	public function beforeUpdate($entity):void {}

	/**
	 * this method can do more with model or entity after Update
	 * @return object
	 */
	public function afterUpdate($entity):void {}

	/**
	 * master of query method
	 * @return query methods
	 */
	public function beforeShow($entity):Object {
		return $entity;
	}

	/**
	 * master of query method
	 * @return query methods
	 */
	public function afterShow($entity):Object {
       // return new $this->resourcesJson($entity);
        return $entity;
	}

	/**
	 * master of query method
	 * @return query methods
	 */
	public function afterDestroy($entity):void {}

	/**
	 * master of query method
	 * @return query methods
	 */
	public function beforeDestroy($entity):void {}

	/**
	 * master of query method
	 * @return query methods
	 */
	public function beforeRestore($entity):void {}

	/**
	 * master of query method
	 * @return query methods
	 */
	public function afterRestore($entity):void {}

	/**
	 * prepend query and appended to parent Api::class
	 * @return entity query
	 */
	public function appendQuery() {
		$query = $this->query($this->replace_entity())->orderBy(request('orderBy', 'id'), request('sort', 'desc'));
		if (request('limit') > 0) {
			$query = $query->limit('limit', request('limit'));
		}
		return $query;
	}
	/**
	 * prepend show query and appended to parent Api::class
	 * @return entity query
	 */
	public function appendShowQuery() {
		return $this->beforeShow($this->replace_entity())->orderBy(request('orderBy', 'id'), request('sort', 'desc'));
	}

}
