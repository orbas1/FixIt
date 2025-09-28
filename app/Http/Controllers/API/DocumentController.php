<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\UploadProviderDocumentRequest;
use App\Repositories\API\DocumentRepository;
use Exception;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public $repository;

    public function __construct(DocumentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $documents = $this->repository->where(['status' => true]);

            return $documents = $documents->latest('created_at')->paginate($request->paginate ?? $documents->count());

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
    public function show(string $id)
    {
        //
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

    public function verifyUserDocument(Request $request)
    {
        return $this->repository->verifyUserDocument($request);
    }

    public function getUserDocuments(Request $request)
    {
        return $this->repository->getUserDocuments($request);
    }

    public function uploadProviderDocument(UploadProviderDocumentRequest $request)
    {
        return $this->repository->uploadProviderDocument($request);
    }
}
