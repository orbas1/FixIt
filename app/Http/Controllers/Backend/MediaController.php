<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\Backend\MediaRepository;

class MediaController extends Controller
{
    protected $repository;

    public function __construct(MediaRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show Admin Profile
     */
    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }
}
