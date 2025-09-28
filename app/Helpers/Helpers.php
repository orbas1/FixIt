<?php

namespace App\Helpers;

use App\Enums\AdvertisementStatusEnum;
use App\Enums\ServiceTypeEnum;
use App\Models\ServicemanWithdrawRequest;
use App\Models\WithdrawRequest;
use Exception;
use App\SMS\SMS;
use App\Enums\BannerTypeEnum;
use App\Enums\BookingEnum;
use App\Enums\BookingEnumSlug;
use App\Enums\BookingStatusReq;
use App\Enums\FrontEnum;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\RoleEnum;
use App\Enums\SortByEnum;
use App\Models\Address;
use App\Models\Advertisement;
use App\Models\BankDetail;
use App\Models\Blog;
use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\Category;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ExtraCharge;
use App\Models\HomePage;
use App\Models\Module;
use App\Models\ProviderWallet;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServicemanWallet;
use App\Models\ServicePackage;
use App\Models\CustomSmsGateway;
use App\Models\Setting;
use App\Models\State;
use App\Models\SystemLang;
use App\Models\Testimonial;
use App\Models\ThemeOption;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Zone;
use Carbon\Carbon;
use Google\Service\MyBusinessBusinessInformation\ServiceType;
use Google_Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Coupon\Entities\Coupon;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Nwidart\Modules\Facades\Module as NwidartModule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class Helpers
{

    public static function canCancelBooking($booking)
    {
        $settings = self::getSettings();
        if (isset($settings['general']['cancellation_restriction_hours'])) {
            $bookingDateTime = Carbon::parse($booking->date_time);
            $cutoffTime = $bookingDateTime->subHours($settings['general']['cancellation_restriction_hours']);
            return Carbon::now()->isBefore($cutoffTime);
        }
        return true;
    }

    public static function getEncrypter()
    {
        return App::make('encrypter');
    }

    public static function isEncrypted($key)
    {
        return strpos($key, 'eyJpdiI') === 0;
    }

    public static function getServiceIds() {}


    public static function encryptKey($key)
    {
        if (config('app.demo')) {
            if ($key) {
                return Self::getEncrypter()?->encrypt($key);
            }
        }

        return $key;
    }

    public static function decryptKey($key)
    {
        if (config('app.demo')) {
            if (self::isEncrypted($key)) {
                return self::getEncrypter()?->decrypt($key);
            }

            return $key;
        }

        return $key;
    }

    public static function getSmsGatewaySettings()
    {
        return CustomSmsGateway::first();
    }



    //removed
    public static function sendSMS($sendTo, $message)
    {
        try {
            $defaultSMSGateway = self::getDefaultSMSGateway();
            if($defaultSMSGateway && $defaultSMSGateway !== 'firebase'){
                if($defaultSMSGateway == 'custom'){
                    $sms = new SMS();
                    $data['to'] = $sendTo;
                    $data['message'] = $message;

                    $sms->sendSMS($data);
                }

                $module = NwidartModule::find($defaultSMSGateway);
                if ($module) {
                    if (!is_null($module) && $module?->isEnabled()) {
                        $moduleName = $module->getName();

                        $sms = 'Modules\\' . $moduleName . '\\SMS\\' . $moduleName;
                        if (class_exists($sms) && method_exists($sms, 'getIntent')) {
                            return $sms::getIntent($sendTo, $message);
                        }
                    }
                }
            }
        } catch (Exception $e) {
        }
    }

    public static function modules()
    {
        return Module::get();
    }

    public static function isUserLogin()
    {
        return Auth::guard('api')->check();
    }

    public static function isFirstAddress($address)
    {
        if ($address) {
            if ($address->user_id) {
                $addresses = Address::where('user_id', $address->user_id)->count();
            } else {
                $addresses = Address::where('service_id', $address->service_id)->count();
            }

            return $addresses > 1;
        }

        return true;
    }

    public static function getBookingById($id)
    {
        return Booking::findOrFail($id)?->first();
    }

    public static function getBookingByIdForProof($id)
    {
        return Booking::findOrFail($id);
    }

    public static function getCountries()
    {
        return Country::pluck('name', 'id');
    }

    public static function getCountryCodes()
    {
        return Country::get(['phone_code', 'id', 'iso_3166_2', 'flag', 'name'])->unique('phone_code');
    }

    public static function getStatesByCountryId($countryId)
    {
        return State::where('country_id', $countryId)->get(['name', 'id']);
    }

    public static function getCountryCode()
    {
       return Country::get(['phone_code', 'id', 'iso_3166_2', 'flag'])->unique('phone_code');
    }

    public static function getConsumerById($consumer_id)
    {
        return User::whereNull('deleted_at')->where('id', $consumer_id)->first();
    }

    public static function getZoneByPoint($latitude, $longitude)
    {
        $lat = (float) $latitude;
        $lng = (float) $longitude;
        $point = new Point($lat, $lng);
        return Zone::whereContains('place_points', $point)->get(['id', 'name']);
    }

    public static function mediaUpload($modelName, $fileName)
    {
        $media = $modelName->addMediaFromRequest($fileName)->toMediaCollection($fileName);
        $modelName->profile_image_url = $media->getFullUrl();
        $modelName->save();
    }

    public static function getRelatedServiceId($model, $category_id, $service_id)
    {
        return $model->whereRelation(
            'categories',
            function ($categories) use ($category_id) {
                $categories->Where('category_id', $category_id);
            }
        )->whereNot('id', $service_id)->pluck('id')->toArray();
    }

    public static function getWalletIdByUserId($userId)
    {
        return Wallet::where('consumer_id', $userId)->pluck('id')->first();
    }

    public static function getProviderWalletIdByproviderId($providerId)
    {
        return ProviderWallet::where('provider_id', $providerId)->pluck('id')->first();
    }

    public static function getTestimonials($paginate = null)
    {
        return Testimonial::paginate($paginate);
    }

    public static function getServicePackagesByIds($ids, $paginate = null)
    {
        return ServicePackage::whereIn('id', $ids)?->whereNull('deleted_at')?->paginate($paginate);
    }

    public static function getServicePackageById($ids)
    {
        return ServicePackage::where('id', $ids)?->whereNull('deleted_at')->first();
    }

    public static function getBlogsByIds($ids, $paginate = null)
    {
        return Blog::whereIn('id', $ids)?->whereNull('deleted_at')?->paginate($paginate);
    }

    public static function getDefaultCurrency()
    {
        $settings = self::getSettings();
        return $settings['general']['default_currency'];
    }

    public static function getCurrencyByCode($code)
    {
        return Currency::where('code', $code)?->whereNull('deleted_at')?->first();
    }

    public static function getDefaultCurrencySymbol()
    {
        if (session('currency')) {
            $currency = self::getCurrencyByCode(session('currency'));
            if ($currency) {
                return $currency?->symbol;
            }
        }

        $settings = self::getSettings();
        if (isset($settings['general']['default_currency'])) {
            $currency = $settings['general']['default_currency'];
            return $currency?->symbol;
        }
    }

    public static function getActiveCurrencies()
    {
        return Currency::where('status', true)?->whereNull('deleted_at')?->get();
    }

    public static function getServicemanWalletIdByServicemanId($serviceman_id)
    {
        return ServicemanWallet::where('serviceman_id', $serviceman_id)->pluck('id')->first();
    }

    public static function getProviders()
    {
        return User::role('provider')->whereNull('deleted_at');
    }

    public static function getProvidersByIds($ids)
    {
        return User::role('provider')->whereNull('deleted_at')->whereIn('id',$ids);
    }

    public static function getTopProvidersByRatings($provider_ids = [])
    {
        $providers = self::getProviders()?->get()->filter(function ($provider) {
            return $provider->review_ratings >= 0;
        });

        if(count($provider_ids)) {
            $providers = self::getProvidersByIds($provider_ids)?->get()->filter(function ($provider) {
                    return $provider->review_ratings >= 0;
            });
        }

        $providers->sortByDesc('review_ratings');
        return $providers;
    }

    public static function getServiceRequestSettings()
    {
        $settings = self::getSettings();
        if (isset($settings['service_request'])) {
            return $settings['service_request'];
        }
    }

    public static function getDefaultCurrencyCode()
    {
        if (session('currency')) {
            return session('currency');
        }

        $settings = self::getSettings();
        if (isset($settings['general']['default_currency'])) {
            $currency = $settings['general']['default_currency'];

            return $currency->code;
        }
    }

    public static function covertDefaultExchangeRate($amount)
    {
        return self::currencyConvert(self::getDefaultCurrencyCode(), $amount);
    }

    public static function getCurrencyExchangeRate($currencyCode)
    {
        return Currency::where('code', $currencyCode)?->pluck('exchange_rate')?->first();
    }

    public static function currencyConvert($currencySymbol, $amount)
    {
        $exchangeRate = self::getCurrencyExchangeRate($currencySymbol) ?? 1;
        $price = $amount * $exchangeRate;

        return self::roundNumber($price);
    }

    public static function getWalletBalanceByUserId($userId)
    {
        return Wallet::where('consumer_id', $userId)->pluck('balance')->first();
    }

    public static function getBannerCategories($catgoryType)
    {
        switch ($catgoryType) {
            case BannerTypeEnum::BANNERTYPE['category']:
                return Category::where(['status' => true])->get(['title', 'id']);
                break;

            case BannerTypeEnum::BANNERTYPE['provider']:
                return User::role('provider')->where('status', true)->get();
                break;
            default:
                return Service::where(['status' => true])->get(['title', 'id']);
                break;
        }

        return $categoryType;
    }

    public static function getCurrentRoleName()
    {
        $user = auth()->user();
        if (request()->expectsJson()) {
            $user = Auth::guard('api')->user();
        }

        return $user?->role?->name ?? $user?->roles?->first()?->name;
    }

    public static function getCurrentUser()
    {
        return Auth::guard('api')->user();
    }

    public static function getCurrentUserId()
    {
        return Auth::guard('api')->user()?->id;
    }

    public static function isDefaultLang($id)
    {
        $settings = self::getSettings();
        if ($settings) {
            if (isset($settings['general'])) {
                return $settings['general']['default_language_id'] == $id;
            }
        }
    }

    public static function getCoupon($data)
    {
        return Coupon::where([['code', 'LIKE', '%' . $data . '%'], ['status', true]])
            ->orWhere('id', 'LIKE', '%' . $data . '%')
            ->with(['services', 'exclude_services'])
            ->first();
    }

    public static function isCommandLineInstalled()
    {
        if (env('DB_DATABASE') && env('DB_USERNAME')) {
            DB::connection()->getPDO();
            if (DB::connection()->getDatabaseName()) {
                if (Schema::hasTable('seeders')) {

                    $completeSeeders = DB::table('seeders')
                        ->whereIn('name', config('enums.seeders'))
                        ->where('is_completed', true)->count();

                    if ($completeSeeders == count(config('enums.seeders'))) {
                        Storage::disk('local')->put(
                            config('config.migration'),
                            json_encode(
                                ['application_migration' => 'true']
                            )
                        );

                        return true;
                    }
                }
            }
        }

        return false;
    }

    public static function getFCMAccessToken()
    {
        $client = new Google_Client();
        $client->setAuthConfig(public_path('admin/assets/firebase.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        return $token['access_token'];
    }

    public static function getFirebaseJson()
    {
        $firebaseJson = json_decode(file_get_contents(public_path('admin/assets/firebase.json')), true);
        return $firebaseJson;
    }

    public static function pushNotification($notification)
    {
        try {

            $firebaseJson = self::getFirebaseJson();
            if ($firebaseJson) {
                $ch = curl_init();
                $url = "https://fcm.googleapis.com/v1/projects/{$firebaseJson['project_id']}/messages:send";
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . self::getFCMAccessToken()]);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $result = curl_exec($ch);

                curl_close($ch);
            }
        } catch (Exception $e) {

        }
    }

    public static function installation()
    {
        if (! self::isCommandLineInstalled()) {
            if (self::migration()) {
                if (Storage::disk('local')->exists(config('config.installation'))) {
                    $install = json_decode(Storage::get(config('config.installation')));
                    if ($install->application_installation === 'Completed') {
                        return true;
                    }

                    return true;
                }
            }

            return false;
        }

        return true;
    }

    public static function migration()
    {
        if (! self::isCommandLineInstalled()) {
            if (Storage::disk('local')?->exists(config('config.migration')) === true) {
                $install = json_decode(Storage::get(config('config.migration')));
                if ($install->application_migration == 'true') {
                    return true;
                }

                return true;
            }

            return false;
        }

        return true;
    }

    public static function getPaymentAccount($user_id)
    {
        return BankDetail::where('user_id', $user_id)->first();
    }

    public static function getCurrentProviderId()
    {
        if (self::isUserLogin()) {
            return Auth::guard('api')->user()?->id;
        }
    }

    public static function addMedia($model, $media, $collectionName)
    {
        return $model->addMedia($media)->toMediaCollection($collectionName);
    }

    public static function getSettings()
    {
        return Setting::pluck('values')->first();
    }

    public static function getAdmin()
    {
        return User::role(RoleEnum::ADMIN)->first();
    }

    public static function getRoleByUserId($user_id)
    {
        return User::findOrFail($user_id)->getRoleNames()->first();
    }

    public static function getProviderById($provider_id)
    {
        return User::where('id', $provider_id)->first();
    }

    public static function getProviderIdByServiceId($service_id)
    {
        return Service::withoutGlobalScope('exclude_custom_offers')->where('id', $service_id)->pluck('user_id')->first();
    }

    public static function getRoleNameByUserId($user_id)
    {
        return User::find($user_id)?->role?->name;
    }

    public static function getRelatedProductId($model, $category_id, $product_id = null)
    {
        return $model->whereRelation(
            'categories',
            function ($categories) use ($category_id) {
                $categories->Where('category_id', $category_id);
            }
        )->whereNot('id', $product_id)->inRandomOrder()->limit(6)->pluck('id')->toArray();
    }

    public static function getConsumerBooking($consumer_id, $service_id)
    {
        return Booking::where('consumer_id', $consumer_id)
            ->where('service_id', $service_id)->whereNotNull('parent_id')
            ->get();
    }

    public static function roundNumber($numb)
    {
        return number_format($numb, 2, '.', '');
    }

    public static function formatDecimal($value)
    {
        return floor($value * 100) / 100;
    }

    public static function getServicePrice($service_id)
    {
        return Service::withoutGlobalScope('exclude_custom_offers')->where('id', $service_id)->first(['price', 'discount']);
    }

    public static function getOriginalServicePrice($service)
    {
        $price = Service::where('id', $service['service_id'])->value('price') ?? 0;
        return $price ?? 0;
    }

    public static function getBookingStatusIdByName($name)
    {
        return Cache::rememberForever("booking_status_id_{$name}", function () use ($name) {
            return BookingStatus::where('name', $name)->pluck('id')->first();
        });
    }

    public static function getSalePrice($service)
    {
        $servicePrices = self::getPrice($service);

        return $servicePrices->price - (($servicePrices->price * $servicePrices->discount) / 100);
    }

    public static function getServicePackageSalePrice($service_package_id)
    {
        $servicePrices = ServicePackage::where('id', $service_package_id)->first(['price', 'discount']);
        return $servicePrices->price - (($servicePrices->price * $servicePrices->discount) / 100);
    }

    public static function getPackageSalePrice($service_package)
    {
        $packagePrice = self::getServicePackageSalePrice($service_package['service_package_id']);
        $serviceTotal = 0;
        if (!empty($service_package['services'])) {
            foreach ($service_package['services'] as $service) {

                if (!empty($service['additional_services'])) {
                    $serviceTotal = self::getSalePrice($service);
                    foreach ($service['additional_services'] as $additional_service_id) {

                        $serviceTotal += self::getAdditionalServiceSalePrice($additional_service_id);
                    }
                }
                $packagePrice += $serviceTotal;
            }
        }
        return $packagePrice;
    }

    public static function getAdditionalServicePrice($additionalService)
    {
        return Service::withoutGlobalScope('exclude_custom_offers')->where('id', $additionalService)->pluck('price')->first();
    }

    public static function getSubTotal($price, $quantity = 1)
    {
        return $price * $quantity;
    }

    public static function getTotalRequireServicemenByServiceId($service_id)
    {
        return Service::withoutGlobalScope('exclude_custom_offers')->where('id', $service_id)->pluck('required_servicemen')->first();
    }

    public static function getPackageSubTotal($price, $quantity = 1)
    {
        return $price * $quantity;
    }

    public static function getTotalAmount($services, $service_packages)
    {
        $subtotal = [];
        if ($service_packages) {
            foreach ($service_packages as $service_package) {
                $subtotal[] = self::getPackageSalePrice($service_package);
            }
        }

        foreach ($services as $service) {
            $serviceTotal = self::getSalePrice($service);

            if (!empty($service['additional_services'])) {
                foreach ($service['additional_services'] as $additional_service) {
                    $additionalServiceId = $additional_service['id'];
                    $qty = $additional_service['qty'] ?? 1;
                    $serviceTotal += self::getAdditionalServiceSalePrice($additionalServiceId) * $qty;
                }
            }
            $subtotal[] = $serviceTotal;
        }
        return array_sum($subtotal);
    }

    public static function getAdditionalServiceSalePrice($additional_service_id)
    {
        $additionalServicePrices = self::getServicePrice($additional_service_id);
        return $additionalServicePrices?->price;
    }

    public static function getPrice($service)
    {
        return self::getServicePrice($service['service_id']);
    }

    public static function walletIsEnable()
    {
        $settings = self::getSettings();

        return $settings['activation']['wallet_enable'];
    }

    public static function additionalServicesIsEnable()
    {
        $settings = self::getSettings();
        return $settings['activation']['additional_services'];
    }

    public static function couponIsEnable()
    {
        $settings = self::getSettings();

        return $settings['activation']['coupon_enable'];
    }

    public static function getCategoryCommissionRate($categories)
    {
        return Category::whereIn('id', $categories)->pluck('commission_rate');
    }

    public static function getBookingIdBySlug($slug)
    {
        return BookingStatus::where('slug', $slug)->first();
    }

    public static function getBookingStatusIdByReq($req_status)
    {
        $status = $req_status;
        switch ($req_status) {
            case BookingStatusReq::PENDING:
                $status = BookingEnum::PENDING;
                break;
            case BookingStatusReq::PENDING:
                $status = BookingEnum::PENDING;
                break;
            case BookingStatusReq::ASSIGNED:
                $status = BookingEnum::ASSIGNED;
                break;
            case BookingStatusReq::ON_THE_WAY:
                $status = BookingEnum::ON_THE_WAY;
                break;
            // case BookingStatusReq::DECLINE:
            //     $status = BookingEnum::DECLINE;
            //     break;
            case BookingStatusReq::ON_HOLD:
                $status = BookingEnum::ON_HOLD;
                break;

            case BookingStatusReq::START_AGAIN:
                $status = BookingEnum::START_AGAIN;
                break;

            case BookingStatusReq::COMPLETED:
                $status = BookingEnum::COMPLETED;
                break;
        }

        return self::getbookingStatusId($status);
    }

    public static function getbookingStatusIdBySlug($booking_status_slug)
    {
        return BookingStatus::where('slug', $booking_status_slug)?->value('id');
    }

    public static function getbookingStatusId($booking_status)
    {
        return BookingStatus::where('name', $booking_status)?->value('id');
    }

    public static function getbookingStatusName($booking_status_id)
    {
        return BookingStatus::where('name', $booking_status_id)?->value('name');
    }

    public static function getTopSellingServicec($services)
    {
        // $orders_count = $services->withCount(['bookings'])->get()->sum('bookings_count');
        // $services = $services->orderByDesc('bookings_count');
        // if (!$orders_count) {
        //     $services = (new Service)->newQuery();
        //     $services->whereRaw('1 = 0');

        //     return $services;
        // }

        // return $services;
        return $services->withCount('bookings')->having('bookings_count', '>', 0)->orderByDesc('bookings_count');
    }

    public static function getTopVendors($store)
    {
        $store = $store->orderByDesc('orders_count');
        $orders_count = $store->withCount(['orders'])->get()->sum('orders_count');
        if (! $orders_count) {
            $store = (new User)->newQuery();
            $store->whereRaw('1 = 0');

            return $store;
        }

        return $store;
    }

    public static function getProductStock($product_id)
    {
        return Service::where([['id', $product_id], ['status', true]])->first();
    }

    public static function getCountUsedPerConsumer($consumer, $coupon)
    {
        return Booking::where([['consumer_id', $consumer], ['coupon_id', $coupon]])->count();
    }

    public static function isBookingCompleted($bookings)
    {
        foreach ($bookings as $booking) {
            if ($booking->payment_status == PaymentStatus::COMPLETED && $booking->booking_status->slug == BookingEnumSlug::COMPLETED) {
                return true;
            }
        }

        return false;
    }

    public static function isAlreadyReviewed($consumer_id, $service_id)
    {
        $review = Review::where([
            ['consumer_id', $consumer_id],
            ['service_id', $service_id],
        ])->exists();
        if (!$review) {
            return true;
        }

        return false;
    }

    public static function isAlreadyReviewedServiceman($consumer_id, $serviceman_id)
    {
        $review = Review::where([
            ['consumer_id', $consumer_id],
            ['serviceman_id', $serviceman_id],
        ])->exists();
        if (! $review) {
            return true;
        }

        return false;
    }

    public static function getFilterBy($model, $filter_by)
    {
        switch ($filter_by) {
            case SortByEnum::TODAY:
                $model = $model->where('created_at', Carbon::now());
                break;

            case SortByEnum::LAST_WEEK:
                $startWeek = Carbon::now()->subWeek()->startOfWeek();
                $endWeek = Carbon::now()->subWeek()->endOfWeek();
                $model = $model->whereBetween('created_at', [$startWeek, $endWeek]);
                break;

            case SortByEnum::LAST_MONTH:
                $model = $model->whereMonth('created_at', Carbon::now()->subMonth()->month);
                break;

            case SortByEnum::THIS_YEAR:
                $model = $model->whereYear('created_at', Carbon::now()->year);
                break;
        }

        return $model;
    }

    public static function getProviderRatingList($provider_id)
    {
        $review = Review::where('provider_id', $provider_id)->get();
        return [
            $review->where('rating', 5)->count(),
            $review->where('rating', 4)->count(),
            $review->where('rating', 3)->count(),
            $review->where('rating', 2)->count(),
            $review->where('rating', 1)->count(),
        ];
    }

    public static function getProviderReviewRatings($provider){
        return $provider->reviews->avg('rating') ? round($provider->reviews->avg('rating'), 2) : 0;
    }

    public static function getServicemanReviewRatings($serviceman){

        return $serviceman->servicemanreviews->avg('rating') ? round($serviceman->servicemanreviews->avg('rating'), 2) : 0;
    }

    public static function getServiceManRatingList($serviceman_id)
    {
        $review = Review::where('serviceman_id', $serviceman_id)->get();

        return [
            $review->where('rating', 5)->count(),
            $review->where('rating', 4)->count(),
            $review->where('rating', 3)->count(),
            $review->where('rating', 2)->count(),
            $review->where('rating', 1)->count(),
        ];
    }

    public static function getServiceRatingList($service_id)
    {
        $review = Review::where('service_id', $service_id)->get();

        return [
            $review->where('rating', 5)->count(),
            $review->where('rating', 4)->count(),
            $review->where('rating', 3)->count(),
            $review->where('rating', 2)->count(),
            $review->where('rating', 1)->count(),
        ];
    }

    public static function getReviewRatings($service_id)
    {
        $review = Review::where('service_id', $service_id)->get();

        return [
            $review->where('rating', 1)->count(),
            $review->where('rating', 2)->count(),
            $review->where('rating', 3)->count(),
            $review->where('rating', 4)->count(),
            $review->where('rating', 5)->count(),
        ];
    }

    public static function getRatingPercentages(array $counts, int $total)
    {
        if (count($counts ?? [])) {
            return array_map(fn($count) => $total > 0 ? ($count / $total) * 100 : 0, $counts);
        }

        return [];
    }

    public static function getTotalProviders($start_date = null, $end_date = null)
    {
        $query = User::role(RoleEnum::PROVIDER)->where('system_reserve', false);

        if ($start_date && $end_date) {
            return $query->whereBetween('created_at', [$start_date, $end_date])->count();
        }

        return $query->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->count();
    }
    public static function getTotalProvidersPercentage($start_date = null, $end_date = null)
    {
        $sort = request('sort') ?? null;
        $previousRange = self::getPreviousDateRange($sort, request('start'), request('end'));
        $previousCount = self::getTotalProviders($previousRange['start'], $previousRange['end']);
        $customRangeCount = self::getTotalProviders($start_date, $end_date);

        return self::calculatePercentage($customRangeCount, $previousCount);
    }

    public static function getProviderWithdraw($start_date = null, $end_date = null)
    {
        $query = WithdrawRequest::query();
        $roleName = self::getCurrentRoleName();
        if ($roleName === RoleEnum::PROVIDER) {
            $query->where('provider_id', auth()->id());
        }

        if ($start_date && $end_date) {
            return $query->whereBetween('created_at', [$start_date, $end_date])->sum('amount');
        }

        return $query->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->sum('amount');
    }

    public static function getProviderWithdrawPercentage($start_date = null, $end_date = null)
    {
        $sort = request('sort') ?? null;
        $previousRange = self::getPreviousDateRange($sort, request('start'), request('end'));
        $previousCount = self::getProviderWithdraw($previousRange['start'], $previousRange['end']);
        $customRangeCount = self::getProviderWithdraw($start_date, $end_date);

        return self::calculatePercentage($customRangeCount, $previousCount);
    }

    public static function getServicemanWithdraw($start_date = null, $end_date = null)
    {
        $query = ServicemanWithdrawRequest::query();
        $roleName = self::getCurrentRoleName();
        if ($roleName === RoleEnum::SERVICEMAN) {
            $query->where('serviceman_id', auth()->id());
        }

        if ($start_date && $end_date) {
            return $query->whereBetween('created_at', [$start_date, $end_date])->sum('amount');
        }

        return $query->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->sum('amount');
    }
    public static function getServicemanWithdrawPercentage($start_date = null, $end_date = null)
    {
        $sort = request('sort') ?? null;
        $previousRange = self::getPreviousDateRange($sort, request('start'), request('end'));
        $previousCount = self::getServicemanWithdraw($previousRange['start'], $previousRange['end']);
        $customRangeCount = self::getServicemanWithdraw($start_date, $end_date);

        return self::calculatePercentage($customRangeCount, $previousCount);
    }

    public static function getServicemenCount()
    {
        return User::role(RoleEnum::SERVICEMAN)->where('system_reserve', false)->where('provider_id', auth()->user()->id)->count();
    }

    public static function getServiceTypeCount($start_date = null, $end_date = null)
    {
        $types = [
            'fixed' => ServiceTypeEnum::FIXED,
            'remotely' => ServiceTypeEnum::REMOTELY,
            'provider_site' => ServiceTypeEnum::PROVIDER_SITE
        ];

        $counts = [];

        $roleName = self::getCurrentRoleName();

        foreach ($types as $label => $type) {
            $query = Service::where('deleted_at', null)->where('type', $type);

            if ($roleName === RoleEnum::PROVIDER) {
                $query->where('user_id', auth()->id());
            }

            if ($start_date && $end_date) {
                $query->whereBetween('created_at', [$start_date, $end_date]);
            } else {
                $query->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'));
            }

            $counts[] = $query->count();
        }

        return [
            'series' => $counts,
        ];
    }



    public static function getTotalServicemen($start_date = null, $end_date = null)
    {
        $query = User::role(RoleEnum::SERVICEMAN);

        $roleName = self::getCurrentRoleName();
        if ($roleName === RoleEnum::PROVIDER) {
            $query->where('provider_id', auth()->id());
        }

        if ($start_date && $end_date) {
            return $query->whereBetween('created_at', [$start_date, $end_date])->count();
        }

        return $query->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->count();
    }


    public static function getTotalServicemenPercentage($start_date = null, $end_date = null)
    {
        $sort = request('sort') ?? null;
        $previousRange = self::getPreviousDateRange($sort, request('start'), request('end'));
        $previousCount = self::getTotalServicemen($previousRange['start'], $previousRange['end']);
        $customRangeCount = self::getTotalServicemen($start_date, $end_date);

        return self::calculatePercentage($customRangeCount, $previousCount);
    }


    public static function isZoneExists()
    {
        return Zone::whereNull('deleted_at')?->exists();
    }

    public static function getServicesCount($start_date = null, $end_date = null)
    {
        $query = Service::query();

        $roleName = self::getCurrentRoleName();
        if ($roleName === RoleEnum::PROVIDER) {
            $query->where('user_id', auth()->id());
        }


        if ($start_date && $end_date) {
            return $query->whereBetween('created_at', [$start_date, $end_date])->count();
        }

        return $query->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->count();
    }

    public static function getTotalServicesPercentage($start_date = null, $end_date = null)
    {
    $sort = request('sort') ?? null;
    $previousRange = self::getPreviousDateRange($sort, request('start'), request('end'));
    $previousCount = self::getServicesCount($previousRange['start'], $previousRange['end']);
    $customRangeCount = self::getServicesCount($start_date, $end_date);

    return self::calculatePercentage($customRangeCount, $previousCount);
    }

    public static function getTotalPayment($start_date = null, $end_date = null,$paymentType = null)
    {
        $roleName = self::getCurrentRoleName();

        if ($roleName === RoleEnum::PROVIDER) {
            $bookings = Booking::whereNull('parent_id')->whereHas('sub_bookings', function ($query) {
                $query->where('provider_id', auth()->user()->id);
            });
        } else if ($roleName === RoleEnum::SERVICEMAN) {
            $bookings = Booking::whereHas('servicemen', function ($query) {
                $query->where('users.id', auth()->user()->id);
            })->whereNotNull('parent_id');
        } else {
            $bookings = Booking::whereNotNull('parent_id');
        }

        if($paymentType!== null){
            $bookings->where('payment_method','cash');
        } else {
            $bookings->whereNot('payment_method','cash');
        }

        if ($start_date && $end_date) {
            return $bookings->whereBetween('created_at', [$start_date, $end_date])->sum('total');
        }

        return $bookings->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->sum('total');
    }

    public static function getTotalPaymentPercentage($start_date = null, $end_date = null,$paymentType = null)
    {
    $sort = request('sort') ?? null;
    $previousRange = self::getPreviousDateRange($sort, request('start'), request('end'));
    $previousCount = self::getTotalPayment($previousRange['start'], $previousRange['end'],$paymentType);
    $customRangeCount = self::getTotalPayment($start_date, $end_date,$paymentType);

    return self::calculatePercentage($customRangeCount, $previousCount);
    }

    public static function getPayment($id,$paymentType = null)
    {
        $role = self::getRoleByUserId($id);

        if ($role === RoleEnum::PROVIDER) {
            $bookings = Booking::whereNull('parent_id')->whereHas('sub_bookings', function ($query) use ($id) {
                $query->where('provider_id', $id);
            });
        } else if ($role === RoleEnum::SERVICEMAN) {
            $bookings = Booking::whereHas('servicemen', function ($query) use ($id) {
                $query->where('users.id', $id);
            })->whereNotNull('parent_id');
        } else {
            $bookings = Booking::whereNotNull('parent_id');
        }

        if($paymentType!== null){
            $bookings->where('payment_method','cash');
        } else {
            $bookings->whereNot('payment_method','cash');
        }

        return $bookings?->sum('total');
    }

    public static function getTotalBookings($start_date = null, $end_date = null)
    {
        $roleName = self::getCurrentRoleName();

        if ($roleName === RoleEnum::PROVIDER) {
            $bookings = Booking::whereNull('parent_id')->whereHas('sub_bookings', function ($query) {
                $query->where('provider_id', auth()->user()->id);
            });
        } else if ($roleName === RoleEnum::SERVICEMAN) {
            $bookings = Booking::whereHas('servicemen', function ($query) {
                $query->where('users.id', auth()->user()->id);
            })->whereNotNull('parent_id')
                ;
        } else {
            $bookings = Booking::whereNotNull('parent_id');
        }


        if ($start_date && $end_date) {
            return $bookings->whereBetween('created_at', [$start_date, $end_date])->count();
        }

        return $bookings->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->count();
    }

    public static function getTotalBookingPercentage($start_date = null, $end_date = null)
    {
    $sort = request('sort') ?? null;
    $previousRange = self::getPreviousDateRange($sort, request('start'), request('end'));
    $previousCount = self::getTotalBookings($previousRange['start'], $previousRange['end']);
    $customRangeCount = self::getTotalBookings($start_date, $end_date);

    return self::calculatePercentage($customRangeCount, $previousCount);
    }

    public static function getReviewsCount($start_date = null, $end_date = null)
    {
        $query = Review::where('deleted_at',null);

        $roleName = self::getCurrentRoleName();
        if ($roleName === RoleEnum::SERVICEMAN) {
            $query->where('serviceman_id', auth()->id());
        }

        if ($start_date && $end_date) {
            return $query->whereBetween('created_at', [$start_date, $end_date])->count();
        }

        return $query->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->count();
    }

    public static function getTotalReviewsPercentage($start_date = null, $end_date = null)
    {
    $sort = request('sort') ?? null;
    $previousRange = self::getPreviousDateRange($sort, request('start'), request('end'));
    $previousCount = self::getReviewsCount($previousRange['start'], $previousRange['end']);
    $customRangeCount = self::getReviewsCount($start_date, $end_date);

    return self::calculatePercentage($customRangeCount, $previousCount);
    }


    public static function getBookingsCount()
    {
        $roleName = self::getCurrentRoleName();
        if ($roleName === RoleEnum::PROVIDER) {
            $bookings = Booking::whereNull('parent_id')->whereHas('sub_bookings', function ($query) {
                $query->where('provider_id', auth()->user()->id);
            })->count();
        } else if ($roleName === RoleEnum::SERVICEMAN) {
            $bookings = Booking::whereHas('servicemen', function ($query) {
                $query->where('users.id', auth()->user()->id);
            })->whereNotNull('parent_id')
                ->count();
        } else {
            $bookings = Booking::whereNotNull('parent_id')?->count();
        }

        return $bookings;
    }

    public static function getCustomersCount()
    {
        return User::role(RoleEnum::CONSUMER)->where('system_reserve', false)->count();
    }

    public static function getTotalCustomers($start_date = null, $end_date = null)
    {
        $query = User::role(RoleEnum::CONSUMER)->where('system_reserve', false);

        if ($start_date && $end_date) {
            return $query->whereBetween('created_at', [$start_date, $end_date])->count();
        }

        return $query->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->count();
    }

    public static function getTotalCustomersPercentage($start_date = null, $end_date = null)
    {
        $sort = request('sort') ?? null;
        $previousRange = self::getPreviousDateRange($sort, request('start'), request('end'));
        $previousCount = self::getTotalCustomers($previousRange['start'], $previousRange['end']);
        $customRangeCount = self::getTotalCustomers($start_date, $end_date);

        return self::calculatePercentage($customRangeCount, $previousCount);
    }


    // Payment Gateway
    public static function isModuleEnable($moduleName)
    {
        return NwidartModule::isEnabled($moduleName);
    }

    public static function getAllModules()
    {
        return NwidartModule::all();
    }

    public static function getLanguages()
    {
        return SystemLang::where('status', true)?->get();
    }


    public static function getPaymentMethodList()
    {
        $settings = self::getSettings();
        $paymentMethods = [];

        $paymentMethods[] = [
            'name' => __('static.cash'),
            'slug' => PaymentMethod::COD,
            'image' => null,
            'status' => $settings['activation']['cash'] ? true : false,
        ];

        $modules = self::getAllModules();

        foreach ($modules as $module) {
            $paymentFile = module_path($module->getName(), 'Config/payment.php');

            if (file_exists($paymentFile)) {
                $payment = include $paymentFile;

                $configs = @$payment['configs'] ?? [];

                $allConfigsFilled = collect($configs)->every(function ($value) {
                    return !is_null($value) && $value !== '';
                });

                if (!$allConfigsFilled) {
                    continue;
                }

                $paymentMethods[] = [
                    'name' => $payment['name'],
                    'slug' => $payment['slug'],
                    'title' => $payment['title'],
                    'image' => url($payment['image']),
                    'status' => $module?->isEnabled(),
                    'processing_fee' => @$payment['processing_fee'],
                    'subscription' => @$payment['subscription'],
                ];
            }
        }

        return $paymentMethods;
    }


    public static function getActiveOnlinePaymentMethods()
    {
        $paymentMethods =  self::getPaymentMethodList();
        $filteredMethods = array_filter($paymentMethods, function ($method) {
            return $method['status'] === true;
        });

        return $filteredMethods;
    }

    public static function getActivePaymentMethods()
    {
        $paymentMethods =  self::getPaymentMethodList();
        $filteredMethods = array_filter($paymentMethods, function ($method) {
            return $method['status'] === true;
        });

        return $filteredMethods;
    }

    public static function getPaymentMethodConfigs()
    {
        $paymentMethods = [];
        $modules = self::getAllModules();
        foreach ($modules as $module) {
            $paymentFile = module_path($module->getName(), 'Config/payment.php');
            if (file_exists($paymentFile)) {
                $payment = include $paymentFile;
                $paymentMethods[] = [
                    'name' => $payment['name'],
                    'slug' => $payment['slug'],
                    'image' => url($payment['image']),
                    'title' => $payment['title'],
                    'processing_fee' => @$payment['processing_fee'],
                    'status' => $module?->isEnabled(),
                    'configs' => $payment['configs'],
                    'fields' => $payment['fields'],
                    'subscription' => @$payment['subscription']
                ];
            }
        }

        return $paymentMethods;
    }

    // SMS Gateways
    public static function getSMSGatewayList()
    {
        $smsGateways = [];
        $modules = self::getAllModules();
        foreach ($modules as $module) {
            $smsFile = module_path($module->getName(), 'Config/sms.php');
            if (file_exists($smsFile)) {
                $sms = include $smsFile;
                $smsGateways[] = [
                    'name' => $sms['name'],
                    'slug' => $sms['slug'],
                    'notes' => @$sms['notes'],
                    'image' => url($sms['image']),
                    'status' => $module?->isEnabled(),
                ];
            }
        }

        return $smsGateways;
    }


    public static function getSMSGatewayConfigs()
    {
        $smsGateways = [];
        $modules = self::getAllModules();
        foreach ($modules as $module) {
            $smsFile = module_path($module->getName(), 'Config/sms.php');
            if (file_exists($smsFile)) {
                $sms = include $smsFile;
                $smsGateways[] = [
                    'name' => $sms['name'],
                    'slug' => $sms['slug'],
                    'notes' => @$sms['notes'],
                    'image' => url($sms['image']),
                    'status' => $module?->isEnabled(),
                    'configs' => $sms['configs'],
                    'fields' => $sms['fields'],
                ];
            }
        }

        return $smsGateways;
    }

    public static function getDefaultSMSGateway()
    {
        $settings = self::getSettings();
        return $settings['general']['default_sms_gateway'] ?? null;
    }

    public static function getDefaultLanguageLocale()
    {
        $settings = self::getSettings();
        return $settings['general']['default_language']?->locale;
    }

    public static function getPaymentStatusColorClasses()
    {
        return [
            ucfirst(PaymentStatus::COMPLETED) => 'success',
            ucfirst(PaymentStatus::PENDING) => 'pending',
            ucfirst(PaymentStatus::PROCESSING) => 'positive',
            ucfirst(PaymentStatus::FAILED) => 'failed',
            ucfirst(PaymentStatus::EXPIRED) => 'expired',
            ucfirst(PaymentStatus::REFUNDED) => 'progress',
            ucfirst(PaymentStatus::CANCELLED) => 'critical',
          ];
    }

    public static function getAllProviders()
    {
        return User::role(RoleEnum::PROVIDER)->get();
    }

    public static function getAllUsers()
    {
        return User::role(RoleEnum::CONSUMER)->get();
    }

    public static function getAllZones()
    {
        return Zone::get();
    }

    public static function getbookingStatus()
    {
        return BookingStatus::get();
    }

    public static function getAllServices()
    {
        return Service::get();
    }

    public static function getAllVerifiedProviders()
    {
        return User::role(RoleEnum::PROVIDER)->where('is_verified', true)->where('status', true)->get();
    }

    public static function getTotalProviderBookingsByStatus($status, $provider_id)
    {
        return Booking::where('booking_status_id', self::getBookingStatusIdByReq($status))?->where('provider_id', $provider_id)->whereNotNull('parent_id')->whereNull('deleted_at')?->count();
    }

    public static function getTotalProviderBookings($provider_id)
    {
        return Booking::where('provider_id', $provider_id)->whereNotNull('parent_id')->whereNull('deleted_at')?->count();
    }

    // =================================== Frontend ======================================

    public static function getCurrentHomePage()
    {
        return HomePage::where('status', true)?->pluck('content')?->first();
    }

    public static function getServiceById($id)
    {
        return Service::where('id', $id)?->whereNull('deleted_at')?->with('additionalServices')?->first();
    }

    public static function getAdditionalServiceById($id)
    {
        return Service::where('id', $id)?->whereNull('deleted_at')?->whereNotNull('parent_id')?->first();
    }

    // public static function getServiceByProviderId($provider_id)
    // {
    //     return Service::where('user_id', $provider_id)?->where('status', true)?->whereNull('deleted_at')?->whereNull('parent_id')?->where('type', ServiceTypeEnum::FIXED)?->latest()?->paginate();
    // }

    public static function getServiceByProviderId($providerId)
    {
        $zoneIds = session('zoneIds', []);
        
        $query = Service::query()->where('user_id', $providerId)?->where('status', true);
        
        if (!empty($zoneIds)) {
            $query->whereHas('categories', function ($categoryQuery) use ($zoneIds) {
                $categoryQuery->whereHas('zones', function ($zoneQuery) use ($zoneIds) {
                    $zoneQuery->whereIn('zones.id', $zoneIds);
                });
            });
        } else {
            return collect();
        }

        $paginate = self::getThemeOptionsPaginate();
        return $query->paginate($paginate['service_per_page']);
    }


    public static function getServicesByIds($ids)
    {
        return Service::whereIn('id', $ids)?->whereNull('deleted_at')?->get();
    }

    public static function getThemeOptions()
    {
        return ThemeOption::first()?->options;
    }

    public static function dateTimeFormat($timestamp, $format)
    {
        return Carbon::parse($timestamp)->format($format);
    }

    public static function getFooterUsefulLinks()
    {
        return [
            [
                'slug' => '/',
                'name' => 'Home',
            ],
            [
                'slug' => 'category',
                'name' => 'Categories',
            ],
            [
                'slug' => 'service',
                'name' => 'Services',
            ],
            [
                'slug' => 'booking',
                'name' => 'Bookings',
            ],
            [
                'slug' => 'blog',
                'name' => 'Blogs',
            ],
            [
                'slug' => 'provider',
                'name' => 'Providers',
            ],
        ];
    }

    public static function getFooterPagesLinks()
    {
        return [
            [
                'slug' => 'privacy-policy',
                'name' => 'Privacy Policy',
            ],
            [
                'slug' => 'terms-conditions',
                'name' => 'Terms & Conditions',
            ],
            [
                'slug' => 'contact-us',
                'name' => 'Contact Us',
            ],
            [
                'slug' => 'about-us',
                'name' => 'About Us',
            ],
        ];
    }

    public static function getFooterOthersLinks()
    {
        return [
            [
                'slug' => 'account/profile',
                'name' => 'My Account',
            ],
            [
                'slug' => 'wishlist',
                'name' => 'Wishlist',
            ],
            [
                'slug' => 'booking',
                'name' => 'Bookings',
            ],
            [
                'slug' => 'providers',
                'name' => 'Providers',
            ],
            [
                'slug' => 'service',
                'name' => 'Services',
            ],
        ];
    }


    public static function getAssetUrl($url)
    {
        return str_replace(config('app.url'), "", $url);
    }

    public static function getServicemenByProviderId($provider_id)
    {
        return User::where('provider_id', $provider_id)?->whereNull('deleted_at')?->get();
    }

    public static function getUsersByIds($ids)
    {
        return User::whereIn('id', $ids)?->whereNull('deleted_at')?->get();
    }

    public static function getPerServicemen($service)
    {
        $reqServicemen = $service?->required_servicemen;
        return $service?->service_rate / (($reqServicemen > 0) ? $reqServicemen : 1);
    }

    public static function getServiceCategories()
    {
        return Category::where('status', true)?->where('category_type', 'service')?->whereNull('deleted_at')?->get();
    }

    public static function getCoupons()
    {
        return Coupon::whereNull('deleted_at')?->get();
    }

    public static function getActiveBookingStatusList()
    {
        return BookingStatus::where('status', true)?->whereNull('deleted_at')?->get();
    }

    public static function isExtraChargePaymentPending($booking_id)
    {
        return ExtraCharge::where('booking_id', $booking_id)
            ?->whereNot('payment_status', PaymentStatus::COMPLETED)
            ?->exists() ?? false;
    }

    public static function getTotalExtraCharges($booking_id)
    {
        return ExtraCharge::where('booking_id', $booking_id)?->sum('total');
    }

    public static function getTotalAddonCharges($booking_id)
    {
        return DB::table('booking_additional_services')
            ->where('booking_id', $booking_id)
            ->sum('total_price');
    }

    public static function getExtraChargePaymentAmount($booking_id)
    {
        return ExtraCharge::where('booking_id', $booking_id)
            ?->whereNot('payment_status', PaymentStatus::COMPLETED)
            ?->sum('total');
    }


    public static function getServicesByZoneIds($zoneIds)
    {
        return Service::whereHas('categories', function (Builder $categories) use ($zoneIds) {
            $categories->whereHas('zones', function (Builder $zones) use ($zoneIds) {
                $zones->WhereIn('zones.id', $zoneIds);
            });
        });
    }

    public static function getCategoriesByZoneIds($zoneIds)
    {
        return Category::whereRelation('zones', function ($zones) use ($zoneIds) {
            $zones->WhereIn('zone_id', $zoneIds);
        });
    }

    public static function getCategoriesByIds($ids)
    {
        return Category::whereIn('id', $ids)?->whereNull('deleted_at')?->get();
    }

    public static function getThemeOptionsPaginate()
    {
        $themeOptions = self::getThemeOptions();
        return $themeOptions['pagination'];
    }

    public static function getServices($ids = [])
    {
        $zoneIds = session('zoneIds', []);
        $query = Service::query();

        // Always apply zone filter
        if (count($zoneIds)) {
            $query->whereHas('categories', function ($categories) use ($zoneIds) {
                $categories->whereHas('zones', function ($zones) use ($zoneIds) {
                    $zones->whereIn('zones.id', $zoneIds);
                });
            });
        } else {
            // If no zone is selected, return empty collection
            return collect();
        }

        // Apply service ID filter if provided
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $paginate = self::getThemeOptionsPaginate();
        return $query->paginate($paginate['service_per_page']);
    }

    public static function getCategories($ids = [])
    {
        $zoneIds = session('zoneIds', []);
        $query = Category::query();
        
        if (!empty($zoneIds)) {
            $query->whereHas('zones', function ($zones) use ($zoneIds) {
                $zones->whereIn('zone_id', $zoneIds);
            });
        }

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $paginate = self::getThemeOptionsPaginate();
        return $query->paginate($paginate['categories_per_page']);
    }

    public static function getBookingsCountById($id)
    {
        $role = self::getRoleByUserId($id);
        $bookings = 0;

        if ($role == RoleEnum::PROVIDER) {
            $bookings = Booking::whereNull('parent_id')
                ->whereHas('sub_bookings', function ($query) use ($id) {
                    $query->where('provider_id', $id);
                })->count();
        } elseif ($role == RoleEnum::CONSUMER) {
            $bookings = Booking::whereNull('parent_id')
                ->whereHas('sub_bookings', function ($query) use ($id) {
                    $query->where('consumer_id', $id);
                })->count();
        } elseif ($role == RoleEnum::SERVICEMAN) {
            $bookings = Booking::whereHas('servicemen', function ($query)  use ($id) {
                $query->where('users.id', $id);
            })->whereNotNull('parent_id')
                ->count();
        }

        return $bookings;
    }

    public static function getLanguageByLocale($locale)
    {

        return SystemLang::where('locale', $locale)?->whereNull('deleted_at')->first();
    }

    public static function getServicemenCountById($id)
    {
        $role = self::getRoleByUserId($id);

        if ($role == RoleEnum::PROVIDER) {
            $servicemen = User::role(RoleEnum::SERVICEMAN)->where('system_reserve', false)->where('provider_id', $id)->count();
        }
        return $servicemen ?? 0;
    }

    public static function getServicesCountById($id)
    {

        $role = self::getRoleByUserId($id);

        if ($role == RoleEnum::PROVIDER) {
            $services = Service::where('user_id', $id);
        }
        return $services?->count() ?? 0;
    }

    public static function getHomePageAdvertiseBanners()
    {
        $zoneIds = session('zoneIds', []);
        return cache()->remember("dashboard_home_banner_advertisements_" . implode('_', $zoneIds), 60, function() use ($zoneIds) {
            return Advertisement::where('type', 'banner')
                ->where('screen', 'home')
                ->whereNotIn('status', [
                    AdvertisementStatusEnum::PENDING,
                    AdvertisementStatusEnum::PAUSED,
                    AdvertisementStatusEnum::REJECTED,
                    AdvertisementStatusEnum::EXPIRED,
                ])
                ->when(count($zoneIds), function ($query) use ($zoneIds) {
                    return $query->whereIn('zone', $zoneIds);
                })->with(['media', 'provider:id,slug'])
                ->get();
        });
    }

    public static function getCategoryPageAdvertiseBanners()
    {
        $zoneIds = session('zoneIds', []);
        return cache()->remember("dashboard_category_banner_advertisements_" . implode('_', $zoneIds), 60, function() use ($zoneIds) {
            return Advertisement::where('type', 'banner')
                ->where('screen', 'category')
                ->whereNotIn('status', [
                    AdvertisementStatusEnum::PENDING,
                    AdvertisementStatusEnum::PAUSED,
                    AdvertisementStatusEnum::REJECTED,
                    AdvertisementStatusEnum::EXPIRED,
                ])
                ->when(count($zoneIds), function ($query) use ($zoneIds) {
                    return $query->whereIn('zone', $zoneIds);
                })->with(['media', 'provider:id,slug'])
                ->get();
        });
    }

    public static function getHomePageAdvertiseServices()
    {
        $zoneIds = session('zoneIds', []);
        return cache()->remember("dashboard_service_advertisements_" . implode('_', $zoneIds), 60, function() use ($zoneIds) {
            return Advertisement::where('type', 'service')
                ->where('screen', 'home')
                ->whereNotIn('status', [
                    AdvertisementStatusEnum::PENDING,
                    AdvertisementStatusEnum::PAUSED,
                    AdvertisementStatusEnum::REJECTED,
                    AdvertisementStatusEnum::EXPIRED,
                ])
                ->when(count($zoneIds), function ($query) use ($zoneIds) {
                    return $query->whereIn('zone', $zoneIds);
                })->get();
        });
    }

    public static function getCategoryPageAdvertiseServices()
    {
        $zoneIds = session('zoneIds', []);
        return cache()->remember("dashboard_service_advertisements_" . implode('_', $zoneIds), 60, function() use ($zoneIds) {
            return Advertisement::where('type', 'service')
                ->where('screen', 'category')
                ->whereNotIn('status', [
                    AdvertisementStatusEnum::PENDING,
                    AdvertisementStatusEnum::PAUSED,
                    AdvertisementStatusEnum::REJECTED,
                    AdvertisementStatusEnum::EXPIRED,
                ])
                ->when(count($zoneIds), function ($query) use ($zoneIds) {
                    return $query->whereIn('zone', $zoneIds);
                })->get();
        });
    }

    public static function getBalanceById($id)
    {
        $role = self::getRoleByUserId($id);
        if ($role == RoleEnum::PROVIDER) {
            $provider = User::findOrFail($id);
            $balance = $provider?->providerWallet?->balance;
        } elseif ($role == RoleEnum::SERVICEMAN) {
            $servicemen = User::findOrFail($id);

            $balance = $servicemen?->servicemanWallet?->balance;
        } elseif ($role == RoleEnum::CONSUMER) {
            $consumer = User::findOrFail($id);
            $balance = $consumer?->wallet?->balance;
        }
        return $balance ?? 0.0;
    }

    public static function getWithdrawAmountById($id)
    {
        $role = self::getRoleByUserId($id);
        if ($role == RoleEnum::PROVIDER) {
            $provider = User::findOrFail($id);
            $balance = $provider?->providerWithdrawRequest?->sum('amount');

        } elseif ($role == RoleEnum::SERVICEMAN) {
            $servicemen = User::findOrFail($id);

            $balance = $servicemen?->servicemanWallet?->balance;
        }
        return $balance ?? 0.0;
    }

    public static function getReviewsCountById($id)
    {
        $role = self::getRoleByUserId($id);
        if ($role == RoleEnum::PROVIDER) {
            $provider = User::findOrFail($id);
            $reviews = $provider?->reviews?->where('provider_id',$id)?->count();

        } elseif ($role == RoleEnum::SERVICEMAN) {
            $servicemen = User::findOrFail($id);

            $balance = $servicemen?->servicemanWallet?->balance;
        }
        return $reviews ?? 0;
    }

    public static function isFileExistsFromURL($url, $placeHolder = false)
    {
        if(!is_null($url) && !empty($url)) {
            $localFilePath = public_path(self::getAssetUrl($url));
            if(file_exists($localFilePath)) {
                return asset(self::getAssetUrl($url));
            }
        }

        if($placeHolder) {
            return FrontEnum::getPlaceholderImageUrl();
        }

        return false;
    }

    public static function getBookingsCountByStatus($status)
    {
        $role = self::getCurrentRoleName();
        $providerId = null;
        $servicemanId = null;

        if ($role == RoleEnum::PROVIDER) {
            $providerId = auth()?->user()?->id;
        } elseif ($role == RoleEnum::SERVICEMAN) {
            $servicemanId = auth()?->user()?->id;
        }
        $bookings = Booking::getFilteredBookings($providerId,$servicemanId);

        $bookingCount = $bookings->filter(function ($booking) use ($status) {
            return $booking->booking_status?->slug === $status;
        })->count();


        return $bookingCount;
    }

    public static function getStartAndEndDate($sort, $startDate = null, $endDate = null)
    {
      $startCurrentDate = Carbon::now();
      $endCurrentDate = Carbon::now();

      switch ($sort) {
        case 'today':
          return [
            'start' => $startCurrentDate->startOfDay(),
            'end' => $endCurrentDate->endOfDay(),
          ];

        case 'this_week':
          return [
            'start' => $startCurrentDate->startOfWeek(),
            'end' => $endCurrentDate->endOfWeek(),
          ];

        case 'this_month':
          return [
            'start' => $startCurrentDate->startOfMonth(),
            'end' => $endCurrentDate->endOfMonth(),
          ];

        case 'this_year':
          return [
            'start' => $startCurrentDate->startOfYear(),
            'end' => $endCurrentDate->endOfYear(),
          ];

        case 'custom':
          if ($startDate && $endDate) {
            return [
              'start' => Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay(),
              'end' => Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay(),
            ];
          }
          break;
        default:
          return [
            'start' => $startCurrentDate->startOfYear(),
            'end' => $endCurrentDate->endOfYear(),
          ];
      }
    }


    public static function calculatePercentage($customRangeCount, $todayCount)
    {
        if ($todayCount == 0) {
            $todayCount = 1;
            $difference = 1;
            $percentage = ($customRangeCount / $todayCount) * 100;
        } else {
        $difference = $customRangeCount - $todayCount;
        $percentage = ($difference / $todayCount) * 100;
        }

        return [
        'status' => $difference > 0 ? 'increase' : ($difference < 0 ? 'decrease' : 'no_change'),
        'percentage' => number_format(($percentage), 2),
        ];
    }

    public static function getPreviousDateRange($sort, $start_date = null, $end_date = null)
    {
        switch ($sort) {
        case 'today':
            return [
            'start' => Carbon::yesterday()->startOfDay(),
            'end' => Carbon::yesterday()->endOfDay(),
            ];

        case 'this_week':
            return [
            'start' => Carbon::now()->subWeek()->startOfWeek(),
            'end' => Carbon::now()->subWeek()->endOfWeek(),
            ];

        case 'this_month':
            return [
            'start' => Carbon::now()->startOfMonth()->subMonthsNoOverflow(),
            'end' => Carbon::now()->subMonthsNoOverflow()->endOfMonth(),
            ];

        case 'this_year':
            return [
            'start' => Carbon::now()->subYear()->startOfYear(),
            'end' => Carbon::now()->subYear()->endOfYear(),
            ];

        case 'custom':
            if ($start_date && $end_date) {
            return [
                'start' => Carbon::createFromFormat('d-m-Y', $start_date)->subYear()->startOfDay(),
                'end' => Carbon::createFromFormat('d-m-Y', $end_date)->subYear()->endOfDay(),
            ];
            }
            break;

        default:
            return [
            'start' => Carbon::now()->subMonth()->startOfMonth(),
            'end' => Carbon::now()->subMonth()->endOfMonth(),
            ];
        }
    }

    public static function formatServiceType($type)
    {
        $mapping = [
            ServiceTypeEnum::PROVIDER_SITE =>  __('static.provider_site'),
            ServiceTypeEnum::FIXED => __('static.user_site'),
            ServiceTypeEnum::REMOTELY => __('static.remotely'),
        ];

        return $mapping[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }


    public static function isDemoModeEnabled()
    {
        try {

            return env('ENABLE_DEMO_MODE');

        } catch (Exception $e) {

            return false;
        }
    }


    public static function getDefaultCountryCode()
    {
        $settings = self::getSettings();
        return $settings['general']['country_code'] ?? 1;
    }
}
