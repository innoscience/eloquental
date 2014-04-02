<?php

namespace Innoscience\Eloquental;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query;
use Illuminate\Support\MessageBag;
use Validator;
use Event;

/**
 * Class Eloquental
 * @package Innoscience\Eloquental
 */
abstract class Eloquental extends Model
{

	/**
	 * Building query event closure
	 *
	 * @var \Closure
	 */
	public static $buildingQueryEvent;
	/**
	 * Builder class to use
	 *
	 * @var string
	 */
	protected static $builder = 'Innoscience\Eloquental\Query\Builder';
	/**
	 * Model's default validation rules
	 *
	 * @var array
	 */
	public $rules = array();

	/**
	 * Model's default validation messages
	 *
	 * @var array
	 */
	public $customMessages = array();

	/**
	 * Attributes to purge before saving, for validation
	 *
	 * @var array
	 */
	public $autoPurge = array();

	/**
	 * Array of default orderBy sets
	 *
	 * @var array
	 */
	public $orderBy = array();

	/**
	 * Temporary rules for situational validation
	 *
	 * @var array
	 */
	protected $flashRules = array();

	/**
	 * MessageBag for validation errors
	 *
	 * @var null|\Illuminate\Support\MessageBag
	 */
	protected $validationErrors = null;

	/**
	 * Instance of the Validator class when validation used
	 *
	 * @var \Illuminate\Validation\Validator
	 */
	protected $validatorInstance = null;

	/**
	 * Specifies whether the model should skip the validation system
	 *
	 * @var bool
	 */
	protected $skipValidation = FALSE;

	/**
	 * BuildingQuery event closure
	 *
	 * @param \Closure
	 */
	public static function buildingQuery($callback) {
		Event::listen('eloquental.'.get_called_class().'.buildQuery', $callback);
	}

	/**
	 * Get the current Builder class name
	 * @return string
	 */
	public static function getBuilder() {
		return self::$builder;
	}

	/**
	 * Set the Builder class called when the Builder class is instantiated
	 *
	 * @param $builderClassName
	 */
	public static function setBuilder($builderClassName) {
		self::$builder = $builderClassName;
	}

	/**
	 * The Valdating closure called when the validate() method is executed
	 *
	 * @param $callback
	 */
	public static function validating($callback) {
		static::registerModelEvent('validating', $callback);
	}

	/**
	 * The validated closure called when the validate() method has finished successfully
	 *
	 * @param $callback
	 */
	public static function validated($callback) {
		static::registerModelEvent('validated', $callback);
	}

	/**
	 * @param \Illuminate\Database\Query\Builder $query
	 *
	 * @return \Illuminate\Database\Query\Builder
	 */
	public static function callBuildingQueryEvent($query) {
		$newQuery = Event::fire('eloquental.'.get_called_class().'.buildQuery', array($query));
		if ($newQuery) {
			$query = $newQuery[0];
		}
		return $query;
	}

	/**
	 * Merges new validation events into observable events for models
	 * @return array
	 */
	public function getObservableEvents() {
		return array_merge(parent::getObservableEvents(), array('validating', 'validated'));
	}

	/**
	 * Overrides original to add modelBuilder functionality, create a new Eloquent query builder for the model.
	 *
	 * @param  \Illuminate\Database\Query\Builder $query
	 *
	 * @return \Innoscience\Eloquental\Query\Builder|\Illuminate\Database\Eloquent\Builder|static
	 */
	public function newEloquentBuilder($query) {
		if (static::getBuilder()) {
			$builder = static::getBuilder();
			return new $builder($query);
		}

		return new Builder($query);
	}

	/**
	 * Overrides original method to add validation check and auto-purge
	 *
	 * @param array $options
	 *
	 * @return bool
	 */
	public function save(array $options = array()) {

		if ($this->rules && !$this->skipValidation && !$this->validate()) {
			return FALSE;
		}

		$this->skipValidation = FALSE;

		$this->autoPurge();

		return parent::save($options);
	}

	/**
	 * Validates model, also allows to override model's built in rules & custom messages
	 *
	 * @param array $rules
	 * @param array $customMessages
	 *
	 * @return bool
	 */
	public function validate($rules = array(), $customMessages = array()) {
		$this->skipValidation = FALSE;
		$this->validationErrors = null;
		$this->setFlashRules($rules);

		if ($this->getFlashRules()) {
			$this->getValidator()->setRules($this->getFlashRules());
			$this->getValidator()->setCustomMessages($customMessages?:$this->getCustomMessages());
		}

		if ($this->fireModelEvent('validating') === FALSE) {
			$this->clearFlashRules();
			return FALSE;
		}

		if ($this->getValidator()->fails()) {
			$this->clearFlashRules();
			$this->validationErrors = $this->getValidator()->messages();
			return FALSE;
		}

		if ($this->fireModelEvent('validated') === FALSE) {
			$this->clearFlashRules();
			return FALSE;
		}

		return TRUE;
	}

