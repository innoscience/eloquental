<?php

namespace Innoscience\Eloquental\Query;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class Builder extends EloquentBuilder {

	/**
	 * The model being queried.
	 *
	 * @var \Innoscience\Eloquental\Eloquental
	 */
	 protected $model;

	/**
	 * Get the hydrated models without eager loading.
	 *
	 * @param  array  $columns
	 * @return array|static[]
	 */
	public function getModels($columns = array('*'))
	{
		$model = $this->model;

		$this->query = $model::callBuildingQueryEvent($this->query);

		if (!is_a($this->query, 'Illuminate\Database\Query\Builder')) {
			throw new \Exception("\$query must be an instance of \\Illuminate\\Database\\Query\\Builder");
		}

		$this->builderOrderBy();

		return parent::getModels($columns);
	}

	/**
	 * Applies $model->orderBy clause if its present and no ordering has been set on the query
	 *
	 * @return $this
	 */
	public function builderOrderBy() {

		if ($this->model->getOrderBy() && !$this->query->orders) {
			$orderByClauses = $this->model->getOrderBy();
			for ($clauseCount = 0; $clauseCount <= count($orderByClauses) % 2; $clauseCount=+2) {
				$this->query->orderBy($orderByClauses[$clauseCount], isset($orderByClauses[$clauseCount+1]) ?$orderByClauses[$clauseCount+1] : 'asc');
			}
		}
		return $this;
	}

}