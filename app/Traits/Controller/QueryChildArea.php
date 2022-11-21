<?php

namespace App\Traits\Controller;


use App\Models\Area;
use Illuminate\Pagination\LengthAwarePaginator;

trait QueryChildArea {
	/**
	 * @param string $query
	 * @return LengthAwarePaginator
	 */
	protected function getAllChildAreas(Area $area) {
		$childAreaIdList = [];
		foreach ($area->childAreas as $childArea) {
			$childAreaIdList[] = $childArea->id;
			$childAreaIdList = array_merge($childAreaIdList, $this->getAllChildAreas($childArea));
		}
		return $childAreaIdList;
	}
}