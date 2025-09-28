<?php

use App\Models\Language;
use App\Models\SystemLang;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */
Route::get('/clean-unused-media', function () {
    $storagePath = storage_path('app/public');
    $directories = File::directories($storagePath);

    $deletedFilesCount = 0;
    $deletedFoldersCount = 0;

    foreach ($directories as $dir) {
        $folderId = basename($dir);

        $validFiles = Media::where('id', $folderId)->pluck('file_name')->toArray();
        $files = File::files($dir);

        foreach ($files as $file) {
            if (!in_array($file->getFilename(), $validFiles)) {
                File::delete($file->getPathname());
                $deletedFilesCount++;
            }
        }

        if (count(File::files($dir)) === 0) {
            File::deleteDirectory($dir);
            $deletedFoldersCount++;
        }
    }

    return redirect()->route('frontend.home')->with(['message', "$deletedFilesCount unused files and $deletedFoldersCount empty folders deleted."]);
});

Route::post('states', 'CountryStateController@getStates');
Route::get('getCountryCode', 'CountryStateController@getCountryCode');
Route::get('booking/invoice/{booking_number}', 'App\Http\Controllers\API\BookingController@getInvoice')->name('invoice');

Route::get('language/{locale}', function ($locale) {
    app()->setLocale($locale);
    session()?->put('locale', $locale);
    $language = SystemLang::where('locale', $locale)->first();
    Session::put('dir', $language?->is_rtl ? 'rtl' : 'ltr');
    return redirect()?->back();
})->name('lang');

Route::get('set-currency/{currency}', function ($currency) {
    session(['currency' => $currency]);
    return redirect()->back();
})->name('set.currency');

