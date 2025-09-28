<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ServiceManDataTable;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateServicemanRequest;
use App\Http\Requests\Backend\UpdateServiceManRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\Address;
use App\Models\Language;
use App\Models\User;
use App\Repositories\Backend\ServicemanRepository;
use Illuminate\Http\Request;

class ServicemanController extends Controller
{
    private $repository;

    private $address;
    private $serviceman;

    private $language;

    public function __construct(ServicemanRepository $repository, Address $address, Language $language, User $serviceman)
    {
        $this->repository = $repository;
        $this->address = $address;
        $this->language = $language;
        $this->serviceman = $serviceman;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ServiceManDataTable $dataTable)
    {
        return $dataTable->render('backend.serviceman.index', ['servicemans' => $this->repository->role('serviceman')->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $languages = $this->language->pluck('key', 'id');

        return view('backend.serviceman.create', [
            'countries' => Helpers::getCountries(),
            'providers' => $this->repository->role('provider')->get(),
            'languages' => $languages,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateServicemanRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $serviceman = $this->serviceman->findOrFail($id);
        $languages = $this->language->pluck('key', 'id');
        $userKnownLanguages = $serviceman->knownLanguages->pluck('id')->toArray();

        return view('backend.serviceman.edit', [
            'serviceman' => $serviceman,
            'countries' => Helpers::getCountries(),
            'providers' => $this->repository
                ->role('provider')
                ->get(),
            'address' => $this->address
                ->where('user_id', $id)
                ->where('is_primary', true)->first(),
            'languages' => $languages,
            'userKnownLanguages' => $userKnownLanguages,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceManRequest $request,$id)
    {
        return $this->repository->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }

    public function updateIsFeatured(Request $request)
    {
        return $this->repository->updateIsFeatured($request->statusVal, $request->subject_id);
    }

    public function updateStatus(Request $request)
    {
        return $this->repository->updateStatus($request->statusVal, $request->subject_id);
    }

    public function changePassword(UpdatePasswordRequest $request, $id)
    {
        return $this->repository->changePassword($request, $id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    public function servicemanLocation()
    {
        return $this->repository->servicemanLocation();
    }

    public function servicemanCordinates($id)
    {
       return $this->repository->servicemanCordinates($id);
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
