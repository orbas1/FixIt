<?php

namespace App\Repositories\API;

use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Resources\UserDocumentResource;
use App\Models\Document;
use App\Models\User;
use App\Models\UserDocument;
use Exception;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class DocumentRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title' => 'like',
    ];

    protected $user;

    protected $userDocument;

    public function model()
    {
        $this->userDocument = new UserDocument();
        $this->user = new User();

        return Document::class;
    }

    public function show($banner)
    {
        try {
            $item = $this->model->with('media')->findOrFail($banner->id);

            return response()->json(['success' => true, 'data' => $item]);
        } catch (Exception $e) {

            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $banner = $this->model->create([
                'type' => $request->type,
                'related_id' => $request->related_id,
                'status' => $request->status,
            ]);

            if ($request->images) {
                $images = $request->images;
                foreach ($images as $image) {
                    $banner->addMedia($image)->toMediaCollection('image');
                }
            }
            DB::commit();

            return response()->json([
                'message' => __('static.document.store'),
                'banner' => $banner,
            ]);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function verifyUserDocument($request)
    {
        DB::beginTransaction();
        try {
            $user = $this->user::findOrFail($request->userId);
            if ($user->documents) {
                $user->documents->update([
                    'status' => 'verified',
                ]);
            }
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getUserDocuments($request)
    {
        DB::beginTransaction();
        try {
            $user = $this->user::findOrFail($request->userId);
            if ($user->UserDocuments()) {
                $userDocuments = $user->UserDocuments();

                $items = $userDocuments->with('media')->latest('created_at')->paginate($request->paginate ?? $userDocuments->count());

                return response()->json([
                    'success' => true,
                    'data' => UserDocumentResource::collection($items)
                ]);
            } else {
                return response()->json(['success' => false, 'message' => __('static.document.document_not_uploaded')], 404);
            }
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function uploadProviderDocument($request)
    {
        DB::beginTransaction();
        try {
            $getCurrentUserId = Helpers::getCurrentUserId();
            $provider = Helpers::getProviderById($getCurrentUserId);

            if ($request->user_document_id) {
                $userDocument = $this->userDocument::findOrFail($request->user_document_id);
                if (! $userDocument) {
                    return response()->json([
                        'sucess' => false,
                        'message' => __('static.document.document_not_uploaded'),
                    ]);
                }
                $userDocument->update([
                    'user_id' => $provider->id,
                    'document_id' => $request->document_id,
                    'identity_no' => $request->identity_no,
                    'notes' => $request->notes ?? null,
                    'status' => 'pending',
                ]);

                if ($request->hasFile('images')) {
                    $images = $request->file('images');
                    $userDocument->clearMediaCollection('provider_documents');
                    foreach ($images as $image) {
                        $userDocument->addMedia($image)->toMediaCollection('provider_documents');
                    }
                    $userDocument->media;
                }

                DB::commit();

                return response()->json(['success' => true, 'message' => __('static.document.updated')], 200);
            } else {
                $userDocument = $this->userDocument::create([
                    'user_id' => $provider->id,
                    'document_id' => $request->document_id,
                    'status' => 'pending',
                    'identity_no' => $request->identity_no,
                    'notes' => $request->notes,
                ]);

                if ($request->hasFile('images')) {
                    $images = $request->file('images');
                    foreach ($images as $image) {
                        $userDocument->addMedia($image)->toMediaCollection('provider_documents');
                    }
                    $userDocument->media;
                }
                DB::commit();

                return response()->json(['success' => true, 'message' => __('static.document.document_uploaded_successfull')], 200);
            }
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