Route::group(['middleware' => [ 'route.access', 'localization', 'maintenance'], 'namespace' => 'Frontend', 'as' => 'frontend.'], function () {
    Route::middleware(['guest'])->group(function () {
        Route::get('login', 'LoginController@index')->name('login.index');
        Route::post('login', 'LoginController@login')->name('login');
        Route::get('forgot', 'LoginController@forgot')->name('forgot.index');
        Route::post('send-otp', 'LoginController@sendOTP')->name('forgot.otp');
        Route::get('register', 'RegisterController@index')->name('register.index');
        Route::post('register', 'RegisterController@register')->name('register');
        Route::get('confirm', 'LoginController@confirm')->name('confirm.index');
        Route::post('forgot/{token}', 'LoginController@verifyOTP')->name('confirm');
        Route::post('reset-password', 'LoginController@resetPassword')->name('reset');

        // Login with number
        Route::get('login/number', 'LoginController@loginWithNumber')->name('login.number');
        Route::post('login/number', 'LoginController@createUserOrOtp')->name('login.number.otp');
        Route::get('login/otp', 'LoginController@showOtp')->name('login.otp');
        Route::post('login/otp', 'LoginController@verifyOtpNumber')->name('login.otp.submit');
        Route::post('/verify-firebase-otp', 'LoginController@verifyFirebaseOtp')->name('firebase.verify-otp');

    });

    Route::get('/check-login', 'LoginController@checkLogin')->name('check.login');

    // Login with google
    Route::get('auth/redirect/{provider}', 'SocialLoginController@redirectToProvider')->name('redirectToProvider');
    Route::get('auth/callback/{provider}', 'SocialLoginController@handleProviderCallback');

    // Zone
    Route::get('get-address', 'ZoneController@getAddress');
    Route::get('check-zone', 'ZoneController@checkZone');
    Route::get('google-autocomplete', 'ZoneController@autoComplete');
    Route::get('get-coordinates', 'ZoneController@getCoordinates');

    // Home
    Route::get('/', 'HomeController@index')->name('home');

    Route::get('/sitemap.xml', 'SitemapController@generate');

    // Category
    Route::get('category', 'CategoryController@index')->name('category.index');

    // Blog
    Route::get('blog', 'BlogController@index')->name('blog.index');
    Route::get('blog/{slug}', 'BlogController@details')->name('blog.details');

    // About Us
    Route::get('about-us', 'AboutUsController@index')->name('about.index');

    // Blog
    Route::get('blog', 'BlogController@index')->name('blog.index');
    Route::get('blog/{slug}', 'BlogController@details')->name('blog.details');
    Route::get('page/{slug}', 'PageController@details')->name('page.details');

    // About Us
    Route::get('about-us', 'AboutUsController@index')->name('about.index');

    // Contact Us
    Route::get('contact-us', 'ContactUsController@index')->name('contact.index');
    Route::post('contact-us', 'ContactUsController@sendMail')->name('contact.mail');

    // Service
    Route::get('service', 'ServiceController@index')->name('service.index');
    Route::get('search/service', 'ServiceController@search')->name('service.search');
    Route::get('service/{slug}', 'ServiceController@details')->name('service.details');

    // Service Package Details
    Route::get('service-package', 'ServicePackageController@index')->name('service-package.index');
    Route::get('service-package/select-servicemen', 'ServicePackageController@selectServicemen')->name('service-package.select-servicemen');
    Route::get('service-package/{slug}', 'ServicePackageController@details')->name('service-package.details');

    // Provider
    Route::get('providers', 'ProviderController@index')->name('provider.index');
    Route::get('provider/{slug?}', 'ProviderController@details')->name('provider.details');

    // Cart
    Route::get('cart', 'CartController@index')->name('cart.index');
    Route::post('add-to-cart', 'CartController@addToCart')->name('cart.add');
    Route::patch('update-cart', 'CartController@update')->name('cart.update');
    Route::post('remove-cart', 'CartController@remove')->name('cart.remove');
    Route::post('apply-coupon', 'CartController@applyCoupon')->name('apply.coupon');
    Route::post('remove-coupon', 'CartController@removeCoupon')->name('remove.coupon');
    Route::post('coupon-handle',  'CartController@handleCoupon')->name('coupon.handle');

    // Addresses
    Route::resource('address', 'AddressController', ['except' => ['show']]);

    // Subscribe
    Route::post('subscribe', action: 'SubscribeController@store')->name('subscribe');

    // Comment
    Route::post('blogs/{blog}/comments', 'CommentController@store')->middleware('auth')->name('comments.store');
    Route::get('blogs-details/{blog}', 'CommentController@show')->name('blog.comment.details');

    // Pages
    Route::get('privacy-policy', 'PageController@privacy')->name('privacy.index');
    Route::get('terms-conditions', 'PageController@terms')->name('terms.index');

    Route::group(['middleware' => ['auth']], routes: function () {

        // Account
        Route::get('account/profile', 'AccountController@profile')->name('account.profile.index');
        Route::get('account/custom-job', 'AccountController@getCustomJobs')->name('account.customJob');
        Route::put('account/profile/update', 'AccountController@updateProfile')->name('account.profile.update');
        Route::put('account/password/update', 'AccountController@updatePassword')->name('account.password.update');
        Route::get('account/notification', 'AccountController@notification')->name('account.notification');
        Route::get('account/wallet', 'AccountController@wallet')->name('account.wallet');
        Route::post('account/wallet/top-up', 'AccountController@walletTopUp')->name('wallet.topUp');
        Route::get('account/address', 'AccountController@address')->name('account.address');
        Route::delete('account/address/{id}', 'AddressController@destroy')->name('account.address.delete');
        Route::get('account/password', 'AccountController@password')->name('account.password');
        Route::post('notifications/mark-as-read', 'AccountController@markAsRead')->name('notifications.markAsRead');
        Route::post('notifications/web/mark-as-read', 'AccountController@webMarkAsRead')->name('notifications.webMarkAsRead');

        // Logout
        Route::post('logout', 'AccountController@logout')->name('logout');

        // Reviews
        Route::get('account/review', 'AccountController@review')->name('account.review');
        Route::post('account/review', 'ReviewController@store')->name('account.review.store');
        Route::put('account/review/update/{id}', 'ReviewController@update')->name('account.review.update');
        Route::delete('account/review/{id}', 'ReviewController@destroy')->name('account.review.delete');

        // Booking
        Route::get('booking/payment', 'BookingController@payment')->name('payment.index');
        Route::post('booking/payment-now', 'BookingController@paymentNow')->name('payment.now');
        Route::resource('booking', 'BookingController');

        // Wishlist
        Route::get('wishlist', 'WishlistController@index')->name('wishlist.index');
        Route::get('wishlist/check', 'WishlistController@check')->name('wishlist.check');
        Route::post('wishlist/add', 'WishlistController@add')->name('wishlist.add');
        Route::post('wishlist/remove', 'WishlistController@remove')->name('wishlist.remove');

        // Service-booking
        Route::get('service-booking', 'BookingController@service')->name('booking.service');
        Route::post('service-booking', 'BookingController@serviceBooking')->name('booking.service.store');
        Route::get('service-package-booking/{slug}', 'BookingController@servicePackage')->name('booking.service-package');
        Route::post('service-package-booking', 'BookingController@servicePackageBooking')->name('booking.service-package.store');

        Route::post('custom-job/store','ServiceRequestController@store')->name('custom-job.store');
        Route::delete('custom-job/delete/{id}','ServiceRequestController@delete')->name('custom-job.delete');

        Route::post('bid/update','ServiceRequestController@UpdateBid')->name('bid.update');

    });
});

// Clear Cache
Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');
    Artisan::call('clear-compiled');
    Artisan::call('storage:link');
    Artisan::call('module:publish');

    return "cache cleared successfully";
});
