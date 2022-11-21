<?php

namespace App\Models;

use App\Traits\DateTimeFix;
use App\Traits\ModelDataParse;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use DateTimeFix;
    use ModelDataParse;

    public function getMetaAttribute($value) {
        return $this->getJsonValue($value);
    }

    public function setMetaAttribute($value) {
        $this->setJsonValue('meta', $value);
    }

    public function getTranslation($languageCode, $key, $default = null) {
        if (empty($this->translationClass)) {
            return $default;
        }
        $translation = $this->translations->where('language_code', $languageCode)->first();
        if (empty($translation)) {
            return $default;
        }
        return empty($translation->{$key}) ? $default : $translation->{$key};
    }
}
