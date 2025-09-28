<?php

namespace App\Repositories\Backend;

use Exception;
use App\Helpers\Helpers;
use App\Models\Service;
use App\Models\Address;
use App\Models\Category;
use App\Enums\CategoryType;
use App\Enums\RoleEnum;
use App\Enums\ServiceTypeEnum;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Prettus\Repository\Eloquent\BaseRepository;
use Symfony\Component\HttpFoundation\Response;

class ProviderSiteServiceRepository extends BaseRepository
{
    protected $address;

    protected $providers;

    protected $category;

    protected $taxes;
    
    public function model()
    {
        $this->category = new Category();
        $this->providers = new User();
        $this->address = new Address();
        $this->taxes = new Tax();
        return Service::class;
    }

    public function create($attribute = [])
    {
        return view('backend.providerSiteService.create',[
            'services' => $this->getServices('service'),
            'providers' => $this->getProviders(),
            'categories' => $this->getCategories(),
            'countries' => Helpers::getCountries(),
        ]);
    }

    public function getTaxes()
    {
        return $this->taxes->where('status', true)->pluck('name', 'id');
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            if ($this->isProviderCanCreate() && $request->service_type == ServiceTypeEnum::PROVIDER_SITE) {
                $service = $this->model->create([
                    'service_type' => $request->service_type,
                    'price' => $request->price,
                    'title' => $request->title,
                    'status' => $request->status,
                    'discount' => $request->discount,
                    'duration' => $request->duration,
                    'duration_unit' => $request->duration_unit,
                    'user_id' => $request->provider_id,
                    'description' => $request->description,
                    'tax_id' => $request->tax_id,
                    'service_rate' => $request->service_rate,
                    'is_random_related_services' => $request->is_random_related_services,
                ]);

                if (isset($request->category_id)) {
                    $service->categories()->attach($request->category_id);
                    $service->categories;
                }

                if (! isset($request->service_id) && $request->is_random_related_services == true) {
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
                        $service->addMedia($image)->toMediaCollection('image');
                    }
                    $service->media;
                }

                if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
                    $service->addMedia($request->file('thumbnail'))->toMediaCollection('thumbnail');
                }

                // Store FAQs
                if (isset($request->faqs) && is_array($request->faqs)) {
                    foreach ($request->faqs as $faq) {
                        $service->faqs()->create([
                            'question' => $faq['question'],
                            'answer' => $faq['answer'],
                        ]);
                    }
                }

                DB::commit();
                return redirect()->route('backend.providerSiteService.index')->with('message', __('static.service.store'));
            }

            throw new Exception(__('static.not_allow_for_creation'), Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
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

                if (!$isAllowed) {
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

    public function edit($id)
    {
        $providerSiteService = $this->model->findOrFail($id);
        return view('backend.providerSiteService.edit', [
            'providerSiteService' => $providerSiteService,
            'providers' => $this->getProviders(),
            'categories' => $this->getCategories(),
            'countries' => Helpers::getCountries(),
            'default_categories' => $this->getDefaultCategories($providerSiteService),
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $providerSiteService = $this->model->findOrFail($id);
            if (isset($request['provider_id'])) {
                $request['user_id'] = $request['provider_id'];
                unset($request['provider_id']); 
            }
            $providerSiteService->update($request->all());

            if (isset($request->category_id)) {
                $providerSiteService->categories()->sync($request['category_id']);
            }

            if (!isset($request['service_id']) && $request['is_random_related_services'] == true) {
                $rand_service_id = $request->category_id[array_rand($request->category_id)];
                $related_service_ids = Helpers::getRelatedServiceId($providerSiteService, $rand_service_id, $providerSiteService->id);
                $providerSiteService->related_services()->sync($related_service_ids);
            }

            if (isset($request['service_id']) && $request['is_random_related_services'] == false) {
                $providerSiteService->related_services()->sync($request->service_id);
            }

            if ($request['image']) {
                $uploadedImages = $request['image'];
                $providerSiteService->clearMediaCollection('image');
                foreach ($uploadedImages as $uploadedImage) {
                    if ($uploadedImage->isValid()) {
                        $providerSiteService->addMedia($uploadedImage)->toMediaCollection('image');
                    }
                    $providerSiteService->media;
                }
            }

            if ($request['thumbnail']) {
                $providerSiteService->clearMediaCollection('thumbnail');
                $providerSiteService->addMedia($request['thumbnail'])->toMediaCollection('thumbnail');
            }

            // Update FAQs
            if (isset($request->faqs) && is_array($request->faqs)) {
                $requestFaqIds = array_filter(array_column($request->faqs, 'id'));
                foreach ($request->faqs as $faq) {
                    if (isset($faq['id'])) {
                        // Update existing FAQ
                        $existingFaq = $providerSiteService->faqs()->where('id', $faq['id'])->first();
                        if ($existingFaq) {
                            $existingFaq->update([
                                'question' => $faq['question'],
                                'answer' => $faq['answer'],
                            ]);
                        }
                    } else {
                        // Create new FAQ
                        $requestFaqIds[] = $providerSiteService->faqs()->create([
                            'question' => $faq['question'],
                            'answer' => $faq['answer'],
                        ])->id;
                    }
                }
                $providerSiteService->faqs()->whereNotIn('id', $requestFaqIds)->delete();
            }

            DB::commit();
            return redirect()->route('backend.providerSiteService.index')->with('success', __('static.service.updated'));
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $providerSiteService = $this->model->findOrFail($id);
            $providerSiteService->destroy($id);

            DB::commit();

            return redirect()->back()->with(['message' => 'Service deleted successfully']);
        } catch (Exception $e) {

            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function status($id, $status)
    {
        try {
            $providerSiteService = $this->model->findOrFail($id);
            $providerSiteService->update(['status' => $status]);

            return json_encode(['resp' => $providerSiteService]);
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteAll($ids)
    {
        DB::beginTransaction();
        try {

            $this->model->whereNot('system_reserve', true)->whereIn('id', $ids)->delete();

            return back()->with('message', 'Roles Deleted Successfully');
        } catch (Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    protected function getProviders()
    {
        return $this->providers->role('provider')->where('status', true)->pluck('name', 'id');
    }

    private function getCategories()
    {
        return $this->category->where('category_type', CategoryType::SERVICE)
            ->where('status', true)
            ->pluck('title', 'id');
    }

    private function getDefaultCategories($providerSiteService)
    {
        $categories = [];
        foreach ($providerSiteService->categories as $category) {
            $categories[] = $category->id;
        }
        $categories = array_map('strval', $categories);

        return $categories;
    }

    private function getServices($service)
    {
        if (Request::is('backend/providerSiteService/create')) {
            return $this->model->get();
        } else {
            return $this->model->get()->except($service->id);
        }
    }
}
