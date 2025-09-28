<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProviderDocumentDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateProviderDocumentRequest;
use App\Http\Requests\Backend\UpdateProviderDocumentRequest;
use App\Models\Address;
use App\Models\Document;
use App\Models\User;
use App\Models\UserDocument;
use App\Repositories\Backend\ProviderDocumentRepository;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class ProviderDocumentController extends Controller
{
    private $document;

    private $repository;

    private $providers;

    public function __construct(ProviderDocumentRepository $repository, Document $document, User $providers, Address $address)
    {
        $this->document = $document;
        $this->providers = $providers;
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Renderable
     */
    public function index(ProviderDocumentDataTable $dataTable)
    {
        return $dataTable->render('backend.provider-document.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Renderable
     */
    public function create()
    {
        return view('backend.provider-document.create', ['providers' => $this->getProviders(), 'documents' => $this->getDocuments()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Renderable
     */
    public function store(CreateProviderDocumentRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Renderable
     */
    public function isVerify(Request $request)
    {
        return $this->repository->isVerify($request);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function show(User $provider)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function edit($id)
    {
        $providerDocument = $this->repository->find($id);
        return view('backend.provider-document.edit', ['providerDocument' => $providerDocument, 'providers' => $this->getProviders(), 'documents' => $this->getDocuments()]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Renderable
     */
    public function update(UpdateProviderDocumentRequest $request,$id)
    {
        return $this->repository->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Renderable
     */
    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }

    protected function getProviders()
    {
        return $this->providers->role('provider')->get();
    }

    protected function getDocuments()
    {
        return $this->document->pluck('title', 'id');
    }

    public function deleteRows(Request $request)
    {
        return $this->repository->deleteRows($request);
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
