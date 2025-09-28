<?php

namespace App\Repositories\Backend;

use Exception;
use App\Models\Tax;
use App\Models\Zone;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Prettus\Repository\Eloquent\BaseRepository;

class TaxRepository extends BaseRepository
{
    public function model()
    {
        return Tax::class;
    }

    public function index()
    {
        return view('backend.tax.index');
    }

    public function create($attribute = [])
    {
        $locale = request('locale') ?? Session::get('locale', app()->getLocale());
        request()->merge(['locale' => $locale]);
        $zones = Zone::pluck('name', 'id')->toArray() ?? '';
            return view('backend.tax.create', [
                'zones' => $zones,
                'default_zones' => old('zones', []),
            ]);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $locale = $request['locale'] ?? app()->getLocale();
            $tax = $this->model->create(
                [
                    'name' => $request->name,
                    'rate' => $request->rate,
                    'status' => $request->status,
                    'zone_id' => $request->zone_id,
                ]
            );
            
            $tax->setTranslation('name', $locale, $request['name']);
            $tax->save();
            DB::commit();

            return redirect()->route('backend.tax.index')->with('message', 'Tax Created Successfully.');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $tax = $this->model->findOrFail($id);
        $allZones = Zone::pluck('name', 'id')->toArray() ?? []; 
        $defaultZones = $tax->zones ? $tax->zones->pluck('id')->toArray() : [];
        return view('backend.tax.edit', [
            'tax' => $tax,
            'zones' => $allZones,
            'default_zones' => $defaultZones,
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $locale = $request['locale'] ?? app()->getLocale();
            $tax = $this->model->findOrFail($id);
            $tax->setTranslation('name', $locale, $request['name']);
            $data = Arr::except($request->all(), ['name', 'locale']);
            $tax->update($data);

            DB::commit();
            return redirect()->route('backend.tax.index')->with('success', 'Tax Updated Successfully.');

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $tax = $this->model->findOrFail($id);
            $tax->destroy($id);

            DB::commit();

            return redirect()->back()->with(['message' => 'Tax deleted successfully']);
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function status($id, $status)
    {
        try {

            $tax = $this->model->findOrFail($id);
            $tax->update(['status' => $status]);

            return json_encode(['resp' => $tax]);
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
