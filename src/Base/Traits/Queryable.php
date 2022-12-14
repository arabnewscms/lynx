<?php
namespace Lynx\Base\Traits;

trait Queryable {

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
		$query = $this->query(new $this->entity)->orderBy(request('orderBy', 'id'), request('sort', 'desc'));
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
		return $this->beforeShow(new $this->entity)->orderBy(request('orderBy', 'id'), request('sort', 'desc'));
	}

}