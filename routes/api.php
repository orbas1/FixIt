<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/webhooks/stripe', [\App\Http\Controllers\Webhook\StripeWebhookController::class, '__invoke'])
    ->withoutMiddleware(['throttle:api'])
    ->name('webhooks.stripe');

Route::group(['middleware' => ['localization']], function () {

    Route::post('/login', 'App\Http\Controllers\API\AuthController@login');
    Route::post('/social/login', 'App\Http\Controllers\API\AuthController@socialLogin');
    Route::get('/logout', 'App\Http\Controllers\API\AuthController@logout');
    Route::post('/forgot-password', 'App\Http\Controllers\API\AuthController@forgotPassword');
    Route::post('/verifyOtp', 'App\Http\Controllers\API\AuthController@verifyOtp');
    Route::post('/sendOtp', 'App\Http\Controllers\API\AuthController@sendOtp');
    Route::post('/verifySendOtp', 'App\Http\Controllers\API\AuthController@verifySendOtp');
    Route::post('/update-password', 'App\Http\Controllers\API\AuthController@updatePassword');
    Route::post('/register', 'App\Http\Controllers\API\AuthController@register');

    // Zones
    Route::apiResource('zone', 'App\Http\Controllers\API\ZoneController', ['except' => ['show']]);
    Route::get('zone-by-point', 'App\Http\Controllers\API\ZoneController@getZoneIds')->name('get.zoneId');

    // Countries & States
    Route::apiResource('state', 'App\Http\Controllers\API\StateController');
    Route::post('add-state', 'App\Http\Controllers\API\StateController@store');
    Route::apiResource('country', 'App\Http\Controllers\API\CountryController');

    // Settings & Options
    Route::get('settings', 'App\Http\Controllers\API\SettingController@frontSettings');
    Route::get('onboarding-screens', 'App\Http\Controllers\API\SettingController@getOnboardingScreens');
    Route::get('appearance', 'App\Http\Controllers\API\SettingController@getAppearance');
    Route::get('settings/advertisement', 'App\Http\Controllers\API\SettingController@getAdvertisement');

    Route::get('location/estimate', 'App\Http\Controllers\API\LocationController@estimate');

    // Payment Methods
    Route::get('payment-methods', 'App\Http\Controllers\API\PaymentMethodController@index');

    // Role With Permissions
    Route::apiResource('role', 'App\Http\Controllers\API\RoleController');

    // Users
    Route::get('user', 'App\Http\Controllers\API\UserController@index');

    Route::put('zone-update','App\Http\Controllers\API\AccountController@updateUserZone');

    // Providers
    Route::get('provider', 'App\Http\Controllers\API\ProviderController@index');
    Route::get('provider/{id}', 'App\Http\Controllers\API\ProviderController@show');
    Route::get('providerServices', 'App\Http\Controllers\API\ProviderController@getProviderServices');
    Route::post('provider-zone/update', 'App\Http\Controllers\API\ProviderController@updateProviderZones');

    // Banner
    Route::get('banner', 'App\Http\Controllers\API\BannerController@index');
    Route::get('banner/{id}', 'App\Http\Controllers\API\BannerController@show');

    // Servicemans
    Route::apiResource('serviceman', 'App\Http\Controllers\API\ServicemanController');

    // Service
    Route::get('service', 'App\Http\Controllers\API\ServiceController@index');

    Route::get('service/{service}', 'App\Http\Controllers\API\ServiceController@show');

    Route::get('featuredServices', 'App\Http\Controllers\API\ServiceController@isFeatured');
    Route::get('servicePackages', 'App\Http\Controllers\API\ServiceController@servicePackages');
    Route::get('servicePackages/{id}', 'App\Http\Controllers\API\ServiceController@servicePackages');
    Route::get('service-package/{service_package}', 'App\Http\Controllers\API\ServicePackageController@show');
    Route::get('feed/service-requests', 'App\Http\Controllers\API\FeedController@serviceRequests')
        ->name('api.feed.service-requests');

    // Categories
    Route::get('category', 'App\Http\Controllers\API\CategoryController@index');
    Route::get('category-services', 'App\Http\Controllers\API\CategoryController@categoryServices');

    Route::get('categoryList', 'App\Http\Controllers\API\CategoryController@getAllCategories');
    Route::get('category/{id}', 'App\Http\Controllers\API\CategoryController@show');

    // Currency
    Route::get('currency', 'App\Http\Controllers\API\CurrencyController@index');

    // Blog
    Route::get('blog', 'App\Http\Controllers\API\BlogController@index');
    Route::get('blog/{blog_id}', 'App\Http\Controllers\API\BlogController@show');

    // Blog-Category
    Route::get('blog-category', 'App\Http\Controllers\API\BlogController@index');

    // Pages
    Route::get('page', 'App\Http\Controllers\API\PageController@index');

    //Service FAQ's
    Route::get('service-faqs', 'App\Http\Controllers\API\ServiceController@serviceFAQS');

    Route::get('/providers/highest-ratings', 'App\Http\Controllers\API\ProviderController@getUsersWithHighestRatings');

    // Order Status
    Route::apiResource('bookingStatus', 'App\Http\Controllers\API\BookingStatusController', [
        'only' => ['index', 'show'],
    ]);

    /*------------------------------------ Provider API's --------------------------------------------------------- */

    // Auth API's
    Route::post('/provider-register', 'App\Http\Controllers\API\AuthController@registerProvider');

    //Document
    Route::apiResource('document', 'App\Http\Controllers\API\DocumentController', [
        'only' => ['index', 'show'],
    ]);

    //Tax
    Route::apiResource('tax', 'App\Http\Controllers\API\TaxController', [
        'only' => ['index', 'show'],
    ]);

    //Language
    Route::apiResource('language', 'App\Http\Controllers\API\LanguageController', [
        'only' => ['index', 'show'],
    ]);

    // System Language
    Route::apiResource('systemLang', 'App\Http\Controllers\API\SystemLangController', [
        'only' => ['index'],
    ]);

    // For User App
    Route::get('systemLang/translate/{lang?}', 'App\Http\Controllers\API\SystemLangController@getTranslate');

    // For Provider App
    Route::get('systemLang/provider/translate/{lang?}', 'App\Http\Controllers\API\SystemLangController@getProviderTranslate');

    Route::get('dashboard/user', 'App\Http\Controllers\API\UserController@getDashboardData');
    Route::get('dashboard-2/user', 'App\Http\Controllers\API\UserController@getDashboardData2');

    /*------------------------------------ End Provider API's ------------------------------------------------------ */

    Route::middleware('auth:sanctum')->group(function () {
        //Self
        Route::get('self', 'App\Http\Controllers\API\AccountController@self');

        Route::get('dashboard/provider', 'App\Http\Controllers\API\ProviderController@getDashboardData');
        Route::post('addserviceProofs', 'App\Http\Controllers\API\BookingController@addserviceProofs');
        Route::post('updateserviceProofs', 'App\Http\Controllers\API\BookingController@updateserviceProofs');
        Route::get('statistics/count', 'App\Http\Controllers\API\HomeController@index');
        Route::get('home/chart', 'App\Http\Controllers\API\HomeController@chart');
        Route::get('home/get-top-earning-categories', 'App\Http\Controllers\API\HomeController@getTopCategoryEarnings');
        Route::put('updateProfile', 'App\Http\Controllers\API\AccountController@updateProfile');
        Route::post('store/notification', 'App\Http\Controllers\API\NotificationController@store');
        Route::post('dummy/notification', 'App\Http\Controllers\API\NotificationController@dummyNotification');
        Route::post('update-company-details', 'App\Http\Controllers\API\ProviderController@updateCompanyDetails');

        // Escrows
        Route::apiResource('escrows', 'App\\Http\\Controllers\\API\\EscrowController', [
            'only' => ['index', 'store', 'show'],
        ]);
        Route::post('escrows/{escrow}/release', 'App\\Http\\Controllers\\API\\EscrowController@release')
            ->name('escrows.release');
        Route::post('escrows/{escrow}/refund', 'App\\Http\\Controllers\\API\\EscrowController@refund')
            ->name('escrows.refund');
        Route::post('escrows/{escrow}/payment-intent', 'App\\Http\\Controllers\\API\\EscrowController@paymentIntent')
            ->middleware('throttle:escrow-funding')
            ->name('escrows.payment-intent');

        // Secrets management (admin only)
        Route::apiResource('secrets', \App\Http\Controllers\API\SecretController::class)
            ->except(['create', 'edit']);

        // Privacy compliance
        Route::get('privacy/preferences', [\App\Http\Controllers\API\PrivacyPreferenceController::class, 'show'])
            ->name('privacy.preferences.show');
        Route::put('privacy/preferences', [\App\Http\Controllers\API\PrivacyPreferenceController::class, 'update'])
            ->name('privacy.preferences.update');
        Route::post('privacy/export', [\App\Http\Controllers\API\PrivacyPreferenceController::class, 'export'])
            ->name('privacy.export');
        Route::delete('privacy/account', [\App\Http\Controllers\API\PrivacyPreferenceController::class, 'destroy'])
            ->name('privacy.account.destroy');

        // Threads & messaging
        Route::get('threads', 'App\Http\Controllers\API\ThreadController@index');
        Route::post('threads', 'App\Http\Controllers\API\ThreadController@store');
        Route::get('threads/{thread:public_id}', 'App\Http\Controllers\API\ThreadController@show');
        Route::post('threads/{thread:public_id}/messages', 'App\Http\Controllers\API\ThreadMessageController@store')
            ->middleware('throttle:thread-messages');
        Route::post('threads/{thread:public_id}/read', 'App\Http\Controllers\API\ThreadReadController@store');

        // Delete Account
        Route::get('deleteAccount', 'App\Http\Controllers\API\AccountController@deleteAccount')->name('deleteAccount');

        Route::apiResource('user', 'App\Http\Controllers\API\UserController', [
            'only' => ['store', 'update', 'destroy'],
        ]);

        // category-commission
        Route::get('category-commission', 'App\Http\Controllers\API\CategoryController@getCategoryCommission');

        //Provider
        Route::apiResource('provider', 'App\Http\Controllers\API\ProviderController', [
            'only' => ['store', 'update', 'destroy'],
        ]);

        //Service
        Route::apiResource('service', 'App\Http\Controllers\API\ServiceController', [
            'only' => ['store', 'update', 'destroy'],
        ]);
        Route::post('addServiceAddress/{id}', 'App\Http\Controllers\API\ServiceController@storeServiceAddresses');
        Route::delete('deleteServiceAddress/{id}/{address_id}', 'App\Http\Controllers\API\ServiceController@deleteServiceAddresses');

        //Additional Service
        Route::apiResource('additional-service', 'App\Http\Controllers\API\AdditionalServiceController');

        //Service Package
        Route::patch('service-package/{id}/status', 'App\Http\Controllers\API\ServicePackageController@updateStatus');
        
        Route::apiResource('service-package', 'App\Http\Controllers\API\ServicePackageController', [
            'only' => ['store', 'update', 'destroy'],
        ]);

        //Serviceman
        Route::apiResource('serviceman', 'App\Http\Controllers\API\ServicemanController', [
            'only' => ['store', 'update', 'destroy'],
        ]);

        //Bank Detail
        Route::apiResource('bankDetail', 'App\Http\Controllers\API\BankDetailController')->except(['update']);
        Route::put('bankDetail/{user_id}', 'App\Http\Controllers\API\BankDetailController@update')->middleware('can:backend.bank_detail.edit');


        Route::get('company/{companyId}/addresses', 'App\Http\Controllers\API\CompanyController@getCompanyAddresses');
        Route::get('provider-time-slot/{provider_id}', 'App\Http\Controllers\API\ProviderController@providerTimeSlot');
        Route::post('provider-time-slot', 'App\Http\Controllers\API\ProviderController@storeProviderTimeSlot');
        Route::put('update-provider-time-slot', 'App\Http\Controllers\API\ProviderController@updateProviderTimeSlot');

        //Rate App
        Route::post('rate-app', 'App\Http\Controllers\API\RateAppController@store');

        //Address Status
        Route::post('changeAddressStatus/{id}', 'App\Http\Controllers\API\AddressController@changeAddressStatus');

        Route::apiResource('banner', 'App\Http\Controllers\API\BannerController', [
            'only' => ['store', 'update', 'destroy'],
        ]);

        Route::apiResource('advertisement', 'App\Http\Controllers\API\AdvertisementController')->except(['destroy']);

        Route::apiResource('category', 'App\Http\Controllers\API\CategoryController', [
            'only' => ['store', 'update', 'destroy'],
        ]);

        Route::apiResource('currency', 'App\Http\Controllers\API\CurrencyController', [
            'only' => ['store', 'update', 'destroy'],
        ]);

        //Booking
        Route::apiResource('booking', 'App\Http\Controllers\API\BookingController');
        Route::get('isValidTimeSlot', 'App\Http\Controllers\API\ProviderController@isValidTimeSlot');
        Route::post('checkout', 'App\Http\Controllers\API\CheckoutController@verifyCheckout');
        Route::post('booking/assign', 'App\Http\Controllers\API\BookingController@assign');
        Route::post('booking/add-extra-charges', 'App\Http\Controllers\API\BookingController@addExtraCharges');
        Route::post('booking/payment', 'App\Http\Controllers\API\BookingController@payment');
        Route::delete('booking/{booking}/extra-charges/{charge}','App\Http\Controllers\API\BookingController@deleteExtraCharge');

        //Payment
        Route::get('verifyPayment', 'App\Http\Controllers\API\BookingController@verifyPayment');
        Route::post('re-payment', 'App\Http\Controllers\API\BookingController@rePayment');

        // Booking Status
        Route::apiResource('bookingStatus', 'App\Http\Controllers\API\BookingStatusController', [
            'only' => ['store', 'update', 'destroy'],
        ]);
        Route::post('bookingStatus/deleteAll', 'App\Http\Controllers\API\BookingStatusController@deleteAll');
        Route::put('bookingStatus/{id}/{status}', 'App\Http\Controllers\API\BookingStatusController@status');

        // Fabourite-List
        Route::apiResource('favourite-list', 'App\Http\Controllers\API\FavouriteListController');

        Route::put('address/isPrimary/{id}', 'App\Http\Controllers\API\AddressController@isPrimary');

        Route::apiResource('address', 'App\Http\Controllers\API\AddressController', [
            'only' => ['index', 'store', 'update', 'destroy'],
        ]);

        // Notifications
        Route::get('notifications', 'App\Http\Controllers\API\NotificationController@index');
        Route::get('clear-notifications', 'App\Http\Controllers\API\NotificationController@clearNotifications');
        Route::put('notifications/markAsRead', 'App\Http\Controllers\API\NotificationController@markAsRead');
        Route::delete('notifications/{id}', 'App\Http\Controllers\API\NotificationController@destroy');
        Route::post('notifications/{id}/mark-as-read', 'App\Http\Controllers\API\NotificationController@markAsReadNotification');

        // Reviews
        Route::apiResource('review', 'App\Http\Controllers\API\ReviewController');
        Route::post('review/deleteAll', 'App\Http\Controllers\API\ReviewController@deleteAll');
        Route::get('provider/reviews/{provider_id}', 'App\Http\Controllers\API\ReviewController@getProviderReviews');

        // Wallets
        Route::get('wallet/consumer', 'App\Http\Controllers\API\WalletController@index');
        Route::post('credit/wallet', 'App\Http\Controllers\API\WalletController@credit');
        Route::post('debit/wallet', 'App\Http\Controllers\API\WalletController@debit');
        Route::post('provider/top-up', 'App\Http\Controllers\API\ProviderWalletController@topUp');
        Route::post('provider/withdraw-request', 'App\Http\Controllers\API\ProviderWalletController@withdrawRequest');
        Route::post('consumer/top-up', 'App\Http\Controllers\API\WalletController@topUp');

        // Provider Wallets
        Route::get('wallet/provider', 'App\Http\Controllers\API\ProviderWalletController@index')->middleware('can:backend.provider_wallet.index');
        Route::post('debit/providerWallet', 'App\Http\Controllers\API\ProviderWalletController@debit')->middleware('can:backend.provider_wallet.debit');
        Route::post('credit/providerWallet', 'App\Http\Controllers\API\ProviderWalletController@credit')->middleware('can:backend.provider_wallet.credit');

        // Serviceman Wallets
        Route::get('wallet/serviceman', 'App\Http\Controllers\API\ServicemanWalletController@index')->middleware('can:backend.serviceman_wallet.index');
        Route::post('serviceman/top-up', 'App\Http\Controllers\API\ServicemanWalletController@topUp')->middleware('can:backend.serviceman_withdraw_request.create');

        // Commission Histories
        Route::apiResource('commissionHistory', 'App\Http\Controllers\API\CommissionHistoryController', [
            'only' => ['index', 'show'],
        ]);

        // Delete Account
        Route::get('deleteAccount', 'App\Http\Controllers\API\AccountController@deleteAccount')->name('deleteAccount');

        // Account
        Route::put('update-password', 'App\Http\Controllers\API\AccountController@updatePassword');

        //verify serviceman
        Route::get('verify-user/{userId}', 'App\Http\Controllers\API\DocumentController@verifyUserDocument');
        Route::get('userDocuments/{userId}', 'App\Http\Controllers\API\DocumentController@getUserDocuments');

        //upload provider document
        Route::post('/upload-provider-document', 'App\Http\Controllers\API\DocumentController@uploadProviderDocument');

        // Bids
        Route::apiResource('bid','App\Http\Controllers\API\BidController',['except' => ['show']]);

        // Service Requests
        Route::apiResource('serviceRequest','App\Http\Controllers\API\ServiceRequestController')
            ->middleware('throttle:service-requests');

        Route::apiResource('customOffer', 'App\Http\Controllers\API\CustomOfferController');

        // Calculations
        Route::get('step-2/booking', 'App\Http\Controllers\API\BookingController@bookingStep2');

        // notification
        Route::get('notification', 'App\Http\Controllers\API\CronJobController@notification');

        // createZoomMeeting
        Route::post('generateZoomMeeting', 'App\Http\Controllers\API\BookingController@generateZoomMeeting');

        Route::get('/custom-offer-service', 'App\Http\Controllers\API\ServiceController@showCustomOffer');
    });
});