	protected function clearFlashRules() {
		if ($this->getFlashRules()) {
			$this->setFlashRules(array());
			$this->getValidator()->setRules($this->getRules(), $this->getCustomMessages());
		}
	}

	/**
	 * Get the current flash rules;
	 *
	 * @return array
	 */
	protected function getFlashRules() {
		return $this->flashRules;
	}

	/**
	 * Sets the flash rules via an array
	 *
	 * @param array $array
	 *
	 * @return $this
	 */
	protected function setFlashRules(array $array) {
		$this->flashRules = $array;

		return $this;
	}

	/**
	 * Gets the currently set rules
	 *
	 * @return array
	 */
	public function getRules() {
		return $this->rules;
	}

	/**
	 * Sets all rules
	 *
	 * @param array $array
	 *
	 * @return $this
	 */
	public function setRules(array $array) {
		$this->rules = $array;

		return $this;
	}

	/**
	 * Gets all currently set custom messages
	 *
	 * @return array
	 */
	public function getCustomMessages() {
		return $this->customMessages;
	}

	/**
	 * Sets all custom messages
	 *
	 * @param array $array
	 *
	 * @return $this
	 */
	public function setCustomMessages(array $array) {
		$this->customMessages = $array;

		return $this;
	}

	/**
	 * Retrieve's the current Validator instance
	 *
	 * @return \Illuminate\Validation\Validator
	 */
	public function getValidator() {
		if ($this->validatorInstance === null) {
			$this->validatorInstance = Validator::make($this->getAttributes(), $this->getRules(), $this->getCustomMessages());
		}

		return $this->validatorInstance;
	}

	/**
	 * Runs the auto-purge mechanism before saving.
	 *
	 * @return $this
	 */
	public function autoPurge() {
		foreach ($this->getAutoPurged() as $attribute) {
			unset($this->attributes[$attribute]);
		}

		return $this;
	}

	/**
	 * Gets the current auto purge attributes
	 *
	 * @return array
	 */
	public function getAutoPurged() {
		return $this->autoPurge;
	}

	/**
	 * Add a temporary rule to the validation stack, clears after validation
	 *
	 * @param $attribute
	 * @param $rules
	 *
	 * @return $this
	 */
//	public function flashRule($attribute, $rule) {
//		$this->flashRules[$attribute] = $rule;
//
//		return $this;
//	}

	/**
	 * Set a rule on a field in the validation stack
	 *
	 * @param $attribute
	 * @param $rules
	 *
	 * @return $this
	 */
	public function setRule($attribute, $rule) {
		$this->rules[$attribute] = $rule;

		return $this;
	}

	/**
	 * Get the current rule array
	 *
	 * @param $attribute
	 *
	 * @return mixed
	 */
	public function getRule($attribute) {
		return $this->rules[$attribute];
	}

	/**
	 * Merge an array of rules into the existing rules
	 *
	 * @param array $array
	 *
	 * @return $this
	 */
	public function mergeRules(array $array) {
		$this->rules = array_merge($this->rules, $array);

		return $this;
	}

	/**
	 * Set a custom message for a field
	 *
	 * @param $attribute
	 * @param $rules
	 *
	 * @return $this
	 */
	public function setCustomMessage($attribute, $message) {
		$this->customMessages[$attribute] = $message;

		return $this;
	}

	/**
	 * Merge an array of custom messages into the existing custom messages
	 *
	 * @param array $array
	 *
	 * @return $this
	 */
	public function mergeCustomMessages(array $array) {
		$this->customMessages = array_merge($this->customMessages, $array);

		return $this;
	}

	/**
	 * Retrieve the model's current errors in the form of a MessageBag
	 *
	 * @return MessageBag|null
	 */
	public function errors() {
		if ($this->validationErrors === null) {
			$this->validationErrors = new MessageBag;
		}

		return $this->validationErrors;
	}

	/**
	 * Skip the validation for the model, resets on save
	 *
	 * @return $this
	 */
	public function skipValidation() {
		$this->skipValidation = TRUE;

		return $this;
	}

	/**
	 * Allows an external validator to be attached
	 *
	 * @param \Illuminate\Validation\Validator $validator
	 *
	 * @return $this
	 */
	public function setValidator(\Illuminate\Validation\Validator $validator) {
		$this->validatorInstance = $validator;

		return $this;
	}

	/**
	 * gets the order by array set
	 *
	 * @return array
	 */
	public function getOrderBy() {
		return $this->orderBy;
	}

	/**
	 * Sets the orderBy order sets
	 *
	 * @param array $array
	 *
	 * @return $this
	 */
	public function setOrderBy(array $array) {
		$this->orderBy = $array;

		return $this;
	}
}
