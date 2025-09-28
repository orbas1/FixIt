<?php

namespace App\Http\Controllers\Backend;

use App\Models\ThemeOption;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\ThemeOptionRepository;

class ThemeOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(ThemeOptionRepository $repository)
    {
        $this->authorizeResource(ThemeOption::class, 'themeOption');
        $this->repository = $repository;
    }

    public function index()
    {
        return $this->repository->index();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ThemeOption $themeOption)
    {
        return $this->repository->update($request, $themeOption?->id);
    }
}
