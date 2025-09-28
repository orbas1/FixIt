<?php

namespace App\Repositories\Backend;

use Exception;
use App\Models\User;
use App\Models\PushNotificationTemplate;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\File;
use App\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;

class PushNotificationTemplateRepository extends BaseRepository
{
    function model()
    {
        return PushNotificationTemplate::class;
    }
    public function index($request)
    {
        $search = $request->get('search');

        $pushNotificationTemplates = [];
        $coreFile = config_path('notify-template.php');
        $coreTemplates = [];

        if (file_exists($coreFile)) {
            $coreTemplates = include $coreFile;
        }
        if (isset($coreTemplates['name'], $coreTemplates['slug'],$coreTemplates['push-notification-templates'])) {

            $templates = $coreTemplates['push-notification-templates'];

            if ($search) {
                $templates = array_filter($templates, function($template) use ($search) {
                    return stripos($template['name'], $search) !== false ||
                        stripos($template['description'], $search) !== false;
                });
            }

            $pushNotificationTemplates[] = [
                'name' => $coreTemplates['name'],
                'slug' => $coreTemplates['slug'],
                'status' => true,
                'templates' => $templates
            ];
        }

        return view('backend.push-notification-template.index' , ['pushNotificationTemplates' => $pushNotificationTemplates]);
    }

    public function edit($request ,$slug)
    {
        $content = $this->model->where('slug', $slug)->first();
        $eventAndShortcodes = $this->fetchEventAndShortcodes($slug);
        return view('backend.push-notification-template.template', [
            'slug' => $slug,
            'content' => $content,
            'eventAndShortcodes' => $eventAndShortcodes,
        ]);
    }

    public function update($request, $slug)
    {
        DB::beginTransaction();
        try {
            $data = [
                'title' => $request['title'],
                'content' => $request['content'],
            ];

            $template = $this->model->updateOrCreate(
                ['slug' => $slug],
                $data
            );

            DB::commit();
            return redirect()->back()->with('success', 'Template updated successfully!');
        } catch (Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to update the template: ' . $e->getMessage());
        }
    }

    public function fetchEventAndShortcodes($slug)
    {
        $eventAndShortcodes = [];
        $coreTemplateFile = config_path('notify-template.php');
        if (file_exists($coreTemplateFile)) {
            $templates = include $coreTemplateFile;



            if (isset($templates['push-notification-templates'])) {
                foreach ($templates['push-notification-templates'] as $template) {
                    if ($template['slug'] === $slug) {
                        if (isset($template['shortcodes']) || isset($template['name'])) {
                            $eventAndShortcodes = [
                                'name' => $template['name'],
                                'shortcodes' => $template['shortcodes']
                            ];
                        }
                        break;
                    }
                }
            }
        }
        return $eventAndShortcodes;
    }
}
