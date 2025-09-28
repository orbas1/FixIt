<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SubscribeDataTable;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\SubscribeRepository;

class SubscribeController extends Controller
{
    public $repository;

    public function __construct(SubscribeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(SubscribeDataTable $dataTable)
    {
        return $this->repository->index($dataTable);
    }
}