<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\ZoneResource;
use Exception;
use App\Models\Zone;
use Illuminate\Http\Request;
use App\Exceptions\ExceptionHandler;
use App\Http\Controllers\Controller;
use App\Repositories\API\ZoneRepository;
use Illuminate\Database\Eloquent\Builder;
use MatanYadaev\EloquentSpatial\Objects\Point;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(ZoneRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {

            $zones = $this->filter($this->repository, $request);
            $zones = $zones->latest('created_at')->with('currency')->paginate($request->paginate);
            return response()->json([
                'success' => true,
                'data' => ZoneResource::collection($zones),
            ]);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Zone $zone)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zone $zone)
    {
        return $this->repository->edit($zone);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Zone $zone)
    {
        //
    }

    public function destroy(Zone $zone)
    {
        //
    }

    public function status(Request $request, $id)
    {
        //
    }

    public function filter($zones, $request)
    {
        if (isset($request->status)) {
            $zones = $zones->where('status', $request->status);
        }

        if ($request->category_ids) {
            $category_ids = explode(',', $request->category_ids);
            $zones->whereHas('categories', function (Builder $categories) use ($category_ids) {
                $categories->WhereIn('categories.id', $category_ids);
            });

        }

        return $zones;
    }

    public function getZoneIds(Request $request)
    {
        if ($request->lat && $request->lng) {
            $latitude = (float) $request->lat;
            $longitude = (float) $request->lng;
            $point = new Point($latitude, $longitude);
            $zones = Zone::whereContains('place_points', $point)->get(['id', 'name', 'currency_id']);
            if (count($zones)) {
                return [
                    'success' => true,
                    'data' => $zones,
                ];
            }
        }

        return [
            'success' => false,
            'data' => [],
        ];
    }
}
