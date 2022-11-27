<?php

namespace App\Models\SongSelect;

use App\Models\BaseModel;

class SongModel extends BaseModel
{
    protected $table = 'songs';

    public function singers() {
        return $this->hasManyThrough(
            SingerModel::class,
            SongSingerModel::class,
            'singer_id',
            'id',
            'id',
            'song_id'
        );
    }
}
