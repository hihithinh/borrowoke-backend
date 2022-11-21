<?php
namespace App\Traits;


trait ModelDataParse {
	protected function setJsonValue(string $colName, $value) {
		if (!empty($value)) {
			$this->attributes[$colName] = json_encode($value);
		} else {
			$this->attributes[$colName] = null;
		}
	}

	protected function getJsonValue($value, $assoc = false) {
		return empty($value) ? null : json_decode($value, $assoc);
	}

	protected function setArrayValue(string $colName, $value) {
		if (empty($value)) {
			$value = [];
		}
		$this->attributes[$colName] = '{' . implode(',', $value) . '}';
	}

	protected function getArrayValue($value) {
		$value = preg_replace('/^{/', '[', $value);
		$value = preg_replace('/}$/', ']', $value);
		$value = $this->getJsonValue($value);
		return array_map(function ($item) {
			return $this->getJsonValue($item);
		}, $value);
	}

	protected function getNumericValue($value) {
		if (is_null($value)) {
			return null;
		}
		$floatVal = (float)$value;
		$intVal = (int)$value;
		return $floatVal != $intVal ? $floatVal : $intVal;
	}

	public function getStatusAttribute($value) {
		return $this->getNumericValue($value);
	}

	public function getTypeAttribute($value) {
		return $this->getNumericValue($value);
	}
}
