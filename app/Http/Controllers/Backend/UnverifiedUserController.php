<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use Illuminate\Http\Request;
use App\DataTables\UnverifiedUserDataTable;
use App\Http\Controllers\Controller;


class UnverifiedUserController extends Controller
{
   
    public function index(UnverifiedUserDataTable $dataTable)
    {
        return $dataTable->render('backend.unverified-user.index');
    }

    public function verify(Request $request , $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_verified' => $request->status]);
    }

}
