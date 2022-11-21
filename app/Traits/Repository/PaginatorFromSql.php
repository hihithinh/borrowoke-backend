<?php

namespace App\Traits\Repository;


use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

trait PaginatorFromSql {
	/**
	 * @param string $query
	 * @return LengthAwarePaginator
	 */
	protected function getPaginatorFromSql(string $query) {
		$totalSql = <<<sql
select count(*)
from ($query) temp
sql;

		$page = (int)request('page', 1);
		if ($page <= 0) {
			$page = 1;
		}
		$pageSize = (int)request('pageSize');
		if ($pageSize <= 0) {
			$pageSize = 1;
		}
		$offset = ($page - 1) * $pageSize;

		$query .= " limit $pageSize offset $offset";

		$items = DB::select($query);
		$total = DB::select($totalSql)[0]->count;
		$result = new LengthAwarePaginator($items, $total, $pageSize, $page);
		$result->setCollection(collect(DB::select($query)));

		return $result;
	}
}