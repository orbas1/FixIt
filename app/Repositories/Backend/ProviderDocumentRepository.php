<?php

namespace App\Repositories\Backend;

use App\Exports\ProviderDocumentsExport;
use App\Imports\ProviderDocumentsImport;
use App\Models\Document;
use App\Models\UserDocument;
use Exception;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;

class ProviderDocumentRepository extends BaseRepository
{
    protected $document;

    public function model()
    {
        $this->document = new Document();

        return UserDocument::class;
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $document = $this->model->create($request->except(['_token', 'submit', 'image']));

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $document->addMediaFromRequest('image')->toMediaCollection('provider_documents');
            }
            DB::commit();

            return redirect()->route('backend.provider-document.index')->with('message', 'Document Created Successfully.');
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $userDocument = $this->model->findOrFail($id);
            $userDocument->update($request->except(['_token', 'submit', 'image']));

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $userDocument->clearMediaCollection('provider_documents');
                $userDocument->addMediaFromRequest('image')->toMediaCollection('provider_documents');
            }

            DB::commit();

            return redirect()->route('backend.provider-document.index')->with('message', 'Document Updated Successfully');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $userDocument = $this->model->findOrFail($id);
            $userDocument->destroy($id);

            DB::commit();

            return redirect()->route('backend.provider-document.index')->with('message', 'Document Deleted Successfully');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteRows($request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $providerDocument = $this->model->findOrFail($request->id[$row]);
                $providerDocument->delete();
            }
        } catch (\Exception $e) {
            redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function export($request)
    {
        try {
            $format = $request->input('format', 'xlsx');

            if ($format == 'csv') {

                return Excel::download(new ProviderDocumentsExport, 'providerDocument.csv');
            }
            return Excel::download(new ProviderDocumentsExport, 'providerDocument.xlsx');
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

            Excel::import(new ProviderDocumentsImport(), $tempFile);

            if ($activeTab === 'google_sheet' && file_exists($tempFile)) {
                unlink($tempFile);
            }

            return redirect()->back()->with('success', __('static.import.csv_file_import'));
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
