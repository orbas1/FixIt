<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProviderDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateProviderRequest;
use App\Http\Requests\Backend\UpdateProviderRequest;
use App\Integration\GoogleMap;
use App\Models\Country;
use App\Models\Provider;
use App\Models\State;
use App\Models\User;
use App\Repositories\Backend\ProviderRepository;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProviderController extends Controller
{
    private $repository;

    public function __construct(ProviderRepository $repository)
    {
        $this->authorizeResource(Provider::class, 'provider');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(ProviderDataTable $dataTable)
    {
        return $dataTable->render('backend.provider.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        return $this->repository->create();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Renderable
     */
    public function store(CreateProviderRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show(Provider $provider)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit(Provider $provider)
    {
        return $this->repository->edit($provider?->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Renderable
     */
    public function update(UpdateProviderRequest $request, Provider $provider)
    {
        return $this->repository->update($request, $provider?->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    public function general_info($id)
    {
        return $this->repository->general_info($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function destroy(Provider $provider)
    {
        return $this->repository->destroy($provider?->id);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $provider = User::find($request->id[$row]);
                $provider->delete();
            }
        } catch (\Exception $e) {
            throw new $e;
        }
    }

    public function getPlaceId(Request $request): JsonResponse
    {
        $googleMapApi = new GoogleMap();

        return $googleMapApi->addressId($request->inputData);
    }

    public function findAddressBasedOnPlaceId(Request $request)
    {
        $googleMapApi = new GoogleMap();
        $addressDetails = $googleMapApi->addressBasedOnPlaceId($request->placeId);
        if ($addressDetails) {
            $countryName = $addressDetails['country'];
            $stateName = $addressDetails['state'];
            $country = Country::firstOrCreate(['name' => $countryName]);
            $state = State::firstOrCreate(['name' => $stateName, 'country_id' => $country->id]);

            $addressDetails['country_id'] = $country->id;
            $addressDetails['state_id'] = $state->id;

            return response()->json($addressDetails);
        }
    }

    public function export(Request $request)
    {
        return $this->repository->export($request);
    }
    public function import(Request $request)
    {
        return $this->repository->import($request);
    }
}
