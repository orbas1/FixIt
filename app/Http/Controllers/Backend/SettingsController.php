<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Repositories\Backend\SettingsRepository;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(SettingsRepository $repository)
    {
        $this->authorizeResource(Setting::class, 'setting');
        $this->repository = $repository;
    }

    public function index()
    {
        return $this->repository->index();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Setting $setting)
    {
        return $this->repository->update($request, $setting?->id);
    }

    public function setTheme(Request $request)
    {
        $request->session()->put('theme', $request->input('theme'));
        return response()->json(['success' => true]);
    }
}
