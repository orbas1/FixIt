<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ExceptionHandler;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateBannerRequest;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use App\Repositories\API\BannerRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public $repository;

    public function __construct(BannerRepository $repository)
    {
        $this->authorizeResource(Banner::class, 'banner',[
            'except' => ['index','show'],
        ]);
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $banners = $this->filter($this->repository, $request);
            $banners = $banners->where('is_offer', true)->latest('created_at')->simplePaginate($request->paginate ?? $banners->count());
            return  BannerResource::collection($banners);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show($id)
    {
        return $this->repository->show($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function filter($banner, $request)
    {
        if($request->has('zone_ids') && !empty($request->zone_ids)){
            $zone_ids = explode(',', $request->zone_ids);
            $banner = $banner->whereHas('zones', function (Builder $zones) use ($zone_ids) {
                $zones->WhereIn('zones.id', $zone_ids);
            });
        }

        return $banner;
    }
}
