<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ActivityDataTable;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\ActivityLogRepository;


class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(ActivityLogRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(ActivityDataTable $dataTable)
    {
        return $dataTable->render('backend.system-tool.activity-log');
    }

    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }

    public function deleteAll()
    {
        return $this->repository->deleteAll();
    }


}
