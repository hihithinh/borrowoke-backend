<?php

namespace App\Http\Controllers\SongSelect;

use App\Http\Controllers\BaseController;
use App\Services\SongSelect\SongService;

class SongController extends BaseController
{
    protected SongService $songService;

    public function __construct()
    {
        $this->songService = resolve(SongService::class);
    }

    public function searchSongs()
    {
        $options = request()->all();

        $songs = $this->songService->searchSongs($options);

        return response()->json(
            $songs->map(function ($song) {
                return [
                    'id'         => $song->id,
                    'name'       => $song->name,
                    'has_male'   => $song->has_male,
                    'has_female' => $song->has_female,
                    'singersStr' => $song->singersStr,
                    'canSing'    => false,
                    'isLike'     => false,
                    'difficulty' => null,
                ];
            })
        );
    }
}
