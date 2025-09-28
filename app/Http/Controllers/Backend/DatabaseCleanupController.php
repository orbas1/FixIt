<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Exception;
use App\Exceptions\ExceptionHandler;

class DatabaseCleanupController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $tables = DB::select('SHOW TABLES');
        $tableNames = [];
        foreach ($tables as $table) {
            $tableNames[] = $table->{key((array)$table)};
        }
        $tables = [];
        foreach ($tableNames as $table) {
            $count = DB::table($table)->count();
            $tables[$table] = $count;
        }
        return view('backend.system-tool.cleanup-db',['tables' => $tables]);
    }

    public function store(Request $request)
    {
        if (Helpers::isDemoModeEnabled()) {
            throw new ExceptionHandler("This action is disabled in demo mode", 400);
        }
        if($request->checkAll)
        {
            Artisan::call('migrate:fresh');
            Artisan::call('db:seed');
            $modules = Module::all();
            foreach ($modules as $module) {
                $moduleName = $module->getName();
                Artisan::call('module:seed '.$moduleName);
            }
            $request->session()->flush();
            return to_route('login');
        }
        $tables = $request->table_name;
        foreach ($tables as $table) {
             DB::table($table)->delete();
        }
        return redirect()->back()->with('success', 'Database Table cleaned up successfully');
    }
}
