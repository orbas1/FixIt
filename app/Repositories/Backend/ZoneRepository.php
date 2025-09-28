<?php

namespace App\Repositories\Backend;

use App\Exports\ZoneExport;
use App\Imports\ZoneImport;
use App\Models\Zone;
use Exception;
use App\Models\Currency;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\Session;

class ZoneRepository extends BaseRepository
{
    public function model()
    {
        $this->currency = new Currency();
        return Zone::class;
    }

    public function create($attribute = [])
    {
        $locale = request('locale') ?? Session::get('locale', app()->getLocale());
        request()->merge(['locale' => $locale]);
        $currencies = $this->currency->pluck('code', 'id');
        return view('backend.zone.create' , ['currencies' => $currencies]);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $coordinates = json_decode($request?->place_points ?? '', true);
            $points = array_map(function ($coordinate) {
                return new Point($coordinate['lat'], $coordinate['lng']);
            }, $coordinates);

            if (head($points) != $points[count($points) - 1]) {
                $points[] = head($points);
            }

            $lineString = new LineString($points);
            $place_points = new Polygon([$lineString]);
            $zone = $this->model->create([
                'name' => $request->name,
                'place_points' => $place_points,
                'locations' => $coordinates,
                'status' => $request->status,
                'currency_id' => $request?->currency_id,
                'payment_methods' => $request?->payment_methods
            ]);

            $locale = $request['locale'] ?? app()->getLocale();
            $zone->setTranslation('name', $locale, $request['name']);
            $zone->save();

            DB::commit();
            return redirect()->route('backend.zone.index')->with('message', __('static.zone.created'));

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $zone = $this->model->findOrFail($id);
        $coordinates = $zone->place_points ? json_decode($zone->place_points) : null;
        $currencies = $this->currency->pluck('code', 'id');

        return view('backend.zone.edit', ['coordinates' => $coordinates, 'zone' => $zone , 'currencies' => $currencies]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $zone = $this->model->findOrFail($id);
            $locale = $request['locale'] ?? app()->getLocale();
            $zone->setTranslation('name', $locale, $request['name']);
            if (isset($request['place_points']) && ! empty($request['place_points'])) {
                $coordinates = json_decode($request['place_points'] ?? '', true);
                $points = array_map(function ($coordinate) {
                    return new Point($coordinate['lat'], $coordinate['lng']);
                }, $coordinates);

                if (head($points) != $points[count($points) - 1]) {
                    $points[] = head($points);
                }

                $lineString = new LineString($points);
                $place_points = new Polygon([$lineString]);
                unset($request['place_points']);
                $zone->place_points = $place_points;
                $zone->locations = $coordinates;
                $zone->save();
            }
            $data = Arr::except($request, ['name', 'locale']);
            $zone->update($data);

            DB::commit();
            return redirect()->route('backend.zone.index')->with('message', __('static.zone.updated'));

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $zone = $this->model->findOrFail($id);
            $zone->destroy($id);

            DB::commit();
            return redirect()->back()->with(['message' => __('static.zone.deleted')]);
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

            return back()->with('message', __('static.zone.deleted'));

        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function export($request)
    {
        try {
            $format = $request->input('format', 'xlsx');

            if ($format == 'csv') {

                return Excel::download(new ZoneExport, 'zones.csv');
            }
            return Excel::download(new ZoneExport, 'zones.xlsx');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function import($request)
    {
        try {
            $activeTab = $request->input('active_tab');

            $tempFile = null;

            if ($activeTab === 'direct-link') {

                $googleSheetUrl = $request->input('google_sheet_url');

                if (!$googleSheetUrl) {
                    throw new Exception(__('static.import.no_url_provided'));
                }

                if (!filter_var($googleSheetUrl, FILTER_VALIDATE_URL)) {
                    throw new Exception(__('static.import.invalid_url'));
                }

                $parsedUrl = parse_url($googleSheetUrl);
                preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $parsedUrl['path'], $matches);
                $sheetId = $matches[1] ?? null;
                parse_str($parsedUrl['query'] ?? '', $queryParams);
                $gid = $queryParams['gid'] ?? 0;

                if (!$sheetId) {
                    throw new Exception(__('static.import.invalid_sheet_id'));
                }

                $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

                $response = Http::get($csvUrl);

                if (!$response->ok()) {
                    throw new Exception(__('static.import.failed_to_fetch_csv'));
                }

                $tempFile = tempnam(sys_get_temp_dir(), 'google_sheet_') . '.csv';

                file_put_contents($tempFile, $response->body());
            } elseif ($activeTab === 'local-file') {
                $file = $request->file('fileImport');

                if (!$file) {
                    throw new Exception(__('static.import.no_file_uploaded'));
                }

                if ($file->getClientOriginalExtension() != 'csv') {
                    throw new Exception(__('static.import.csv_file_allow'));
                }

                $tempFile = $file->getPathname();
            } else {
                throw new Exception(__('static.import.no_valid_input'));
            }

            Excel::import(new ZoneImport(), $tempFile);

            if ($activeTab === 'google_sheet' && file_exists($tempFile)) {
                unlink($tempFile);
            }

            return redirect()->back()->with('success', __('static.import.csv_file_import'));
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
