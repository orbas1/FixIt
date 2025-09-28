<?php

namespace App\Repositories\Backend;

use App\Helpers\Helpers;
use App\Models\Currency;
use App\Models\SystemLang;
use App\Models\ThemeOption;
use App\Models\TimeZone;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Prettus\Repository\Eloquent\BaseRepository;

class ThemeOptionRepository extends BaseRepository
{
    protected $timeZone;

    protected $currency;

    protected $systemlang;

    protected $paymentGateWays;

    public function model()
    {
        $this->currency = new Currency();
        $this->timeZone = new TimeZone();
        $this->systemlang = new SystemLang();

        return ThemeOption::class;
    }

    public function index()
    {
        $settings = $this->model->pluck('options')->first();
        $themeOptionId = $this->model->pluck('id')->first();
        $timeZones = $this->timeZone->pluck('name', 'code');
        $currencies = $this->currency->pluck('code', 'id');
        $systemlangs = $this->systemlang->where('status', true)->pluck('name', 'id');

        return view('backend.theme-options.index', [
            'settings' => $settings,
            'timeZones' => $timeZones,
            'currencies' => $currencies,
            'themeOptionId' => $themeOptionId,
            'systemlangs' => $systemlangs,
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $themeOptions = $this->model->findOrFail($id);
            $requestData = $request->except(['_token', '_method']);

            if ($request->hasFile('about_us.left_bg_image_url')) {
                $image = $themeOptions->addMediaFromRequest('about_us.left_bg_image_url')->toMediaCollection('left_bg_image_url');
                $imageURL = $image->getUrl();
                $requestData['about_us']['left_bg_image_url'] = Helpers::getAssetUrl($imageURL);
            } else {
                $requestData['about_us']['left_bg_image_url'] = $themeOptions->options['about_us']['left_bg_image_url'];
            }

            if ($request->hasFile('about_us.right_bg_image_url')) {
                $image = $themeOptions->addMediaFromRequest('about_us.right_bg_image_url')->toMediaCollection('right_bg_image_url');
                $imageURL = $image->getUrl();
                $requestData['about_us']['right_bg_image_url'] = Helpers::getAssetUrl($imageURL);
            } else {
                $requestData['about_us']['right_bg_image_url'] = $themeOptions->options['about_us']['right_bg_image_url'];
            }

            if ($request->hasFile('authentication.header_logo')) {
                $image = $themeOptions->addMediaFromRequest('authentication.header_logo')->toMediaCollection('header_logo');
                $imageURL = $image->getUrl();
                $requestData['authentication']['header_logo'] = Helpers::getAssetUrl($imageURL);
            } else {
                $requestData['authentication']['header_logo'] = $themeOptions->options['authentication']['header_logo'];
            }

            if ($request->hasFile('authentication.auth_images')) {
                $image = $themeOptions->addMediaFromRequest('authentication.auth_images')->toMediaCollection('auth_images');
                $imageURL = $image->getUrl();
                $requestData['authentication']['auth_images'] = Helpers::getAssetUrl($imageURL);
            } else {
                $requestData['authentication']['auth_images'] = $themeOptions->options['authentication']['auth_images'];
            }

            if ($request->hasFile('general.header_logo')) {
                $image = $themeOptions->addMediaFromRequest('general.header_logo')->toMediaCollection('header_logo');
                $imageURL = $image->getUrl();
                $requestData['general']['header_logo'] = Helpers::getAssetUrl($imageURL);
            } else {
                $requestData['general']['header_logo'] = $themeOptions->options['general']['header_logo'];
            }

            if ($request->hasFile('general.favicon_icon')) {
                $image = $themeOptions->addMediaFromRequest('general.favicon_icon')->toMediaCollection('favicon_icon');
                $imageURL = $image->getUrl();
                $requestData['general']['favicon_icon'] = Helpers::getAssetUrl($imageURL);
            } else {
                $requestData['general']['favicon_icon'] = $themeOptions->options['general']['favicon_icon'];
            }

            if ($request->hasFile('general.footer_logo')) {
                $image = $themeOptions->addMediaFromRequest('general.footer_logo')->toMediaCollection('footer_logo');
                $imageURL = $image->getUrl();
                $requestData['general']['footer_logo'] = Helpers::getAssetUrl($imageURL);
            } else {
                $requestData['general']['footer_logo'] = $themeOptions->options['general']['footer_logo'];
            }

            if ($request->hasFile('seo.og_image')) {
                $image = $themeOptions->addMediaFromRequest('seo.og_image')->toMediaCollection('og_image');
                $imageURL = $image->getUrl();
                $requestData['seo']['og_image'] = Helpers::getAssetUrl($imageURL);
            } else {
                $requestData['seo']['og_image'] = $themeOptions->options['seo']['og_image'];
            }

            $links = Helpers::getFooterUsefulLinks();
            $selectedSlugs = $requestData['footer']['useful_link'] ?? [];
            $selectedLinks = collect($links)
                ->whereIn('slug', $selectedSlugs)
                ->values()?->toArray();

            $requestData['footer']['useful_link'] = $selectedLinks;

            $links = Helpers::getFooterPagesLinks();
            $selectedSlugs = $requestData['footer']['pages'] ?? [];
            $selectedLinks = collect($links)
                ->whereIn('slug', $selectedSlugs)
                ->values()?->toArray();

            $requestData['footer']['pages'] = $selectedLinks;

            $links = Helpers::getFooterOthersLinks();
            $selectedSlugs = $requestData['footer']['others'] ?? [];
            $selectedLinks = collect($links)
                ->whereIn('slug', $selectedSlugs)
                ->values()?->toArray();

            $requestData['footer']['others'] = $selectedLinks;
            $themeOptions->update([
                'options' => $requestData,
            ]);

            DB::commit();
            return redirect()->route('backend.theme_options.index')->with('message', __('static.theme_options.updat_successfully'));

        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function setAppLocale($language)
    {
        Session::put('locale', $language?->locale);
        Session::put('dir', $language?->is_rtl ? 'rtl' : 'ltr');
        app()->setLocale(Session::get('locale'));
    }

    public function getLanguageById($id)
    {
        return SystemLang::where('id', $id)?->first();
    }
}
