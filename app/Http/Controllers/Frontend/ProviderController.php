<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\User;

class ProviderController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $providers = Helpers::getProviders();
        $providers = $providers->paginate(Helpers::getThemeOptions()['pagination']['provider_list_per_page']);

        return view('frontend.provider.index', ['providers' => $providers]);
    }

    public function details($slug)
    {
        $provider = User::where('slug', $slug)->with('media')->whereNull('deleted_at')?->first();
        $services = Helpers::getServiceByProviderId($provider?->id);
        
        return view('frontend.provider.details', ['provider' => $provider, 'services' => $services]);
    }
}