<?php

namespace App\Repositories\Backend;

use App\Enums\RoleEnum;
use App\Exports\ServicePackageExport;
use App\Helpers\Helpers;
use App\Imports\ServicePackageImport;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use DateTime;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Maatwebsite\Excel\Facades\Excel;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\Session;

class ServicePackageRepository extends BaseRepository
{
    protected $service;

    protected $user;

    public function model()
    {
        $this->service = new Service();
        $this->user = new User();

        return ServicePackage::class;
    }

    public function index()
    {
        return view('backend.service-package.index');
    }

    protected function getProviders()
    {
        return $this->user->role('provider')->get();
    }

    public function create($attributes = [])
    {
        $locale = request('locale') ?? Session::get('locale', app()->getLocale());
        request()->merge(['locale' => $locale]);
        return view('backend.service-package.create', [
            'providers' => $this->getProviders(),
        ]);
    }

    public function getProviderServices($request)
    {
        $language = app()->getLocale();
        $data = $this->service->where('user_id', $request->provider_id)
                              ->where('id', '!=', $request->service_id)
                              ->whereNull('parent_id')
                              ->with('media')
                              ->get()
                              ->map(function ($service) {
                                return [
                                    'id' => $service->id,
                                    'title' => $service->title,
                                    'media' => $service->media->map(function ($media) {
                                        return [
                                            'id' => $media->id,
                                            'original_url' => $media->getUrl(),
                                        ];
                                    }),
                                ];
                            });
        return response()->json($data);
    }

    public function isProviderCanCreate()
    {
        if (Helpers::isUserLogin()) {
            $isAllowed = true;
            $roleName = Helpers::getCurrentRoleName();
            if ($roleName == RoleEnum::PROVIDER) {
                $isAllowed = false;
                $provider = Auth::user();
                $maxItems = $this->model->where('provider_id', Auth::user()?->id)?->whereNUll('deleted_at')?->count() ?? 0;
                if (Helpers::isModuleEnable('Subscription')) {
                    if (function_exists('isPlanAllowed')) {
                        $isAllowed = isPlanAllowed('allowed_max_service_packages', $maxItems, $provider?->id);
                    }
                }

                if (!$isAllowed) {
                    $settings = Helpers::getSettings();
                    $max_service_packages = $settings['default_creation_limits']['allowed_max_service_packages'];
                    if ($max_service_packages > $maxItems) {
                        $isAllowed = true;
                    }
                }
            }

            return $isAllowed;
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            if ($this->isProviderCanCreate()) {
                $locale = $request->locale ?? app()->getLocale();
                [$start_date, $end_date] = explode(' to ', $request->start_end_date);
                $start_date = DateTime::createFromFormat('d-m-Y', $start_date)->format('Y-m-d');
                $end_date = DateTime::createFromFormat('d-m-Y', $end_date)->format('Y-m-d');
                $service_package = $this->model->create([
                    'title' => $request->title,
                    'hexa_code' => $request->hexa_code,
                    'bg_color' => $request?->bg_color,
                    'price' => $request->price,
                    'discount' => $request->discount,
                    'description' => $request->description,
                    'is_featured' => $request->is_featured,
                    'provider_id' => $request->provider_id,
                    'started_at' => $start_date,
                    'ended_at' => $end_date,
                    'status' => $request->status,
                ]);

                if (isset($request->service_id)) {
                    if (in_array('all', $request->service_id)) {
                        $allServices = Service::pluck('id')->toArray();
                        $request->merge(['service_id' => $allServices]);
                    }
                    $service_package->services()->attach($request->service_id);
                    $service_package->services;
                }

                if ($request->hasFile('image')) {
                    $service_package->addMediaFromRequest('image')->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                }

                $service_package->setTranslation('title', $locale, $request['title']);
                $service_package->setTranslation('description', $locale, $request['description']);
                $service_package->save();
                
                DB::commit();

                return redirect()->route('backend.service-package.index')->with('message', 'Service-Package Created Successfully.');
            }

            throw new Exception(__('static.not_allow_for_creation'), 400);
        } catch (Exception $e) {

            DB::rollback();
            throw $e;
        }
    }

    public function edit($id)
    {
        $service_package = $this->model->findOrFail($id);
        return view('backend.service-package.edit', [
            'services' => $this->getServices($service_package),
            'service_package' => $service_package,
            'default_services' => $this->getDefaultServices($service_package),
            'providers' => $this->getProviders(),
        ]);
    }

    public function getDefaultServices($service_package)
    {
        $services = [];
        foreach ($service_package->services as $service) {
            $services[] = $service->id;
        }
        $services = array_map('strval', $services);

        return $services;
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();
            if (!is_null($request->start_end_date)) {
                [$start_date, $end_date] = explode(' to ', $request->start_end_date);
                $start_date = \Carbon\Carbon::createFromFormat('d-m-Y', $start_date)->format('Y-m-d');
                $end_date = \Carbon\Carbon::createFromFormat('d-m-Y', $end_date)->format('Y-m-d');
                $request->merge(['started_at' => $start_date, 'ended_at' => $end_date]);
            }
            $service_package = $this->model->findOrFail($id);
            $service_package->setTranslation('title', $locale, $request['title']);
            $service_package->setTranslation('description', $locale, $request['description']);
            $data = Arr::except($request->all(), ['title', 'description', 'start_end_date']);

            $service_package->update($data);

            if (isset($request->service_id)) {

                if (in_array('all', $request->service_id)) {
                    $allServices = Service::pluck('id')->toArray();
                    $request->merge(['service_id' => $allServices]);
                }

                $service_package->services()->sync($request->service_id);
            }

            if ($request->file('image')) {
                $existingThumbnail = $service_package->getMedia('image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingThumbnail as $media) {
                    $media->delete();
                }
                $service_package->addMediaFromRequest('image')->withCustomProperties(['language' => $locale])->toMediaCollection('image');
            }

            DB::commit();

            return redirect()->route('backend.service-package.index')->with('message', 'Service-Package Updated Successfully.');
        } catch (Exception $e) {

            DB::rollback();
            throw $e;
        }
    }

    public function status($id, $status)
    {
        try {
            $service_package = $this->model->findOrFail($id);
            $service_package->update([
                'status' => $status,
            ]);

            return json_encode(['resp' => $service_package]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function destroy($id)
    {
        try {

            $service_package = $this->model->findOrFail($id);
            $service_package->services()->detach();
            $service_package->destroy($id);

            return redirect()->route('backend.service-package.index')->with('message', 'Service-Package Deleted Successfully');
        } catch (Exception $e) {

            throw $e;
        }
    }

    private function getServices($service_package)
    {
        if (FacadesRequest::is('backend/service-package/create')) {
            return $this->service->get();
        } else {
            return $this->service->get()->except($service_package->id);
        }
    }
    public function export($request)
    {
        try {
            $format = $request->input('format', 'xlsx');

            if ($format == 'csv') {

                return Excel::download(new ServicePackageExport, 'service-packages.csv');
            }
            return Excel::download(new ServicePackageExport, 'service-packages.xlsx');
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

            Excel::import(new ServicePackageImport(), $tempFile);

            if ($activeTab === 'google_sheet' && file_exists($tempFile)) {
                unlink($tempFile);
            }

            return redirect()->back()->with('success', __('static.import.csv_file_import'));
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

}
