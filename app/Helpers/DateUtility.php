<?php

namespace App\Helpers;


use App\Enums\EDateFormat;
use Carbon\Carbon;

class DateUtility {
	public static function tryParsedDateFromFormat($date, $tz = null, $from_format = null) {
		if (empty($date)) {
			return null;
		}

		if (empty($from_format)) {
			$from_formats = [EDateFormat::MODEL_DATE_FORMAT, EDateFormat::DATE_FORMAT_WITHOUT_MICROSECOND];
		} else {
			$from_formats = [$from_format];
		}

		$type = gettype($date);
		$tmp = $date;
		if ($type === 'string') {
			foreach ($from_formats as $format) {
				try {
					return Carbon::createFromFormat($format, $date)->setTimezone($tz);
				} catch (\Exception $e) {
					// logger('date format error', compact('date', 'e'));
				}
			}
			return null;
		}
		if ($type === 'object' && !$date instanceof Carbon && !$date instanceof \Illuminate\Support\Carbon) {
			// logger('not a date object', compact('date', 'from_format', 'to_formal'));
			return null;
		}
		if ($type === 'integer') {
			return Carbon::createFromTimestampUTC($date)->setTimezone($tz);
		}

		if (empty($tmp)) {
			return '';
		}
		return $tmp->setTimezone($tz);
	}

    public static function hasTimePart(String $date, $from_format = null) {
        if (Carbon::hasFormat($date, $from_format)) {
            return false;
        } else {
            return true;
        }
    }

	/**
	 * @param \Illuminate\Support\Carbon|string $fromTime
	 * @param \Illuminate\Support\Carbon|string $toTime
	 * @param int $slotDurationInMinutes
	 * @return array
	 */
	public static function splitTimeToSlot($fromTime, $toTime, $slotDurationInMinutes = 60) {
		if (!($fromTime instanceof \Illuminate\Support\Carbon) && !($fromTime instanceof Carbon)) {
			$fromTime = \Illuminate\Support\Carbon::parse($fromTime);
		}
		if (!($toTime instanceof \Illuminate\Support\Carbon) && !($toTime instanceof Carbon)) {
			$toTime = \Illuminate\Support\Carbon::parse($toTime);
		}

		$numOfSlot = floor($fromTime->diffInMinutes($toTime) / $slotDurationInMinutes);
		$slot = [];
		for ($i = 0; $i < $numOfSlot; $i++) {
			$slot[] = [
				'fromTime' => $fromTime->format('c'),
				'toTime' => $fromTime->addMinutes($slotDurationInMinutes)->format('c'),
				'active' => false
			];
		}

		return $slot;
	}

	public static function toStandardDateFormat($date, string $fromFormat = null) {
	    if (is_null($date)) {
	        return null;
        }

	    if ($date instanceof Carbon) {
	        return $date->format(EDateFormat::STANDARD_DATE_FORMAT);
        }

	    $d = self::tryParsedDateFromFormat($date, null, $fromFormat);
	    return $d->format(EDateFormat::STANDARD_DATE_FORMAT);
    }
}
