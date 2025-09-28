<?php

namespace App\Repositories\Backend;

use App\Enums\RoleEnum;
use App\Exports\AddOnServiceExport;
use App\Helpers\Helpers;
use App\Imports\AddOnServiceImport;
use App\Models\Service;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;
use Maatwebsite\Excel\Facades\Excel;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\Session;

class AdditionalServiceRepository extends BaseRepository
{
    public function model()
    {
        return Service::class;
    }

    public function index()
    {
        return view('backend.additional-service.index');
    }

    public function create($attributes = [])
    {

        $locale = request('locale') ?? Session::get('locale', app()->getLocale());
        request()->merge(['locale' => $locale]);
        return view('backend.additional-service.create', [
            'services' => $this->getServices('service'),
        ]);
    }


    public function store($request)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();

            $additionalService = $this->model->create([
                'title' => $request->title,
                'price' => $request->price,
                'parent_id' => $request->parent_id,
                'user_id' => auth()->user()->hasRole(RoleEnum::PROVIDER) ? auth()->id()  : Service::find($request['parent_id'])->user_id,
            ]);

            if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
                $additionalService->addMedia($request->file('thumbnail'))->withCustomProperties(['language' => $locale])->toMediaCollection('thumbnail');
            }
            $additionalService->setTranslation('title', $locale, $request['title']);
            $additionalService->save();
            DB::commit();
            return  to_route('backend.additional-service.index')->with('message', __('static.additional_service.created'));

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }


    public function edit($id)
    {
        $additionalService = $this->model->findOrFail($id);
        return view('backend.additional-service.edit', [
            'additionalService' => $additionalService,
            'services' => $this->getServices($additionalService),
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();
            $additionalService = $this->model->findOrFail($id);
            $additionalService->setTranslation('title', $locale, $request['title']);

            $additionalService->update([
                'price' => $request['price'],
                'status' => $request['status'],
                'parent_id' => $request['parent_id'],
                'user_id' => auth()->user()->hasRole(RoleEnum::PROVIDER) ? auth()->id()  : Service::find($request['parent_id'])->user_id
            ]);

            if ($request['thumbnail']) {
                $existingMedia = $additionalService->getMedia('thumbnail')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingMedia as $media) {
                    $media->delete();
                }
                $additionalService->addMedia($request['thumbnail'])->withCustomProperties(['language' => $locale])->toMediaCollection('thumbnail');
            }

            DB::commit();
            return redirect()->route('backend.additional-service.index')->with('message', __('static.additional_service.updated'));

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function status($id, $status)
    {
        try {
            $additionalService = $this->model->findOrFail($id);
            $additionalService->update(['status' => $status]);

            return json_encode(['resp' => $additionalService]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $additionalService = $this->model->findOrFail($id);
            $additionalService->destroy($id);

            return redirect()->route('backend.additional-service.index')->with('message', __('static.additional_service.deleted'));
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function getServices($additionalService)
    {
        $services = $this->model->whereNull('parent_id');
        if(Helpers::getCurrentRoleName() == RoleEnum::PROVIDER){
            $services->where('user_id', auth()->user()->id);
        }
        $serviceList = $services->get();
        if ($additionalService && Request::is('backend/additional-service/*/edit')) {
            return $serviceList->except($additionalService->id);
        }
        return $serviceList;
    }
    public function export($request)
    {
        try {
            $format = $request->input('format', 'xlsx');

            if ($format == 'csv') {

                return Excel::download(new AddOnServiceExport, 'add-on-services.csv');
            }
            return Excel::download(new AddOnServiceExport, 'add-on-services.xlsx');
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

            Excel::import(new AddOnServiceImport(), $tempFile);

            if ($activeTab === 'google_sheet' && file_exists($tempFile)) {
                unlink($tempFile);
            }

            return redirect()->back()->with('success', __('static.import.csv_file_import'));
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
