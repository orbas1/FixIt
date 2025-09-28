<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\BannerDataTable;
use App\Enums\BannerTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateBannerRequest;
use App\Http\Requests\Backend\UpdateBannerRequest;
use App\Models\Banner;
use App\Repositories\Backend\BannerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BannerController extends Controller
{
    public $repository;

    public function __construct(BannerRepository $repository)
    {
        $this->authorizeResource(Banner::class, 'banner');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(BannerDataTable $dataTable)
    {
        return $dataTable->render('backend.banner.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bannerType = BannerTypeEnum::BANNERTYPE;
        $locale = request('locale') ?? Session::get('locale', app()->getLocale());

        request()->merge(['locale' => $locale]);
        return view('backend.banner.create', ['bannerType' => $bannerType]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateBannerRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner)
    {
        return $this->repository->edit($banner?->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBannerRequest $request, Banner $banner)
    {
        return $this->repository->update($request, $banner?->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        return $this->repository->destroy($banner?->id);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $banner = Banner::find($request->id[$row]);
                $banner->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function toggleStatus(Request $request)
    {
        return $this->repository->status($request->id, $request);
    }

    public function status(Request $request, $id)
    {

        return $this->repository->status($id, $request->status);
    }
}
