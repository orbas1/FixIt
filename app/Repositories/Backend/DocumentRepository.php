<?php

namespace App\Repositories\Backend;

use App\Models\Document;
use Exception;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class DocumentRepository extends BaseRepository
{
    public function model()
    {
        return Document::class;
    }

    public function show($id)
    {
        try {
            return $this->model->with('permissions')->findOrFail($id);

        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $this->model->create($request->all());

            DB::commit();

            return redirect()->route('backend.document.index')->with('message', 'Document Created Successfully.');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $document = $this->model->findOrFail($id);
            $document->update($request->all());

            DB::commit();

            return redirect()->route('backend.document.index')->with('message', 'Document Updated Successfully');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $document = $this->model->findOrFail($id);
            $document->destroy($id);

            DB::commit();

            return redirect()->route('backend.document.index')->with('message', 'Document Deleted Successfully');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteAll($ids)
    {
        DB::beginTransaction();
        try {

            $this->model->whereNot('system_reserve', true)->whereIn('id', $ids)->delete();

            return back()->with('message', 'Roles Deleted Successfully');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function status($id, $status)
    {
        try {

            $document = $this->model->findOrFail($id);
            $document->update(['status' => $status]);

            return json_encode(['resp' => $document]);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
