<?php

namespace App\Repositories\Backend;

use Exception;
use Prettus\Repository\Eloquent\BaseRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaRepository extends BaseRepository
{
    public function model()
    {
        return Media::class;
    }

    public function destroy($id)
    {
        try {
            $image = $this->model->findOrfail($id);
            if (!$image) {
                return response()->json(['message' => 'Image not found'], 404);
            }
            $image->destroy($id);

            return response()->json(['message' => 'Image Successfully Deleted'], 200);

        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
