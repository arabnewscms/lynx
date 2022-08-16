<?php
namespace Lynx\Base\Traits;
use Illuminate\Support\Facades\Gate;

trait Policy {
	/**
	 * define all methods from policy
	 * @return void
	 */
	public
function definePolicy():void {
		if (empty($this->policy_key)) {
			$this->policy_key = \Str::random(4);
		}
		if (class_exists($this->policy)) {
			// Check If Methods exists in Policy To Initial features
			if (method_exists($this->policy, 'viewAny')) {
				Gate::define('viewAny-'.$this->policy_key, $this->policy.'@viewAny');
			}

			if (method_exists($this->policy, 'create')) {
				Gate::define('create-'.$this->policy_key, $this->policy.'@create');
			}

			if (method_exists($this->policy, 'view')) {
				Gate::define('view-'.$this->policy_key, $this->policy.'@view');
			}

			if (method_exists($this->policy, 'update')) {
				Gate::define('update-'.$this->policy_key, $this->policy.'@update');
			}

			if (method_exists($this->policy, 'delete')) {
				Gate::define('delete-'.$this->policy_key, $this->policy.'@delete');
			}
			if (method_exists($this->policy, 'forceDelete')) {
				Gate::define('forceDelete-'.$this->policy_key, $this->policy.'@forceDelete');
			}
			if (method_exists($this->policy, 'restore')) {
				Gate::define('restore-'.$this->policy_key, $this->policy.'@restore');
			}
		}
	}
}