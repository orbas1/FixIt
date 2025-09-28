<?php

namespace App\Repositories\Backend;

use Exception;
use App\Models\User;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;

class EmailTemplateRepository extends BaseRepository
{
    function model()
    {
        return EmailTemplate::class;
    }
    public function index($request)
    {

        $search = $request->get('search');

        $emailTemplates = [];
        $coreTemplateFile = config_path('notify-template.php');
        $coreTemplates = [];
        if (file_exists($coreTemplateFile)) {
            $coreTemplates = include $coreTemplateFile;
        }
        if (isset($coreTemplates['name'], $coreTemplates['slug'], $coreTemplates['email-templates'])) {

            $templates = $coreTemplates['email-templates'];

            if ($search) {
                $templates = array_filter($templates, function($template) use ($search) {
                    return stripos($template['name'], $search) !== false ||
                        stripos($template['description'], $search) !== false;
                });
            }

            if ($request->get('role')) {
                $role = $request->get('role');
                $templates = array_filter($templates, function($template) use ($role) {
                    $roleWithParentheses = "($role)";
                    return stripos($template['name'], $roleWithParentheses) !== false;
                });
            }


            $emailTemplates[] = [
                'name' => $coreTemplates['name'],
                'slug' => $coreTemplates['slug'],
                'status' => true,
                'templates' => $templates
            ];
        }
        return view('backend.email-template.index' , ['emailTemplates' => $emailTemplates]);
    }
    public function edit($request ,$slug)
    {
        $content = $this->model->where('slug', $slug)->first();
        $eventAndShortcodes = $this->fetchEventAndShortcodes($slug);
        return view('backend.email-template.template', [
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



            if (isset($templates['email-templates'])) {
                foreach ($templates['email-templates'] as $template) {
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
