<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\DocumentDataTable;
use App\Helpers\ToggleHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateDocumentRequest;
use App\Http\Requests\Backend\UpdateDocumentRequest;
use App\Models\Document;
use App\Repositories\Backend\DocumentRepository;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public $repository;

    public function __construct(DocumentRepository $repository)
    {
        $this->authorizeResource(Document::class, 'document');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(DocumentDataTable $dataTable)
    {
        return $dataTable->render('backend.document.index', ['documents' => $this->repository->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.document.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateDocumentRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        return view('backend.document.edit', ['document' => $document]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentRequest $request, Document $document)
    {
        return $this->repository->update($request, $document?->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        return $this->repository->destroy($document?->id);
    }

    public function toggleStatus(Request $request)
    {
        $dataName = $request->dataName;
        $status = $request->statusVal;
        $subject_id = $request->subject_id;
        $modelClassName = $request->modelClassName;
        $columnName = $request->columnName;
        $response = ToggleHelper::updateStatus($dataName, $status, $modelClassName, $columnName, $subject_id);

        return response()->json($response);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $document = Document::find($request->id[$row]);
                $document->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
