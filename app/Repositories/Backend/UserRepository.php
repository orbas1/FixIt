<?php

namespace App\Repositories\Backend;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Models\Address;
use App\Models\User;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Prettus\Repository\Eloquent\BaseRepository;
use Spatie\Permission\Models\Role;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Exceptions\ExceptionHandler;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;

class UserRepository extends BaseRepository
{
    protected $role;

    protected $address;

    public function model()
    {
        $this->address = new Address();
        $this->role = new Role();
        return User::class;
    }

    public function index()
    {
        return view('backend.user.index', ['users' => $this->model->get()]);
    }

    public function create($attribute = [])
    {
        return view('backend.user.create', [
            'roles' => $this->role->where('system_reserve', 0)->pluck('name', 'id'),
            'countries' => Helpers::getCountries(),
            'countryCodes' => Helpers::getCountryCodes(),
        ]);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $user = $this->model->create([
                'name' => $request->name,
                'email' => $request->email,
                'code' => $request->code,
                'phone' => (string) $request->phone,
                'status' => $request->status,
                'password' => Hash::make($request->password),
            ]);

            if ($request->role) {
                $role = $this->role->findOrFail($request->role);
            }
            $user->assignRole($role);

            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $user->addMediaFromRequest('image')->toMediaCollection('image');
            }

            if (Helpers::walletIsEnable()) {
                $user->wallet()->create();
                $user->wallet;
            }

            DB::commit();
            return redirect()->route('backend.user.index')->with('message', 'User Created Successfully.');

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $user = $this->model->findOrFail($id);
        return view('backend.user.edit', [
            'user' => $user,
            'roles' => $this->role->where('system_reserve', 0)->pluck('name', 'id'),
            'countries' => Helpers::getCountries(),
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $user = $this->model->findOrFail($id);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => (string) $request->phone,
                'code' => $request->code,
                'status' => $request->status,
            ]);

            $role = $this->role->where('name', RoleEnum::CONSUMER)->first();
            if ($request['role']) {
                $role = $this->role->findOrFail($request['role']);
            }
            $user->syncRoles([$role]);

            if ($request->file('image') && $request->file('image')->isValid()) {
                $user->clearMediaCollection('image');
                $user->addMediaFromRequest('image')->toMediaCollection('image');
            }

            DB::commit();
            return redirect()->route('backend.user.index')->with('message', 'User Updated Successfully.');

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function  destroy($id)
    {
        try {
            $user = $this->model->findOrFail($id);
            $user->forcedelete($id);

            return redirect()->back()->with('message', 'User Deleted Successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function status($id, $status)
    {
        try {

            $user = $this->model->findOrFail($id);
            $user->update(['status' => $status]);

            if ($status != 1) {
                $user->tokens()->update([
                    'expires_at' => Carbon::now(),
                ]);
            }

            return json_encode(['resp' => $user]);
        } catch (Exception $e) {

            throw $e;
        }
    }

    public function updatePassword($request, $id)
    {
        try {
            $this->model->findOrFail($id)->update([
                'password' => Hash::make($request->new_password),
            ]);

            return back()->with('message', 'User Password Update Successfully.');

        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function export($request)
    {
        try {
            $format = $request->input('format', 'xlsx');

            if ($format == 'csv') {

                return Excel::download(new UsersExport, 'users.csv');
            }
            return Excel::download(new UsersExport, 'users.xlsx');
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

            if ($activeTab === 'direct-link') {
                Excel::import(new UsersImport(), $tempFile);
            } else {
                Excel::import(new UsersImport(), $request->file('fileImport'));
            }
            
            if ($activeTab === 'google_sheet' && file_exists($tempFile)) {
                unlink($tempFile);
            }
            return redirect()->route('backend.customer.index')->with('success', __('static.import.csv_file_import'));
            } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }  
    }
}
