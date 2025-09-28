<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ImportExportController extends Controller
{

    public function index(Request $request)
    {
      $coreTablesFile = config_path('import-export.php');
      $allTables = [];
      if (file_exists($coreTablesFile)) {
        $allTables = include $coreTablesFile;
      }

      return view('backend.import-export.index' , ['allTables' => $allTables]);
    }

    public function importExport(Request $request , $slug)
    {
      $coreTablesFile = config_path('import-export.php');
      $allTables = [];
      if (file_exists($coreTablesFile)) {
        $allTables = include $coreTablesFile;
      }
      foreach($allTables as $table)
      {
        if($table['slug'] === $slug) {
          $tableData = $table;
        }
      }
      return view('backend.import-export.import' , ['tableData' => $tableData]);
    }


}

