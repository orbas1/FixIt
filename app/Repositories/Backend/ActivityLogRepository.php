<?php

namespace App\Repositories\Backend;

use App\Helpers\Helpers;
use Exception;
use App\Exceptions\ExceptionHandler;
use Spatie\Activitylog\Models\Activity;
use Prettus\Repository\Eloquent\BaseRepository;

class ActivityLogRepository extends BaseRepository
{
    function model()
    {
        return Activity::class;
    }

    public function destroy($id)
    {
        if (Helpers::isDemoModeEnabled()) {
            throw new ExceptionHandler("This action is disabled in demo mode", 400);
        }
        try {

            $this->model->findOrFail($id)?->destroy($id);
            return redirect()->route('backend.activity-logs.index')->with('success', __('static.system_tools.activity_delete_successfully'));

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function deleteAll()
    {
        if (Helpers::isDemoModeEnabled()) {
            throw new ExceptionHandler("This action is disabled in demo mode", 400);
        }
        try {

            $this->model?->truncate();
            return redirect()->route('backend.activity-logs.index')->with('success', __('static.system_tools.activity_delete_successfully'));

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
