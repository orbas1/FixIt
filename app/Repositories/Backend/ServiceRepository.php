<?php

namespace App\Repositories\Backend;

use Exception;
use App\Events\CreateServiceEvent;
use App\Exports\ServiceExport;
use App\Imports\ServiceImport;
use Illuminate\Support\Arr;
use App\Enums\CategoryType;
use App\Enums\RoleEnum;
use App\Enums\ServiceTypeEnum;
use App\Helpers\Helpers;
use App\Models\Address;
use App\Models\Category;
use App\Models\Zone;
use App\Models\Service;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Prettus\Repository\Eloquent\BaseRepository;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ServiceRepository extends BaseRepository
{
    protected $address;

    protected $providers;

    protected $category;

    protected $taxes;
    protected $zone;

    public function model()
    {
        $this->category = new Category();
        $this->providers = new User();
        $this->address = new Address();
        $this->taxes = new Tax();
        $this->zone = new Zone();

        return Service::class;
    }

    public function index()
    {
        return view('backend.service.index');
    }

    public function create($attributes = [])
    {
        $locale = request('locale') ?? Session::get('locale', app()->getLocale());
        request()->merge(['locale' => $locale]);
        return view('backend.service.create', [
            'services' => $this->getServices('service'),
            'providers' => $this->getProviders(),
            'categories' => [],
            'countries' => Helpers::getCountries(),
            'taxes' => $this->getTaxes(),
            'zones' => $this->getZones()
        ]);
    }

    public function getTaxes()
    {
        return $this->taxes->where('status', true)->pluck('name', 'id');
    }

    public function getZones()
    {
        return $this->zone->where('status',true)->pluck('name','id');
    }

    public function isProviderCanCreate()
    {
        if (Helpers::isUserLogin()) {
            $isAllowed = true;
            $roleName = Helpers::getCurrentRoleName();
            if ($roleName == RoleEnum::PROVIDER) {
                $isAllowed = false;
                $provider = Auth::user();
                $maxItems = $provider?->services()->count();
                if (Helpers::isModuleEnable('Subscription')) {
                    if (function_exists('isPlanAllowed')) {
                        $isAllowed = isPlanAllowed('allowed_max_services', $maxItems, $provider?->id);
                    }
                }

                if (! $isAllowed) {
                    $settings = Helpers::getSettings();
                    $max_services = $settings['default_creation_limits']['allowed_max_services'];
                    if ($max_services > $maxItems) {
                        $isAllowed = true;
                    }
                }
            }

            return $isAllowed;
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $locale = $request->locale ?? app()->getLocale();

            if ($this->isProviderCanCreate()) {

                $status = $request->status ?? 0;

                $service = $this->model->create([
                    'type' => $request->type,
                    'price' => $request->price,
                    'title' => $request->title,
                    'video' => $request?->video,
                    'status' => $status,
                    'discount' => $request->discount,
                    'per_serviceman_commission' => $request->per_serviceman_commission,
                    'duration' => $request->duration,
                    'duration_unit' => $request->duration_unit,
                    'user_id' => $request->user_id,
                    'description' => $request->description,
                    'content' => $request->content,
                    'is_featured' => $request->is_featured,
                    'required_servicemen' => $request->required_servicemen,
                    'service_rate' => $request->service_rate,
                    'isMultipleServiceman' => $request->isMultipleServiceman,
                    'is_random_related_services' => $request->is_random_related_services,
                ]);

                if (isset($request->category_id)) {
                    if (in_array('selectAll', $request->category_id)) {
                        $allCategories = Category::pluck('id')->toArray();
                        $request->merge(['category_id' => $allCategories]);
                    }
                    $service->categories()->attach($request->category_id);
                    $service->categories;
                }

                if (isset($request->tax_id)) {

                    $service->taxes()->attach($request->tax_id);
                    $service->taxes;
                }

                if (!isset($request->service_id) && $request->is_random_related_services == true) {
                    $rand_service_id = $request->category_id[array_rand($request->category_id)];
                    $related_service_ids = Helpers::getRelatedServiceId($service, $rand_service_id, $service->id);
                    $service->related_services()->attach($related_service_ids);
                }

                if (isset($request->service_id) && $request->is_random_related_services == false) {
                    $service->related_services()->attach($request->service_id);
                }

                if ($request->hasFile('image')) {
                    $images = $request->file('image');
                    foreach ($images as $image) {
                        $service->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                    }
                    $service->media;
                }

                if ($request->hasFile('web_images')) {
                    $images = $request->file('web_images');
                    foreach ($images as $image) {
                        $service->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('web_images');
                    }
                    $service->media;
                }

                if ($request->hasFile('web_thumbnail') && $request->file('web_thumbnail')->isValid()) {
                    $service->addMedia($request->file('web_thumbnail'))->withCustomProperties(['language' => $locale])->toMediaCollection('web_thumbnail');
                }

                if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
                    $service->addMedia($request->file('thumbnail'))->withCustomProperties(['language' => $locale])->toMediaCollection('thumbnail');
                }

                $service->setTranslation('title', $locale, $request['title']);
                $service->setTranslation('video', $locale, $request['video']);
                $service->setTranslation('description', $locale, $request['description']);
                $service->setTranslation('content', $locale, $request['content']);
                $service->setTranslation('speciality_description', $locale, $request['speciality_description']);
                $service->setTranslation('meta_title', $locale, $request['meta_title']);
                $service->setTranslation('meta_description', $locale, $request['meta_description']);
                $service->save();
                // Store FAQs
                if (isset($request->faqs) && is_array($request->faqs) && count($request->faqs) > 0) {
                    foreach ($request->faqs as $faq) {
                        $question = $faq['question'] ?? null;
                        $answer = $faq['answer'] ?? null;

                        if (!is_null($question) && !is_null($answer) && $question !== '' && $answer !== '' && strtolower($question) !== 'null' && strtolower($answer) !== 'null') {
                            $newFaq = $service->faqs()->create([
                                'question' => $question,
                                'answer' => $answer,
                            ]);

                            $newFaq->setTranslation('question', $locale, $question);
                            $newFaq->setTranslation('answer', $locale, $answer);
                            $newFaq->save();
                        }
                    }
                }

                if($request->type == ServiceTypeEnum::PROVIDER_SITE){
                    $providerSiteaddress = [
                        'country_id' => $request->country_id,
                        'state_id' => $request->state_id,
                        'city' => $request->city,
                        'postal_code' => $request->postal_code,
                        'address' => $request->address,
                        'lat' => (float) $request->lat,
                        'lng' => (float) $request->lng
                    ];
                    $destinationLocation = $providerSiteaddress;
                    $service->destination_location = $destinationLocation;
                    $service->save();
                }
                else{
                    $address = $this->address->create([
                        'service_id' => $service->id,
                        'type' => $request->address_type,
                        'alternative_name' => $request->alternative_name,
                        'alternative_phone' => $request->alternative_phone,
                        'code' => $request->alternative_code,
                        'country_id' => $request->country_id,
                        'state_id' => $request->state_id,
                        'city' => $request->city,
                        'postal_code' => $request->postal_code,
                        'address' => $request->address,
                    ]);
                }

                DB::commit();

                event(new CreateServiceEvent($service));

                return redirect()->route('backend.service.index')->with('message', __('static.service.store'));
            }

            throw new Exception(__('static.not_allow_for_creation'), 400);
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {

        $service = $this->model->findOrFail($id);
        $categories = $service->categories;
        $zones = $categories->flatMap(function ($category) {
            return $category->zones->where('status',true);
        })->pluck('id','name');


        return view('backend.service.edit', [
            'service' => $service,
            'taxes' => $this->getTaxes(),
            'providers' => $this->getProviders(),
            'countries' => Helpers::getCountries(),
            'services' => $this->getServices($service),
            'default_categories' => $this->getDefaultCategories($service),
            'zones' => $this->getZones(),
            'selected_zones' => $zones->toArray()
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $locale = $request->locale ?? app()->getLocale();
            $service = $this->model->findOrFail($id);
            if($service->per_serviceman_commission !== $request->per_serviceman_commission && Helpers::getCurrentRoleName() !== RoleEnum::PROVIDER){
                $this->sendPushNotification($request, $id);
            }
            $service->setTranslation('title', $locale, $request['title']);
            $service->setTranslation('video', $locale, $request['video']);
            $service->setTranslation('description', $locale, $request['description']);
            $service->setTranslation('content', $locale, $request['content']);
            $service->setTranslation('speciality_description', $locale, $request['speciality_description']);
            $service->setTranslation('meta_title', $locale, $request['meta_title']);
            $service->setTranslation('meta_description', $locale, $request['meta_description']);
            $data = Arr::except($request->all(), ['title', 'description', 'content', 'speciality_description', 'meta_title', 'meta_description','tax_id']);

            $service->update($data);

            if($request->type == ServiceTypeEnum::PROVIDER_SITE && isset($request->location)){
                $service->destination_location = $request->location;
            }
            $service->save();

            if (isset($request->category_id)) {
                if (in_array('selectAll', $request->category_id)) {
                    $allCategories = Category::pluck('id')->toArray();
                    $request->merge(['category_id' => $allCategories]);
                }

                $service->categories()->sync($request['category_id']);
            }
            
            if (isset($request->tax_id)) {
                $service->taxes()->sync($request['tax_id']);
            }

            if (!isset($request->service_id) && $request->is_random_related_services == true) {
                $rand_service_id = $request->category_id[array_rand($request->category_id)];
                $related_service_ids = Helpers::getRelatedServiceId($service, $rand_service_id, $service->id);
                $service->related_services()->sync($related_service_ids);
            }

            if (isset($request->service_id) && $request->is_random_related_services == false) {
                $service->related_services()->sync($request->service_id);
            }

            if ($request->image) {
                $uploadedImages = $request->image;
                $images = is_array($uploadedImages) ? $uploadedImages : [$uploadedImages];
                $existingImages = $service->getMedia('image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingImages as $media) {
                    $media->delete();
                }
                foreach ($images as $uploadedImage) {
                    if ($uploadedImage->isValid()) {
                        $service->addMedia($uploadedImage)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                    }
                    $service->media;
                }
            }

            if ($request['thumbnail']) {
                $existingThumbnail = $service->getMedia('thumbnail')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });

                foreach ($existingThumbnail as $media) {
                    $media->delete();
                }
                $service->addMedia($request['thumbnail'])->withCustomProperties(['language' => $locale])->toMediaCollection('thumbnail');
                $service->media;
            }

            if ($request->web_images) {
                $uploadedImages = $request->web_images;
                $web_images = is_array($uploadedImages) ? $uploadedImages : [$uploadedImages];
                $existingWebImages = $service->getMedia('web_images')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingWebImages as $media) {
                    $media->delete();
                }
                foreach ($web_images as $uploadedImage) {
                    if ($uploadedImage->isValid()) {
                        $service->addMedia($uploadedImage)->withCustomProperties(['language' => $locale])->toMediaCollection('web_images');
                    }
                    $service->media;
                }
            }
            if ($request['web_thumbnail']) {
                $webThumbnail = is_array($request['web_thumbnail']) ? $request['web_thumbnail'] : [$request['web_thumbnail']];
                $existingWebThumbnail = $service->getMedia('web_thumbnail')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingWebThumbnail as $media) {
                    $media->delete();
                }
                foreach ($webThumbnail as $image) {
                    if ($image->isValid()) {
                        $service->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('web_thumbnail');
                    }
                    $service->media;
                }
            }

            // Update FAQs
            if (isset($request->faqs) && is_array($request->faqs)) {
                $requestFaqIds = array_filter(array_column($request->faqs, 'id'));
                foreach ($request->faqs as $faq) {
                    $question = $faq['question'] ?? null;
                    $answer = $faq['answer'] ?? null;
                    if (is_null($question) || is_null($answer) || $question === '' || $answer === '' || strtolower($question) === 'null' || strtolower($answer) === 'null') {
                        continue;
                    }
                    if (isset($faq['id'])) {
                        $existingFaq = $service->faqs()->where('id', $faq['id'])->first();
                        if ($existingFaq) {
                            $existingFaq->setTranslation('question', $locale, $question);
                            $existingFaq->setTranslation('answer', $locale, $answer);
                            $existingFaq->save();
                            $requestFaqIds[] = $existingFaq->id;
                        }
                    } else {
                        $newFaq = $service->faqs()->create([
                            'question' => $question,
                            'answer' => $answer,
                        ]);

                        $newFaq->setTranslation('question', $locale, $question);
                        $newFaq->setTranslation('answer', $locale, $answer);
                        $newFaq->save();
                        $requestFaqIds[] = $newFaq->id;
                    }
                }
                $service->faqs()->whereNotIn('id', $requestFaqIds)->delete();
            }

            DB::commit();
            return redirect()->route('backend.service.index')->with('message', __('static.service.updated'));
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function sendPushNotification($request,$id){

        $token = $this->providers->where('id', $request->user_id)->pluck('fcm_token')->first();
        if ($token) {
            $notification = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => 'Commission Rate Changed',
                        'body' => 'The commission rate has been updated. Please check your dashboard for details.',
                    ],
                    'data' => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'type' => 'service',
                        'service_id' => $id
                    ],
                ],
            ];

            Helpers::pushNotification($notification);
        }
    }
    public function status($id, $status)
    {
        try {
            $service = $this->model->findOrFail($id);
            $currentUserRole = Helpers::getCurrentRoleName();
            $settings = Helpers::getSettings();
            $serviceAutoApprove = $settings['activation']['service_auto_approve'] ?? 0;

            if ($currentUserRole === RoleEnum::PROVIDER) {
                if ($service->user_id !== Auth::id()) {
                    return json_encode([
                        'success' => false,
                        'message' => __('static.not_authorized'),
                        'type' => 'error'
                    ]);
                }
                if ($serviceAutoApprove == 0) {
                    return json_encode([
                        'success' => false,
                        'message' => __('static.status_not_editable'),
                        'type' => 'error'
                    ]);
                }
            } elseif ($currentUserRole !== RoleEnum::ADMIN) {
                return json_encode([
                    'success' => false,
                    'message' => __('static.not_authorized'),
                    'type' => 'error'
                ]);
            }
            
            $service->update(['status' => $status]);

            return json_encode(['resp' => $service]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {

            $service = $this->model->findOrFail($id);
            $service->destroy($id);

            return redirect()->route('backend.service.index')->with('message', 'Service Deleted Successfully');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    protected function getProviders()
    {
        return $this->providers->role('provider')->where('status', true)->get();
    }

    private function getCategories()
    {
        return $this->category->where('category_type', CategoryType::SERVICE)
            ->where('status', true)
            ->pluck('title', 'id');
    }

    private function getDefaultCategories($service)
    {
        $categories = [];
        foreach ($service->categories as $category) {
            $categories[] = $category->id;
        }
        $categories = array_map('strval', $categories);

        return $categories;
    }

    private function getServices($service)
    {
        if (Request::is('backend/service/create')) {
            return $this->model->get();
        } else {
            return $this->model->get()->except($service->id);
        }
    }

    public function getZoneCategories($request){
        if (in_array('selectAll', $request->zone_id)) {
            $allZones = Zone::pluck('id')->toArray();
            $request->merge(['zone_id' => $allZones]);
        }
        $categories = $this->category->getDropdownOptions($request->zone_id);
        return response()->json($categories);
    }

    public function getZoneTaxes($request)
    {
        $taxes = [];
        $taxes = Tax::where('zone_id', $request['zone_id'])->get();
        return response()->json($taxes);
    }

    public function export($request)
    {
        try {
            $format = $request->input('format', 'xlsx');

            if ($format == 'csv') {

                return Excel::download(new ServiceExport, 'services.csv');
            }
            return Excel::download(new ServiceExport, 'services.xlsx');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function import($request)
    {
        try {
            $activeTab = $request->input('active_tab');

            $tempFile = null;

            if ($activeTab === 'direct-link') {

                $googleSheetUrl = $request->input('google_sheet_url');

                if (!$googleSheetUrl) {
                    throw new Exception(__('static.import.no_url_provided'));
                }

                if (!filter_var($googleSheetUrl, FILTER_VALIDATE_URL)) {
                    throw new Exception(__('static.import.invalid_url'));
                }

                $parsedUrl = parse_url($googleSheetUrl);
                preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $parsedUrl['path'], $matches);
                $sheetId = $matches[1] ?? null;
                parse_str($parsedUrl['query'] ?? '', $queryParams);
                $gid = $queryParams['gid'] ?? 0;

                if (!$sheetId) {
                    throw new Exception(__('static.import.invalid_sheet_id'));
                }

                $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";
                $response = Http::get($csvUrl);
               
                if (!$response->ok()) {
                    throw new Exception(__('static.import.failed_to_fetch_csv'));
                }

                $tempFile = tempnam(sys_get_temp_dir(), 'google_sheet_') . '.csv';
                file_put_contents($tempFile, $response->body());
            } elseif ($activeTab === 'local-file') {
                $file = $request->file('fileImport');

                if (!$file) {
                    throw new Exception(__('static.import.no_file_uploaded'));
                }

                if ($file->getClientOriginalExtension() != 'csv') {
                    throw new Exception(__('static.import.csv_file_allow'));
                }

                $tempFile = $file->getPathname();
            } else {
                throw new Exception(__('static.import.no_valid_input'));
            }

            Excel::import(new ServiceImport(), $tempFile);
            
            if ($activeTab === 'google_sheet' && file_exists($tempFile)) {
                unlink($tempFile);
            }

            return redirect()->back()->with('success', __('static.import.csv_file_import'));
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}