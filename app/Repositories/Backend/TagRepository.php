<?php

namespace App\Repositories\Backend;

use App\Models\Tag;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class TagRepository extends BaseRepository
{
    public function model()
    {
        return Tag::class;
    }

    public function index()
    {

        return view('backend.tag.index');
    }

    public function create($attribute = [])
    {

        return view('backend.tag.create');
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $locale = $request['locale'] ?? app()->getLocale();
            $tag = $this->model->create(
                [
                    'name' => $request->name,
                    'description' => $request->description,
                    'type' => $request->type,
                    'status' => $request->status,
                ]
            );
            $tag->setTranslation('name', $locale, $request['name']);
            $tag->setTranslation('description', $locale, $request['description']);
            $tag->save();
            DB::commit();

            return redirect()->route('backend.tag.index')->with('message', 'Tag Created Successfully.');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id , $dataTable)
    {
        $tag = $this->model->findOrFail($id);
        return $dataTable->render('backend.tag.edit', ['tag' => $tag]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $locale = $request['locale'] ?? app()->getLocale();
            $tag = $this->model->findOrFail($id);
            $tag->setTranslation('name', $locale, $request['name']);
            $tag->setTranslation('description', $locale, $request['description']);
            $data = Arr::except($request, ['name', 'description', 'locale']);
            $tag->update($data);

            DB::commit();
            return redirect()->route('backend.tag.index')->with('success', 'Tag Updated Successfully.');

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $tag = $this->model->findOrFail($id);
            $tag->destroy($id);

            DB::commit();
            return redirect()->back()->with(['message' => 'Tag deleted successfully']);
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function status($id, $status)
    {
        try {

            $tag = $this->model->findOrFail($id);
            $tag->update(['status' => $status]);

            return json_encode(['resp' => $tag]);
        } catch (Exception $e) {

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
}
