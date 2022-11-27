<?php

namespace App\Repositories\SongSelect;

use App\Models\SongSelect\SongModel;
use App\Repositories\BaseRepository;

class SongRepository extends BaseRepository
{
    /** @var SongModel */
    protected $model;

    public function __construct()
    {
        $this->model = resolve(SongModel::class);
    }

    public function getByOptions(array $options) {
        $query = $this->model
            ->newQuery()
            ->from('songs as so')
            ->select('so.*');

        return $this->getByOption($options, $query);
    }
}
