<?php


namespace App\Helpers;


use App\Enums\EInternalUserRole;
use App\Rules\ValidPhoneNumber;
use Illuminate\Support\Arr;

class BusinessValidatorHelper {
    public static function roleRule(array $option = []) {
        $required = Arr::get($option, 'required', false);
        return [
            'bail',
            $required ? 'required' : 'nullable',
            'numeric',
            function ($attribute, $value, $fail) {
                if (!EInternalUserRole::isValid($value)) {
                    $fail(__('business-validation.role.invalid'));
                }
            },
        ];
    }

    public static function branchIdRule(array $option = []) {
        $required = Arr::get($option, 'required', false);
        return [
            'bail',
            $required ? 'required' : 'nullable',
            'numeric',
            function ($attribute, $value, $fail) {
                $branchRepository = resolve('App\Repositories\BranchRepository');
                $branch = $branchRepository->getById($value);
                if (empty($branch)) {
                    $fail(__('business-validation.branch.not_exist'));
                }
            },
        ];
    }

    public static function passwordRule(array $option = []) {
        $required = Arr::get($option, 'required', false);
        return [
            'bail',
            $required ? 'required' : 'nullable',
            'min:6',
            'max:20',
            function($attribute, $value, $fail) {
                $regex = '/.*[A-Z].*/';
                if (!preg_match($regex, $value)) {
                    $fail(__('validation.custom.regex_password', [
                        'attribute' => __('back/change-password.password_new')
                    ]));
                }
            },
        ];
    }

    public static function passwordOldRule(array $option = []) {
        $required = Arr::get($option, 'required', false);
        return [
            'bail',
            $required ? 'required' : 'nullable',
        ];
    }

    public static function passwordConfirmRule(array $option = []) {
        $required = Arr::get($option, 'required', false);
        return [
            'bail',
            $required ? 'required' : 'nullable',
            'required_with:password',
            'same:password',
            function($attribute, $value, $fail) {
                $regex = '/.*[A-Z].*/';
                if (!preg_match($regex, $value)) {
                    $fail(__('validation.custom.regex_password', [
                        'attribute' => __('back/change-password.password_confirm')
                    ]));
                }
            },
        ];
    }

    public static function content(array $option = []) {
        $required = Arr::get($option, 'required', false);
        $maxLength = Arr::get($option, 'max', 99999999);
        return [
            'bail',
            "max:$maxLength",
            $required ? 'required' : 'nullable',
        ];
    }
}
