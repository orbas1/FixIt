<?php

namespace App\Repositories\Backend;

use App\Models\HomePage;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class HomePageRepository extends BaseRepository
{
    protected $timeZone;

    protected $currency;

    protected $systemlang;

    protected $paymentGateWays;

    public function model()
    {
        return HomePage::class;
    }

    public function index()
    {

        $homePage = $this->model->pluck('content')->first();
        $homePageId = $this->model->pluck('id')->first();

        return view('backend.home-page.index', [
            'homePage' => $homePage,
            'timeZones' => $timeZones = [],
            'currencies' => $currencies = [],
            'homePageId' => $homePageId,
            'systemlangs' => $systemlangs = [],
        ]);
    }

    public function update($request, $id)
    {

        DB::beginTransaction();
        try {
            $homePage = $this->model->findOrFail($id);
            $requestData = $request->except(['_token', '_method']);
            $locale = $request->locale ?? app()->getLocale();
            
            if ($request->hasFile('download.image_url')) {
                $image = $homePage->addMediaFromRequest('download.image_url')->withCustomProperties(['language' => $locale])->toMediaCollection('download.image_url');
                $imageURL = $image->getPath();  
                $relativePath = str_replace(storage_path('app/public'), '', $imageURL); 
                $relativePath  =   '/storage'.$relativePath;
                $requestData['download']['image_url'] = $relativePath;
            } else {
                $requestData['download']['image_url'] = $homePage->content['download']['image_url'] ?? null;
            }

            if ($request->hasFile('custom_job.image_url')) {
                $image = $homePage->addMediaFromRequest('custom_job.image_url')->withCustomProperties(['language' => $locale])->toMediaCollection('custom_job.image_url');
                $imageURL = $image->getPath();  
                $relativePath = str_replace(storage_path('app/public'), '', $imageURL); 
                $relativePath  =   '/storage'.$relativePath;
                $requestData['custom_job']['image_url'] = $relativePath;
            } else {
                $requestData['custom_job']['image_url'] = $homePage->content['custom_job']['image_url'] ?? null;
            }

            if ($request->hasFile('become_a_provider.image_url')) {
                $image = $homePage->addMediaFromRequest('become_a_provider.image_url')->toMediaCollection('become_a_provider.image_url');
                $imageURL = $image->getPath();  
                $relativePath = str_replace(storage_path('app/public'), '', $imageURL); 
                $relativePath  =   '/storage'.$relativePath;  
                $requestData['become_a_provider']['image_url'] = $relativePath;
            } else {
                $requestData['become_a_provider']['image_url'] = $homePage->content['become_a_provider']['image_url'] ?? null;
            }

            if ($request->hasFile('become_a_provider.float_image_1_url')) {
                $image = $homePage->addMediaFromRequest('become_a_provider.float_image_1_url')->toMediaCollection('become_a_provider.float_image_1_url');
                $imageURL = $image->getPath();  
                $relativePath = str_replace(storage_path('app/public'), '', $imageURL); 
                $relativePath  =   '/storage'.$relativePath;
                $requestData['become_a_provider']['float_image_1_url'] = $relativePath;
            } else {
                $requestData['become_a_provider']['float_image_1_url'] = $homePage->content['become_a_provider']['float_image_1_url'] ?? null;
            }

            if ($request->hasFile('become_a_provider.float_image_2_url')) {
                $image = $homePage->addMediaFromRequest('become_a_provider.float_image_2_url')->toMediaCollection('become_a_provider.float_image_2_url');
                $imageURL = $image->getPath();  
                $relativePath = str_replace(storage_path('app/public'), '', $imageURL); 
                $relativePath  =   '/storage'.$relativePath;
                $requestData['become_a_provider']['float_image_2_url'] = $relativePath;
            } else {
                $requestData['become_a_provider']['float_image_2_url'] = $homePage->content['become_a_provider']['float_image_2_url'] ?? null;
            }

            if ($request->hasFile('news_letter.bg_image_url')) {
                $image = $homePage->addMediaFromRequest('news_letter.bg_image_url')->toMediaCollection('news_letter.bg_image_url');
                $imageURL = $image->getPath();  
                $relativePath = str_replace(storage_path('app/public'), '', $imageURL); 
                $relativePath  =   '/storage'.$relativePath;
                $requestData['news_letter']['bg_image_url'] = $relativePath;
            } else {
                $requestData['news_letter']['bg_image_url'] = $homePage->content['news_letter']['bg_image_url'] ?? null;
            }

            if (isset($requestData['value_banners']['banners'])) {
                $banners = $requestData['value_banners']['banners'] ?? [];
                foreach ($banners as $index => $banner) {
                    if (isset($banner['image_url'])) {
                        if ($banner['image_url'] instanceof UploadedFile) {
                            $image = $homePage->addMedia($banner['image_url'])->toMediaCollection('become_a_provider.float_image_2_url');
                            $imageURL = $image->getPath();  
                            $relativePath = str_replace(storage_path('app/public'), '', $imageURL); 
                            $relativePath  =   '/storage'.$relativePath;
                            $banner['image_url'] = $relativePath;
                        } else {
                            $banner['image_url'] = $homePage->content['value_banners']['banners'][$index]['image_url'];
                        }
                    } else {
                        $banner['image_url'] = $homePage->content['value_banners']['banners'][$index]['image_url'];
                    }

                    $banners[$index] = $banner;
                }

                $requestData['value_banners']['banners'] = $banners;
            }

            $homePage->update([
                'content' => $requestData,
            ]);

            DB::commit();

            return redirect()->route('backend.home_page.index')->with('message', __('static.settings.updated_successfully'));

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }
}
