<?php

namespace App\Services\SongSelect;

use App\Models\SongSelect\SongModel;
use App\Repositories\SongSelect\SongRepository;
use App\Services\BaseService;

class SongService extends BaseService
{
    protected SongRepository $songRepository;

    public function __construct()
    {
        $this->songRepository = resolve(SongRepository::class);
    }

    public function getByOptions(array $options) {
        return $this->songRepository->getByOptions($options);
    }

    /**
     * get
     *
     * @param array $options
     *
     * @return bool|\Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|int|mixed|object|null
     */
    public function searchSongs(array $options) {
        $songs = $this->songRepository->getByOptions([

        ]);

        $songs->each(function (SongModel $song) {
            $song->singersStr = implode(
                ', ',
                $song->singers->pluck('name')->toArray()
            );
        });

        return $songs;
    }
}
