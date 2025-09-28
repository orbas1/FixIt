<?php

namespace App\Repositories\API;

use Exception;
use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use Illuminate\Support\Arr;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\Service;
use App\Models\ServiceFAQ;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Events\CreateServiceEvent;

class ServiceRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title' => 'like',
    ];

    protected $serviceFAQS;

    public function boot()
    {
        try {

            $this->pushCriteria(app(RequestCriteria::class));
        } catch (ExceptionHandler $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function model()
    {
        $this->serviceFAQS = new ServiceFAQ();

        return Service::class;
    }

    public function isProviderCanCreate()
    {
        if (Helpers::isUserLogin()) {
            $isAllowed = true;
            $roleName = Helpers::getCurrentRoleName();
            if ($roleName == RoleEnum::PROVIDER) {
                $isAllowed = false;
                $provider = Auth::user();
                $maxItems = $provider?->services()?->count() ?? 0;
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
            if (Helpers::isUserLogin()) {
               $roleName = Helpers::getCurrentRoleName();
               if ($roleName == RoleEnum::PROVIDER) {
                    $provider = Auth::user();
                    if ($provider->type == UserTypeEnum::COMPANY && !$provider->company) {
                        throw new Exception(__('static.provider.please_fill_company_details_service'), 422);
                    }    

                    $hasDocuments = $provider->UserDocuments()->exists();
                    if (!$hasDocuments) {
                        throw new Exception(__('static.provider.please_upload_documents_first_service'), 422);
                    }
                }
            }
            if ($this->isProviderCanCreate()) {
                $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : request('locale');
                $service_rate = $request->price - ($request->price * $request->discount / 100);
                $service = $this->model->create([
                    'type' => $request->type,
                    'price' => $request->price,
                    'title' => $request->title,
                    'status' => $request->status,
                    'discount' => $request->discount,
                    'video' => $request->video,
                    'per_serviceman_commission' => $request->per_serviceman_commission,
                    'duration' => $request->duration,
                    'user_id' => $request->provider_id ?? auth('api')->user()->id,
                    'meta_title' => $request->meta_title ?? null,
                    'description' => $request->description ?? null,
                    'speciality_description' => $request->speciality_description ?? null,
                    'is_featured' => $request->is_featured,
                    'duration_unit' => $request->duration_unit,
                    'service_rate' => $service_rate,
                    'isMultipleServiceman' => $request->isMultipleServiceman ?? 0,
                    'required_servicemen' => $request->required_servicemen,
                    'meta_description' => $request->meta_description,
                    'created_by_id' => auth('api')->user()?->id,
                    'destination_location' => $request->destination_location
                ]);

                if (isset($request['destination_location'])) {
                    $destination_location = [
                        'lat' => (float) $request['destination_location']['lat'],
                        'lng' => (float) $request['destination_location']['lng'],
                        'area' => $request['destination_location']['area'],
                        'address' => $request['destination_location']['address'],
                        'state_id' => $request['destination_location']['state_id'],
                        'country_id' => $request['destination_location']['country_id'],
                        'postal_code' => $request['destination_location']['postal_code'],
                        'city' => $request['destination_location']['city'],
                    ];
                    $request['destination_location'] = $destination_location;
                    $service->destination_location = $destination_location;
                }

                if (isset($request->category_id)) {
                    $service->categories()->attach($request->category_id);
                    $service->categories;
                }

                if (isset($request->tax_ids)) {
                    $service->taxes()->attach($request->tax_ids);
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

                if ($request->image) {
                    $images = $request->file('image');
                    foreach ($images as $image) {
                        $service->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                    }
                    $service->media;
                }

                if (isset($request->thumbnail) || $request->hasFile('thumbnail')) {
                    $service->addMedia($request->thumbnail)->withCustomProperties(['language' => $locale])->toMediaCollection('thumbnail');
                }

                if ($request->web_images) {
                    $images = $request->file('web_images');
                    foreach ($images as $image) {
                        $service->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('web_images');
                    }
                    $service->media;
                }

                if (isset($request->web_thumbnail) || $request->hasFile('web_thumbnail')) {
                    $service->addMedia($request->web_thumbnail)->withCustomProperties(['language' => $locale])->toMediaCollection('web_thumbnail');
                }

                if($request['title']){
                    $service->setTranslation('title', $locale, $request['title']);
                }
                if($request['description']){
                    $service->setTranslation('description', $locale, $request['description']);
                }
                if($request['content']){
                    $service->setTranslation('content', $locale, $request['content']);
                }
                if($request['speciality_description']){
                    $service->setTranslation('speciality_description', $locale, $request['speciality_description']);
                }
                if($request['meta_title']){
                    $service->setTranslation('meta_title', $locale, $request['meta_title']);
                }
                if($request['meta_description']){
                    $service->setTranslation('meta_description', $locale, $request['meta_description']);
                }
                if($request['video']){
                    $service->setTranslation('video', $locale, $request['video']);
                }

                // Store FAQs
                if (isset($request->faqs) && is_array($request->faqs)) {
                    foreach ($request->faqs as $faq) {
                        $newFaq = $service->faqs()->create([
                            'question' => $faq['question'],
                            'answer' => $faq['answer'],
                        ]);
                        if($newFaq){
                            $newFaq->setTranslation('question', $locale, $faq['question']);
                            $newFaq->setTranslation('answer', $locale, $faq['answer']);
                        }
                        $newFaq->save();
                    }
                }

                DB::commit();
                event(new CreateServiceEvent($service));
                return response()->json([
                    'message' => __('static.service.service_created_sucessfully'),
                    'success' => true
                ]);

            }

            throw new Exception(__('static.not_allow_for_creation'), 400);
        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $service = $this->model->findOrFail($id);
            $locale = request()->header('Accept-Lang') ? request()->header('Accept-Lang') : request('locale');
            if (isset($request['price'])) {
                $request['service_rate'] = $request['price'] - ($request['price'] * $request['discount'] / 100);
            }

            if(isset($request['title'])){
                $service->setTranslation('title', $locale, $request['title']);
            }
            if(isset($request['video'])){
                $service->setTranslation('video', $locale, $request['video']);
            }
            if(isset($request['description'])){
                $service->setTranslation('description', $locale, $request['description']);
            }
            if(isset($request['content'])){
                $service->setTranslation('content', $locale, $request['content']);
            }
            if(isset($request['speciality_description'])){
                $service->setTranslation('speciality_description', $locale, $request['speciality_description']);
            }
            if(isset($request['meta_title'])){
                $service->setTranslation('meta_title', $locale, $request['meta_title']);
            }
            if(isset($request['meta_description'])){
                $service->setTranslation('meta_description', $locale, $request['meta_description']);
            }

            $request = Arr::except($request, ['title', 'address_id', 'description', 'content', 'speciality_description', 'meta_title', 'meta_description']);

            if (isset($request['destination_location'])) {
                $destination_location = [
                    'lat' => (float) $request['destination_location']['lat'],
                    'lng' => (float) $request['destination_location']['lng'],
                    'area' => $request['destination_location']['area'],
                    'address' => $request['destination_location']['address'],
                    'state_id' => $request['destination_location']['state_id'],
                    'country_id' => $request['destination_location']['country_id'],
                    'postal_code' => $request['destination_location']['postal_code'],
                    'city' => $request['destination_location']['city'],
                ];
                $request['destination_location'] = $destination_location;
            }
            $service->update($request);

            if (isset($request['category_id'])) {
                $service->categories()->sync($request['category_id']);
                $service->categories;
            }

            if (isset($request['tax_ids'])) {
                $service->taxes()->sync($request['tax_ids']);
                $service->taxes();
            }

            if (!isset($request['service_id']) && isset($request['is_random_related_services']) == true) {
                $rand_service_id = $request['category_id'][array_rand($request['category_id'])];
                $related_service_ids = Helpers::getRelatedServiceId($service, $rand_service_id, $service->id);
                $service->related_services()->sync($related_service_ids);
            }

            if (isset($request['service_id']) && isset($request['is_random_related_services']) == false) {
                $service->related_services()->sync($request['service_id']);
            }

            if (isset($request['image'])) {
                $images = is_array($request['image']) ? $request['image'] : [$request['image']];
                $existingImages = $service->getMedia('image')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingImages as $media) {
                    $media->delete();
                }
                foreach ($images as $image) {
                    $service->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('image');
                }
                $service->media;
            }

            if (isset($request['thumbnail'])) {
                $existingImages = $service->getMedia('thumbnail')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingImages as $media) {
                    $media->delete();
                }
                $service->addMedia($request['thumbnail'])->withCustomProperties(['language' => $locale])->toMediaCollection('thumbnail');
            }

            if (isset($request['web_images'])) {
                $images = is_array($request['web_images']) ? $request['web_images'] : [$request['web_images']];
                $existingImages = $service->getMedia('web_images')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingImages as $media) {
                    $media->delete();
                }
                foreach ($images as $image) {
                    $service->addMedia($image)->withCustomProperties(['language' => $locale])->toMediaCollection('web_images');
                }
                $service->media;
            }

            if (isset($request['web_thumbnail'])) {
                $existingImages = $service->getMedia('web_thumbnail')->filter(function ($media) use ($locale) {
                    return $media->getCustomProperty('language') === $locale;
                });
                foreach ($existingImages as $media) {
                    $media->delete();
                }
                $service->addMedia($request['web_thumbnail'])->withCustomProperties(['language' => $locale])->toMediaCollection('web_thumbnail');
            }

            if (isset($request['faqs']) && is_array($request['faqs'])) {
                $requestFaqIds = array_filter(array_column($request['faqs'], 'id'));
                foreach ($request['faqs'] as $faq) {
                    if (isset($faq['id'])) {
                        // Update existing FAQ
                        $existingFaq = $service->faqs()->where('id', $faq['id'])->first();
                        if ($existingFaq) {
                            $existingFaq->setTranslation('question', $locale, $faq['question']);
                            $existingFaq->setTranslation('answer', $locale, $faq['answer']);
                            $existingFaq->save();
                        }
                    } else {
                        // Create new FAQ
                        $newFaq = $service->faqs()->create([
                            'question' => $faq['question'],
                            'answer' => $faq['answer'],
                        ]);

                        $newFaq->setTranslation('question', $locale, $faq['question']);
                        $newFaq->setTranslation('answer', $locale, $faq['answer']);
                        $newFaq->save();

                        $requestFaqIds[] = $newFaq->id;
                    }
                }
                $service->faqs()->whereNotIn('id', $requestFaqIds)->delete();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => __('static.service.updated'),
            ]);

        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {
            $service = $this->model->findOrFail($id);
            if ($service) {
                $service->delete();

                return response()->json([
                    'success' => true,
                    'message' => __('static.service.destroy'),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('static.service.service_not_found'),
                ]);
            }
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function storeServiceAddresses($request, $id)
    {
        DB::beginTransaction();
        try {
            $service = $this->model::findOrFail($id);
            if ($service) {
                if (isset($request->address_ids)) {
                    foreach ($request->address_ids as $addressId) {
                        $service->serviceAvailabilities()->create(['address_id' => $addressId]);
                    }
                }
                DB::commit();

                return response()->json([
                    'message' => __('static.service.service_address_store'),
                    'service' => $service,
                ]);
            } else {
                throw new Exception(__('static.service.invalid_service_id'), 404);
            }
        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function deleteServiceAddresses($id, $address_id)
    {
        DB::beginTransaction();
        try {
            $service = Service::findOrFail($id);
            if ($service) {
                $service_address = $service->serviceAvailabilities()
                    ->where('service_id', $service->id)
                    ->where('id', $address_id)
                    ->first();
                $service_address->delete();
                DB::commit();

                return response()->json([
                    'message' => __('static.service.service_address_destroy'),
                    'service' => $service,
                ]);
            } else {
                throw new ExceptionHandler(__('static.service.invalid_service_id'), 404);
            }
        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function serviceFAQS($request)
    {
        $service = $this->model::findOrFail($request->service_id);

        return $service->faqs;
    }
}
