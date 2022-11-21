<?php

namespace App\Traits;


use App\Helpers\DateUtility;
use stdClass;

trait ModelStdConvert {
	/**
	 * Convert model to stdObject.
	 *
	 * @param  array $fill
	 * @return stdObject
	 */
	public function toStd($fill = ['*'])
	{
		// backup visible
		$visible = $this->visible;

		if ($fill == ['*']) $fill = array_keys($this->getAttributes());

		// make sure we get all the fields we need
		$this->setVisible($fill);

		$std = (object) $this->attributesToArray();

		// restore visible
		$this->setVisible($visible);

		return $std;
	}

	/**
	 * Make a model from stdObject.
	 *
	 * @param  stdClass $std
	 * @param  array    $fill
	 * @param  boolean  $exists
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public static function newFromStd(stdClass $std, $fill = ['*'], $exists = true)
	{
		$instance = new static;

		$values = ($fill == ['*'])
			? (array) $std
			: array_intersect_key( (array) $std, array_flip($fill));

		// fill attributes and original arrays
		$instance->setRawAttributes($values, true);

		$instance->exists = $exists;

		return $instance;
	}
}
