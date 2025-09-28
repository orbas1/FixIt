<?php

namespace App\Repositories\Backend;

use Exception;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class CurrencyRepository extends BaseRepository
{
    protected $countries;

    public function model()
    {
        $this->countries = new Country();

        return Currency::class;
    }

    public function index()
    {
        return view('backend.currency.index');
    }

    public function create($attribute = [])
    {
        return view('backend.currency.create', ['code' => $this->countries->pluck('currency_code', 'currency_code')]);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $currency = $this->model->create([
                'code' => $request->code,
                'symbol' => $request->symbol,
                'symbol_position' => $request->symbol_position,
                'no_of_decimal' => $request->no_of_decimal,
                'exchange_rate' => $request->exchange_rate,
                'status' => $request->status,
            ]);

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $currency->addMediaFromRequest('image')->toMediaCollection('currency');
            }

            DB::commit();

            return redirect()->route('backend.currency.index')->with('message', 'Currency Created Successfully.');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $currency = $this->model->findOrFail($id);
        return view('backend.currency.edit', [
            'code' => $this->countries->pluck('currency_code', 'currency_code'),
            'currency' => $currency,
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $currency = $this->model->findOrFail($id);
            $currency->update($request->all());

            if ($request->file('image') && $request->file('image')->isValid()) {
                $currency->clearMediaCollection('currency');
                $currency->addMediaFromRequest('image')->toMediaCollection('currency');
            }

            DB::commit();

            return redirect()->route('backend.currency.index')->with('success', 'Currency Updated Successfully.');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $currency = $this->model->findOrFail($id);
            $currency->destroy($id);

            DB::commit();
            return redirect()->route('backend.currency.index')->with('message', 'Currency Deleted Successfully');

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function status($id, $status)
    {
        try {

            $currency = $this->model->findOrFail($id);
            $currency->update(['status' => $status]);

            return json_encode(['resp' => $currency]);
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

    public function getSymbol($request)
    {
        $country = Country::where('currency_code', $request->code)->first();

        if ($country) {
            return response()->json(['symbol' => $country->currency_symbol]);
        } else {
            return response()->json(['symbol' => '']);
        }
    }
}
