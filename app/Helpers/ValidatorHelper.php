<?php


namespace App\Helpers;


use App\Enums\EDateFormat;
use App\Rules\ValidPhoneNumber;
//use App\Rules\ValidVietnamPBXNumber;
use App\Rules\ValidVietnamPhoneNumber;
use Illuminate\Support\Arr;

class ValidatorHelper {
    private static function requireRule($option) {
        $required = Arr::get($option, 'required', false);
        $requiredIf = Arr::get($option, 'required_if', null);
        $requiredWith = (array)Arr::get($option, 'required_with', []);
        $requiredWithout = (array)Arr::get($option, 'required_without', []);
        $requiredWithoutAll = (array)Arr::get($option, 'required_without_all', []);
        if (!$required
			&& empty($requiredIf)
			&& empty($requiredWith)
			&& empty($requiredWithOut)
			&& empty($requiredWithoutAll)) {
        	return ['nullable'];
		}

        $result = [];
        if (!empty($requiredIf)) {
        	$result[] = "required_if:$requiredIf";
		}
        if (!empty($requiredWith)) {
			$result[] = 'required_with:' . implode(',', $requiredWith);
		}
        if (!empty($requiredWithout)) {
			$result[] = 'required_without:' . implode(',', $requiredWithout);
		}
		if (!empty($requiredWithoutAll)) {
			$result[] = 'required_without_all:' . implode(',', $requiredWithoutAll);
		}
		if (!empty($result)) {
			return $result;
		}

		return ['required'];
    }

    public static function nameRule(array $option = []) {
        $maxLength = Arr::get($option, 'max', 60);
        return [
            'bail',
            ...self::requireRule($option),
            "max:$maxLength",
        ];
    }

    public static function emailRule(array $option = []) {
        $maxLength = Arr::get($option, 'max', 60);
        return [
            'bail',
            ...self::requireRule($option),
            'email:rfc,dns',
            "max:$maxLength",
        ];
    }

    public static function dateOfBirthRule(array $option = []) {
        return [
            'bail',
            ...self::requireRule($option),
            'date_format:' . Arr::get($option, 'format', EDateFormat::STANDARD_DATE_FORMAT)
        ];
    }

    public static function standardDateRule(array $option = []) {
        return self::dateOfBirthRule($option);
    }

    public static function phoneRule(array $option = []) {
        return [
            'bail',
            ...self::requireRule($option),
            'min:' . (Arr::get($option, 'pbxNumber', false) ? 8 : 10),
            'max:12',
            new ValidPhoneNumber(),
            // check sdt tong dai
            new ValidVietnamPhoneNumber(Arr::get($option, 'pbxNumber', false)),
        ];
    }

    public static function numberRule(array $option = []) {
        $rules = [
            'bail',
            ...self::requireRule($option),
            'numeric',
        ];

        $min = Arr::get($option, 'min');
        if (!is_null($min)) {
            $rules[] = "min:$min";
        }

        $max = Arr::get($option, 'max');
        if (!is_null($max)) {
            $rules[] = "max:$max";
        }
        return $rules;
    }

    public static function imageRule(array $option = []) {
        return [
            'bail',
            ...self::requireRule($option),
            'file',
            'mimes:jpeg,bmp,png',
            'dimensions:min_width=100,min_height=100',
        ];
    }

    public static function descriptionRule(array $option = []) {
        return [
            'bail',
            ...self::requireRule($option),
        ];
    }

    public static function timeRule(array $option = []) {
        return [
            'bail',
            ...self::requireRule($option),
            'date_format:' . Arr::get($option, 'format', 'H:i'),
        ];
    }

    public static function booleanRule(array $option = []) {
        return [
            'bail',
            ...self::requireRule($option),
            'boolean'
        ];
    }

	public static function urlRule(array $option = []) {
		return [
			'bail',
			...self::requireRule($option),
			'url'
		];
	}

    public static function arrayRule(array $option = []) {
        $result = [
            'bail',
            ...self::requireRule($option),
            'array'
        ];

		$maxLength = Arr::get($option, 'max');
        if ($maxLength) {
			$result[] = "max:$maxLength";
		}

		$minLength = Arr::get($option, 'min');
		if ($minLength) {
			$result[] = "min:$minLength";
		}

		return $result;
    }

    public static function commissionContentRule(array $option = []) {
        $maxLength = Arr::get($option, 'max', 512);
        return [
            'bail',
            ...self::requireRule($option),
            "max:$maxLength"
        ];
    }

    public static function address(array $option = []) {
        $maxLength = Arr::get($option, 'max', 60);
        return [
            'bail',
            ...self::requireRule($option),
            "max:$maxLength",
        ];
    }

    public static function passwordRule(array $option = []) {
        $maxLength = Arr::get($option, 'max', 20);
        $minLength = Arr::get($option, 'min', 6);
        $result = [
            'bail',
            self::requireRule($option),
            "max:$maxLength",
            "min:$minLength",
            "string",
            function($attribute, $value, $fail) {
                $regex = '/.*[A-Z].*/';
                if (!preg_match($regex, $value)) {
                    $fail(__('validation.custom.password'));
                }
            },
        ];
        if (Arr::get($option, 'confirmed', false)) {
        	$result[] = 'confirmed';
		} elseif (Arr::has($option, 'confirm_value')) {
			$result[] = function($attribute, $value, $fail) use ($option) {
				if ($value !== Arr::get($option, 'confirm_value')) {
					$fail(__('validation.confirmed', [
						'attribute' => __('validation.attributes.password_confirmation'),
					]));
				}
			};
		}
        return $result;
    }
}
