<?php

namespace App\Repositories\API;

use App\Models\Zone;
use Prettus\Repository\Eloquent\BaseRepository;

class ZoneRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
    ];

    public function model()
    {
        return Zone::class;
    }
}
