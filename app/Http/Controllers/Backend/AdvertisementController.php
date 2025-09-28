<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\AdvertisementDataTable;
use App\Enums\AdvertisementTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateAdvertisementRequest;
use App\Http\Requests\Backend\UpdateAdvertisementRequest;
use App\Models\Advertisement;
use App\Repositories\Backend\AdvertisementRepository;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    public $repository;

    public function __construct(AdvertisementRepository $repository)
    {
        $this->authorizeResource(Advertisement::class, 'advertisement');
        $this->repository = $repository;
    }

    public function index(AdvertisementDataTable $dataTable)
    {
        return $dataTable->render('backend.advertisement.index');
    }


    public function create()
    {
        return $this->repository->create();
    }

    public function store(CreateAdvertisementRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Advertisement $advertisement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Advertisement $advertisement)
    {
        return $this->repository->edit($advertisement?->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdvertisementRequest $request, Advertisement $advertisement)
    {
        return $this->repository->update($request, $advertisement?->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Advertisement $advertisement)
    {
        return $this->repository->destroy($advertisement?->id);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $advertisement = Advertisement::find($request->id[$row]);
                $advertisement->delete();
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
