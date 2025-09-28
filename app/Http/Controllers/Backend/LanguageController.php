<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\SystemLangDataTable;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateLanguageRequest;
use App\Http\Requests\Backend\UpdateLanguageRequest;
use App\Models\SystemLang;
use App\Repositories\Backend\LanguageRepository;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    private $repository;

    public function __construct(LanguageRepository $repository)
    {
        $this->authorizeResource(SystemLang::class, 'systemLang');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(SystemLangDataTable $dataTable)
    {
        return $dataTable->render('backend.language.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.language.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(SystemLang $systemLang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SystemLang $systemLang)
    {
        $systemLang = $this->repository->find($systemLang->id);
        return view('backend.language.edit', ['language' => $systemLang]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateLanguageRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLanguageRequest $request,SystemLang $systemLang)
    {
        return $this->repository->update($request->all(), $systemLang->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SystemLang $systemLang)
    {
        return $this->repository->destroy($systemLang->id);
    }

    /**
     * Update Status the specified resource from storage.
     *
     * @param  int  $id
     * @param  int  $status
     * @return \Illuminate\Http\Response
     */
    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    public function rtl(Request $request, $id)
    {
        return $this->repository->rtl($id, $request->status);
    }

    public function deleteRows(Request $request)
    {
        try {
            $defaultLanguageId = Helpers::getSettings()['general']['default_language_id'];
            foreach ($request->id as $row => $key) {
                if ($request->id[$row] == $defaultLanguageId) {
                    continue;
                }
                $systemLang = SystemLang::find($request->id[$row]);
                if ($systemLang) {
                    $systemLang->delete();
                }
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e);
        }
    }

    public function translate(Request $request)
    {
        return $this->repository->translate($request);
    }

    public function translate_update(Request $request, $locale)
    {
        return $this->repository->translate_update($request, $locale);
    }
}
