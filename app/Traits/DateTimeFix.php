<?php
namespace App\Traits;


use App\Helpers\DateUtility;

trait DateTimeFix {
	public function getCreatedAtAttribute($value) {
		return DateUtility::tryParsedDateFromFormat($value);
	}

	public function getUpdatedAtAttribute($value) {
		return DateUtility::tryParsedDateFromFormat($value);
	}

	public function getDeletedAtAttribute($value) {
		return DateUtility::tryParsedDateFromFormat($value);
	}

	protected function asDateTime($value) {
		try {
			return parent::asDateTime($value);
		} catch (\Exception $e) {
			return DateUtility::tryParsedDateFromFormat($value);
		}
	}
}
