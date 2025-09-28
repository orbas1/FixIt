<?php

namespace App\Repositories\Backend;

use App\Exports\CommissionHistoriesExport;
use App\Models\CommissionHistory;
use Prettus\Repository\Eloquent\BaseRepository;
use Exception;
use Maatwebsite\Excel\Facades\Excel;

class CommissionRepository extends BaseRepository
{
    public function model()
    {
        return CommissionHistory::class;
    }

    public function index()
    {
        return view('backend.commission.index');
    }

    public function create($attribute = [])
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function store($request)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update($request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function export($request)
    {
        try {
            $format = $request->input('format', 'xlsx');

            if ($format == 'csv') {

                return Excel::download(new CommissionHistoriesExport, 'commission_histories.csv');
            }
            return Excel::download(new CommissionHistoriesExport, 'commission_histories.xlsx');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
