<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['verify' => false, 'register' => false]);
Route::get('/become-provider', 'Auth\BecomeProviderController@index')->name('become-provider.index');
Route::post('/become-provider', 'Auth\BecomeProviderController@store')->name('become-provider.store');
Route::post('/set-theme', 'Backend\SettingsController@setTheme')->name('set-theme');
Route::get('placeId', 'Backend\ProviderController@getPlaceId')->name('placeId');
Route::get('google-address', 'Backend\ProviderController@findAddressBasedOnPlaceId')->name('address');
Route::get('backend/providers-by-category', 'Auth\BecomeProviderController@getProvidersByCategory')->name('providers.byCategory');

Route::group(['middleware' => ['auth', 'route.access'], 'namespace' => 'Backend', 'as' => 'backend.'], function () {
    
    // Dashboard
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

    //editor file upload
    Route::post('upload', 'DashboardController@upload')->name('upload');

    //addresses
    Route::resource('address', 'AddressController', ['except' => ['show']]);

    // Account
    Route::get('account/profile', 'AccountController@profile')->name('account.profile');
    Route::put('account/profile/update', 'AccountController@updateProfile')->name('account.profile.update');
    Route::put('account/password/update', 'AccountController@updatePassword')->name('account.password.update');

    // Users
    Route::resource('user', 'UserController', ['except' => ['show']]);
    Route::put('user/{id}/password/update', 'UserController@updatePassword')->name('user.password.update');
    Route::delete('delete-users', 'UserController@deleteRows')->name('delete.users')->middleware('can:backend.user.destroy');
    Route::put('user/status/{id}', 'UserController@status')->name('user.status')->middleware('can:backend.user.edit');

    // Role
    Route::resource('role', 'RoleController', ['except' => ['show']]);
    Route::delete('delete-roles', 'RoleController@deleteRows')->name('delete.roles')->middleware('can:backend.role.destroy');

    // Provider
    Route::resource('provider', 'ProviderController', ['except' => ['show']]);
    Route::get('provider/export', 'ProviderController@export')->name('provider.export')->middleware('can:backend.provider.index');
    Route::post('provider/import/csv', 'ProviderController@import')->name('provider.import.csv')->middleware('can:backend.provider.create');

    Route::resource('commission', 'CommissionController', ['except' => ['show']]);
    Route::get('commission/export', 'CommissionController@export')->name('commission.export')->middleware('can:backend.commission_history.index');

    Route::resource('provider-document', 'ProviderDocumentController', ['except' => ['show']]);
    Route::get('provider-document/export', 'ProviderDocumentController@export')->name('provider-document.export')->middleware('can:backend.provider_document.index');
    Route::post('provider-document/import/csv', 'ProviderDocumentController@import')->name('provider-document.import.csv')->middleware('can:backend.provider_document.create');

    Route::put('provider/status/{id}', 'ProviderController@status')->name('provider.status');


    Route::resource('/provider-time-slot', 'ProviderTimeSlotController', ['except' => ['show']]);
    Route::put('provider-time-slot/status/{id}', 'ProviderTimeSlotController@status')->name('provider-time-slot.status');
    Route::delete('delete-providers', 'ProviderController@deleteRows')->name('delete.providers');
    Route::delete('delete-provider-time-slots', 'ProviderTimeSlotController@deleteRows')->name('delete.provider-time-slots');
    Route::delete('delete-providerDocuments', 'ProviderDocumentController@deleteRows')->name('delete.providerDocuments');

    //Service
    Route::resource('service', 'ServiceController', ['except' => ['show']]);
    Route::resource('service-requests', 'ServiceRequestController');
    Route::get('service/export', 'ServiceController@export')->name('service.export')->middleware('can:backend.service.index');
    Route::post('service/import', 'ServiceController@import')->name('service.import')->middleware('can:backend.service.create');

    Route::delete('service-request/{serviceRequest}', 'ServiceRequestController@destroy')->name('serviceRequest.destroy');
    Route::delete('delete-serviceReuest', 'ServiceRequestController@deleteRows')->name('delete.serviceRequest');
    Route::resource('additional-service', 'AdditionalServiceController', ['except' => ['show']]);
    Route::get('additional-service/export', 'AdditionalServiceController@export')->name('additional-service.export')->middleware('can:backend.service.index');
    Route::post('additional-service/import/csv', 'AdditionalServiceController@import')->name('additional-service.import.csv')->middleware('can:backend.service.create');

    Route::put('service/status/{id}', 'ServiceController@status')->name('service.status');
    Route::put('additional-service/status/{id}', 'AdditionalServiceController@status')->name('additional-service.status');
    Route::delete('delete-services', 'ServiceController@deleteRows')->name('delete.services');
    Route::resource('service-package', 'ServicePackageController', ['except' => ['show']]);
    Route::delete('delete-servicePackages', 'ServicePackageController@deleteRows')->name('delete.servicePackages')->middleware('can:backend.service-package.destroy');
    Route::put('service-package/status/{id}', 'ServicePackageController@status')->name('service-package-status')->middleware('can:backend.service-package.edit');
    Route::get('service-package/export', 'ServicePackageController@export')->name('service-package.export')->middleware('can:backend.service-package.index');
    Route::post('service-package/import/csv', 'ServicePackageController@import')->name('service-package.import.csv')->middleware('can:backend.service-package.create');

    Route::get('get-zone-categories', 'ServiceController@getZoneCategories')->name('get-zone-categories');
    Route::get('get-zone-taxes', 'ServiceController@getZoneTaxes')->name('get-zone-taxes');

    // Document
    Route::resource('document', 'DocumentController', ['except' => ['show']]);
    Route::put('document/status/{id}', 'DocumentController@status')->name('document.status');
    Route::delete('delete-documents', 'DocumentController@deleteRows')->name('delete.documents');

    // Categories
    Route::get('category/{category}/edit/{locale}', 'CategoryController@edit')->name('category.edit')->middleware('can:backend.service_category.edit');
    Route::resource('category', 'CategoryController', ['except' => ['show', 'edit']]);

    // Currencies
    Route::resource('currency', 'CurrencyController', ['except' => ['show']]);
    Route::get('/get-symbol', 'CurrencyController@getSymbol')->name('get-symbol');
    Route::put('currency/status/{id}', 'CurrencyController@status')->name('currency.status')->middleware('can:backend.currency.edit');
    Route::delete('delete-currencies', 'CurrencyController@deleteRows')->name('delete.currencies')->middleware('can:backend.currency.destroy');

    // Blogs
    Route::resource('blog', 'BlogController', ['except' => ['show']]);
    Route::put('blog-status/{id}', 'BlogController@updateStatus')->name('blog-status')->middleware('can:backend.blog.edit');
    Route::put('blog-featured/{id}', 'BlogController@updateIsFeatured')->name('isFeatured')->middleware('can:backend.blog.edit');
    Route::get('blog-category/{blog_category}/edit/{locale}', 'BlogCategoryController@edit')->name('blog-category.edit')->middleware('can:backend.blog_category.edit');
    Route::resource('blog-category', 'BlogCategoryController', ['except' => ['show', 'edit']])->parameters([
        'blog-category' => 'blog_category'
    ]);
    Route::delete('delete-blogs', 'BlogController@deleteRows')->name('delete.blogs')->middleware('can:backend.blog.destroy');

    // Pages
    Route::resource('page', 'PageController', ['except' => ['show']]);
    Route::put('page/status/{id}', 'PageController@status')->name('page.status')->middleware('can:backend.blog.edit');
    Route::delete('delete-pages', 'PageController@deleteRows')->name('delete.pages')->middleware('can:backend.page.destroy');

    // Testimonials
    Route::resource('testimonial', 'TestimonialController', ['except' => ['show']]);
    Route::put('testimonial/status/{id}', 'TestimonialController@status')->name('testimonial.status')->middleware('can:backend.testimonial.edit');
    Route::delete('delete-testimonials', 'TestimonialController@deleteRows')->name('delete.testimonials')->middleware('can:backend.testimonial.destroy');

    //Subscribes
    Route::get('subscribers', 'SubscribeController@index')->name('subscribers')->middleware('can:backend.news_letter.index');

    // taxes
    Route::resource('tax', 'TaxController', ['except' => ['show']]);
    Route::put('tax/status/{id}', 'TaxController@status')->name('tax.status')->middleware('can:backend.tax.edit');
    Route::delete('delete-taxes', 'TaxController@deleteRows')->name('delete.taxs')->middleware('can:backend.tax.destroy');

    //tags
    Route::resource('tag', 'TagController', ['except' => ['show']]);
    Route::put('tag/status/{id}', 'TagController@status')->name('tag.status')->middleware('can:backend.tag.edit');
    Route::delete('delete-tags', 'TagController@deleteRows')->name('delete.tags')->middleware('can:backend.tag.destroy');

    //Serviceman
    Route::resource('serviceman', 'ServicemanController', ['except' => ['show']]);
    Route::get('serviceman/changeIsFeatured', 'ServicemanController@updateIsFeatured');
    Route::get('serviceman/changeStatus', 'ServicemanController@updateStatus');
    Route::put('serviceman/changePassword/{id}', 'ServicemanController@changePassword')->name('serviceman.updatePassword');
    Route::put('serviceman/status/{id}', 'ServicemanController@status')->name('serviceman.status')->can('backend.serviceman.edit');
    Route::get('serviceman/export', 'ServicemanController@export')->name('serviceman.export')->middleware('can:backend.serviceman.index');
    Route::post('serviceman/import/csv', 'ServicemanController@import')->name('serviceman.import.csv')->middleware('can:backend.serviceman.create');

    //Customer
    Route::resource('customer', 'CustomerController', ['except' => ['show']]);
    Route::delete('delete-customers', 'CustomerController@deleteRows')->name('delete.customers');
    Route::put('customer/status/{id}', 'CustomerController@status')->name('customer.status');

    //Banner
    Route::resource('banner', 'BannerController', ['except' => ['show']]);
    Route::post('/banner-status', 'BannerController@toggleStatus')->name('banner-status')->middleware('can:backend.banner.edit');
    Route::put('banner/status/{id}', 'BannerController@status')->name('banner.status')->middleware('can:backend.banner.edit');
    Route::delete('delete-banners', 'BannerController@deleteRows')->name('delete.banners')->middleware('can:backend.banner.destroy');


    Route::resource('advertisement', 'AdvertisementController', ['except' => ['show']]);
    Route::post('/advertisement-status', 'AdvertisementController@toggleStatus')->name('advertisement-status')->middleware('can:backend.advertisement.edit');
    Route::put('advertisement/status/{id}', 'AdvertisementController@status')->name('advertisement.status')->middleware('can:backend.advertisement.edit');
    Route::delete('delete-advertisements', 'AdvertisementController@deleteRows')->name('delete.advertisements')->middleware('can:backend.advertisement.destroy');


    //Banner Category
    Route::post('bannerCategory', 'BannerTypeCategoryController@getBannerCategory');

    //Wallet
    Route::get('wallet', 'WalletController@index')->name('wallet.index')->middleware('can:backend.wallet.index');
    Route::post('wallet/creditOrdebit', 'WalletController@creditOrdebit')->name('wallet.creditOrdebit')->middleware('canAny:backend.wallet.credit,backend.wallet.debit');
    Route::get('get-user-transactions/{user_id?}', 'WalletController@walletTransations')->name('get-user-transactions')->middleware('can:backend.wallet.index');
    Route::get('transactions', 'TransactionController@index')->name('transaction.index')->middleware('can:backend.payment_transaction.index')->middleware('can:backend.wallet.index');
    Route::get('transaction/export', 'TransactionController@export')->name('transaction.export')->middleware('can:backend.payment_transaction.index');

    //Push Notification
    Route::get('push-notifications', 'NotificationController@create')->name('push-notifications')->middleware('can:backend.push_notification.index');
    Route::get('notifications', 'NotificationController@index')->name('notifications')->middleware('can:backend.push_notification.index');
    Route::delete('notifications/destroy/{id}', 'NotificationController@destroy')->name('push_notification.destroy')->middleware('can:backend.push_notification.destroy');
    Route::post('send-push-notification', 'NotificationController@sendNotification')->name('send-notification')->middleware('can:backend.push_notification.create');
    Route::delete('delete-push-notifications', 'NotificationController@deleteRows')->name('delete.push-notifications')->middleware('can:backend.push_notification.destroy');

    //Notifications
    Route::get('list-notification', 'NotificationController@listNotification')->name('list-notification');
    Route::post('/notifications/mark-as-read', 'NotificationController@markAsRead')->name('notifications.markAsRead');
    Route::post('notifications/test', 'NotificationController@test')->name('mail.test');

    //User Reviews
    Route::resource('review', 'ReviewController', ['except' => ['show']]);
    Route::get('servicemen-review', 'ReviewController@servicemenReview')->name('servicemen-review');
    Route::delete('delete-user-reviews', 'ReviewController@deleteRows')->name('delete.user.reviews')->middleware('can:backend.review.destroy');

    //Provider Wallet
    Route::resource('provider-wallet', 'ProviderWalletController', ['except' => ['show']])->middleware('can:backend.provider_wallet.index');
    Route::post('provider-wallet/creditOrdebit', 'ProviderWalletController@creditOrdebit')->name('provider-wallet.creditOrdebit')->middleware('canAny:backend.provider_wallet.credit,backend.provider_wallet.debit');
    Route::get('get-provider-transactions/{provider_id?}', 'ProviderWalletController@providerWalletTransations')->name('get-provider-transactions')->middleware('can:backend.provider_wallet.index');

    //Serviceman Wallet
    Route::resource('serviceman-wallet', 'ServicemanWalletController', ['except' => ['show']])->middleware('can:backend.serviceman_wallet.index');
    Route::post('serviceman-wallet/creditOrdebit', 'ServicemanWalletController@creditOrDebit')->name('serviceman-wallet.creditOrdebit')->middleware('canAny:backend.serviceman_wallet.credit,backend.serviceman_wallet.debit');
    Route::get('get-serviceman-transactions/{serviceman_id?}', 'ServicemanWalletController@servicemanWalletTransations')->name('get-serviceman-transactions')->middleware('can:backend.serviceman_wallet.index');

    //Withdraw Request
    Route::resource('withdraw-request', 'WithdrawRequestController', ['except' => ['show']]);

    //Withdraw Request
    Route::resource('serviceman-withdraw-request', 'ServicemanWithdrawRequestController', ['except' => ['show']]);

    //media
    Route::delete('delete-media/{id?}', 'MediaController@destroy')->name('media.delete');

    //Settings
    Route::get('settings', 'SettingsController@index')->name('settings.index');
    Route::get('payment-methods', 'PaymentMethodController@index')->name('paymentmethods.index')->middleware('can:backend.payment_method.index');
    Route::post('payment-methods/{payment}', 'PaymentMethodController@update')->name('paymentmethods.update')->middleware('can:backend.payment_method.edit');
    Route::post('payment-methods/status/{payment}', 'PaymentMethodController@status')->name('paymentmethods.status')->middleware('can:backend.payment_method.edit');
    Route::put('update/settings/{setting}', 'SettingsController@update')->name('update.settings');

    //Home Page
    Route::get('home-page', 'HomePageController@index')->name('home_page.index')->middleware('can:backend.home_page.index');
    Route::put('update/home-page/{homePage}', 'HomePageController@update')->name('update.home_page')->middleware('can:backend.home_page.edit');

    //Theme Options
    Route::get('theme-options', 'ThemeOptionController@index')->name('theme_options.index')->middleware('can:backend.theme_option.index');
    Route::put('update/theme-options/{themeOption}', 'ThemeOptionController@update')->name('update.theme_options')->middleware('can:backend.theme_option.edit');

    //SMS Gateways
    Route::get('sms-gateways', 'SMSGatewayController@index')->name('smsgateways.index')->middleware('can:backend.sms_gateway.index');
    Route::post('sms-gateways/{sms}', 'SMSGatewayController@update')->name('smsgateways.update')->middleware('can:backend.sms_gateway.edit');
    Route::post('sms-gateways/status/{sms}', 'SMSGatewayController@status')->name('smsgateways.status')->middleware('can:backend.sms_gateway.edit');

    //Booking
    Route::get('bookings', 'BookingController@index')->name('booking.index')->middleware('can:backend.booking.index');
    Route::get('booking/create', 'PosController@create')->name('booking.create')->middleware('can:backend.booking.create');
    Route::get('booking/filter-services', 'PosController@filterServices')->name('booking.filter-services');
    Route::get('booking/addresses', 'PosController@getAddresses')->name('booking.get-addresses');
    Route::post('service/booking', 'PosController@serviceBooking')->name('service.booking');
    Route::post('service/checkout', 'PosController@serviceCheckout')->name('service.checkout');
    Route::delete('cart/clear', 'PosController@clearCart')->name('cart.clear');
    Route::delete('cart/remove/{id?}', 'PosController@destroy')->name('cart.remove');
    Route::get('booking/export', 'BookingController@export')->name('booking.export')->middleware('can:backend.booking.index');
    Route::post('add/address', 'PosController@addAddress')->name('address.add');

    Route::get('booking/show/{id}', 'BookingController@show')->name('booking.show')->middleware('can:backend.booking.index');
    Route::get('booking/showChild/{id}', 'BookingController@showChild')->name('booking.showChild')->middleware('can:backend.booking.index');
    Route::get('booking/assign', 'BookingController@assign')->name('booking.assign')->middleware('can:backend.booking.edit');
    Route::post('booking/assignServicemen', 'BookingController@assignServicemen')->name('booking.assignServicemen')->middleware('can:backend.booking.edit');
    Route::get('booking/get-servicemen', 'BookingController@getServicemen')->name('booking.getServicemen')->middleware('can:backend.booking.index');
    Route::get('get-provider-services', 'ServicePackageController@getProviderServices')->name('get-provider-services')->middleware('can:backend.booking.index');
    Route::post('booking-status/update/{booking_id}', 'BookingController@updateBookingStatus')->name('bookingStatus.update')->middleware('can:backend.booking.edit');
    Route::post('booking/updateDateTime', 'BookingController@updateDateTime')->name('booking.updateDateTime')->middleware('can:backend.booking.edit');
    Route::post('/update-payment-status', 'BookingController@updatePaymentStatus')->name('booking.updatePaymentStatus')->middleware('can:backend.booking.edit');
    Route::delete('booking/{booking}/extra-charges/{charge}', 'BookingController@deleteExtraCharge')->name('booking.extra-charge.delete');


    //Languages
    Route::resource('systemLang', 'LanguageController', ['except' => ['show']]);
    Route::delete('delete-language', 'LanguageController@deleteRows')->name('delete.systemLang')->middleware('can:backend.language.destroy');
    Route::put('systemLang/status/{id}', 'LanguageController@status')->name('systemLang.status')->middleware('can:backend.language.edit');
    Route::put('systemLang/rtl/{id}', 'LanguageController@rtl')->name('systemLang.rtl')->middleware('can:backend.language.edit');
    Route::get('systemLang/translate/{locale}/{file?}', 'LanguageController@translate')->name('systemLang.translate')->middleware('can:backend.language.edit');
    Route::post('systemLang/translate/{locale}/{file}', 'LanguageController@translate_update')->name('systemLang.translate.update')->middleware('can:backend.language.edit');

    //Zones
    Route::get('zone/{zone}/edit/{locale}', 'ZoneController@edit')->name('zone.edit')->middleware('can:backend.zone.edit');
    Route::resource('zone', 'ZoneController', ['except' => ['show', 'edit']]);
    Route::delete('delete-zones', 'ZoneController@deleteRows')->name('delete.zones')->middleware('can:backend.zone.destroy');
    Route::put('zone/status/{id}', 'ZoneController@status')->name('zone.status')->middleware('can:backend.zone.edit');
    Route::get('zone/export', 'ZoneController@export')->name('zone.export')->middleware('can:backend.zone.index');
    Route::post('zone/import/csv', 'ZoneController@import')->name('zone.import.csv')->middleware('can:backend.zone.create');

    //Email Templates
    Route::get('email-template', 'EmailTemplateController@index')->name('email-template.index');
    Route::get('email-template/edit/{slug}', 'EmailTemplateController@edit')->name('email-template.edit');
    Route::post('email-template/edit/{slug}','EmailTemplateController@update')->name('email-template.update');

    //Sms Templates
    Route::get('sms-template', 'SmsTemplateController@index')->name('sms-template.index');
    Route::get('sms-template/edit/{slug}', 'SmsTemplateController@edit')->name('sms-template.edit');
    Route::post('sms-template/edit/{slug}','SmsTemplateController@update')->name('sms-template.update');

    //Push Notification Templates
    Route::get('push-notification-template', 'PushNotificationTemplateController@index')->name('push-notification-template.index');
    Route::get('push-notification-template/edit/{slug}', 'PushNotificationTemplateController@edit')->name('push-notification-template.edit');
    Route::post('push-notification-template/edit/{slug}','PushNotificationTemplateController@update')->name('push-notification-template.update');

    //Custom Sms Gateway
    Route::resource('custom-sms-gateway', 'CustomSmsGatewayController', ['except' => ['show']]);
    Route::post('custom-sms-gateway/test','CustomSmsGatewayController@test')->name(('custom-sms-gateway.test'));

    Route::resource('backup', 'BackupController');
    Route::get('backup/download-db/{id}', 'BackupController@downloadDbBackup')->name('backup.downloadDbBackup')->middleware('can:backend.backup.create');
    Route::get('backup/download-files/{id}', 'BackupController@downloadFilesBackup')->name('backup.downloadFilesBackup')->middleware('can:backend.backup.create');
    Route::get('backup/download-uploads/{id}', 'BackupController@downoadUploadsBackup')->name('backup.downoadUploadsBackup')->middleware('can:backend.backup.create');
    Route::get('backup/restore-backup/{id}', 'BackupController@restoreBackup')->name('backup.restoreBackup')->middleware('can:backend.backup.create');
    Route::delete('backup/delete-backup/{id}', 'BackupController@deleteBackup')->name('backup.deleteBackup')->middleware('can:backend.backup.destroy');

    //Provider Dashboard
    Route::get('provider/{id}/general', 'UserDashboardController@providerDetails')->name('provider.general-info')->middleware('can:backend.provider_dashboard.index');
    Route::get('provider/{id}/bookings','UserDashboardController@getBookings')->name('provider.get-bookings')->middleware('can:backend.provider_dashboard.index');
    Route::get('provider/{id}/servicemen','UserDashboardController@getServicemen')->name('provider.get-servicemen')->middleware('can:backend.provider_dashboard.index');
    Route::get('provider/{id}/reviews','UserDashboardController@getUserReviews')->name('provider.get-reviews')->middleware('can:backend.provider_dashboard.index');
    Route::get('provider/{id}/documents','UserDashboardController@getUserDocuments')->name('provider.get-documents')->middleware('can:backend.provider_dashboard.index');
    Route::get('provider/{id}/withdraw-requests','UserDashboardController@getProviderWithdrawRequests')->name('provider.get-withdraw-requests')->middleware('can:backend.provider_dashboard.index');

    //Serviceman Dashboard
    Route::get('servicemen/{id}/general', 'UserDashboardController@servicemanDetails')->name('servicemen.general-info')->middleware('can:backend.servicemen_dashboard.index');
    Route::get('servicemen/{id}/bookings','UserDashboardController@getBookings')->name('servicemen.get-bookings')->middleware('can:backend.servicemen_dashboard.index');
    Route::get('servicemen/{id}/reviews','UserDashboardController@getUserReviews')->name('servicemen.get-reviews')->middleware('can:backend.servicemen_dashboard.index');
    Route::get('servicemen/{id}/withdraw-requests','UserDashboardController@getServicemanWithdrawRequests')->name('servicemen.get-withdraw-requests')->middleware('can:backend.servicemen_dashboard.index');

    //Consumer Dashboard
    Route::get('consumer/{id}/general', 'UserDashboardController@consumerDetails')->name('consumer.general-info')->middleware('can:backend.consumer_dashboard.index');
    Route::get('consumer/{id}/bookings','UserDashboardController@getBookings')->name('consumer.get-bookings')->middleware('can:backend.consumer_dashboard.index');
    Route::get('consumer/{id}/reviews','UserDashboardController@getUserReviews')->name('consumer.get-reviews')->middleware('can:backend.servicemen_dashboard.index');

    //unverified-users
    Route::get('unverified-users', 'UnverifiedUserController@index')->name('unverfied-users.index')->middleware('can:backend.unverified_user.index');
    Route::put('unverified-users/{id}', 'UnverifiedUserController@verify')->name('unverfied-users.action')->middleware('can:backend.unverified_user.edit');

    // Serviceman Locations
    Route::get('serviceman-location', 'ServicemanController@servicemanLocation')->name('serviceman-location.index')->middleware('can:backend.serviceman_location.index');
    Route::get('serviceman-coordinates/{id}', 'ServicemanController@servicemanCordinates')->name('serviceman-cordinates.index')->middleware('can:backend.serviceman_location.index');

    //Transaction Report
    Route::get('transaction-report', 'TransactionReportController@index')->name('transaction-report.index')->middleware('can:backend.report.index');
    Route::post('transaction-report/filter', 'TransactionReportController@filter')->name('transaction-report.filter')->middleware('can:backend.report.index');
    Route::post('transaction-report/export', 'TransactionReportController@export')->name('transaction-report.export');

    //Booking Report
    Route::get('booking-report', 'BookingReportController@index')->name('booking-report.index')->middleware('can:backend.report.index');
    Route::post('booking-report/filter', 'BookingReportController@filter')->name('booking-report.filter')->middleware('can:backend.report.index');
    Route::post('booking-report/export', 'BookingReportController@export')->name('booking-report.export')->middleware('can:backend.report.create');

    //Provider Report
    Route::get('provider-report', 'ProviderReportController@index')->name('provider-report.index')->middleware('can:backend.report.index');
    Route::post('provider-report/filter', 'ProviderReportController@filter')->name('provider-report.filter')->middleware('can:backend.report.index');
    Route::post('provider-report/export', 'ProviderReportController@export')->name('provider-report.export')->middleware('can:backend.report.create');

    Route::get('customizations','CustomizationController@index')->name('customization.index')->middleware('can:backend.customization.index');
    Route::post('customizations/store','CustomizationController@store')->name('customization.store')->middleware('can:backend.customization.index');

    Route::post('user/import/csv', 'UserController@import')->name('user.import.csv')->middleware('can:backend.user.create');
    Route::get('user/export', 'UserController@export')->name('user.export')->middleware('can:backend.user.index');

    Route::get('activity-logs', 'ActivityLogController@index')->name('activity-logs.index')->middleware('can:backend.system_tool.index');
    Route::delete('activity-logs/destroy/{id}', 'ActivityLogController@destroy')->name('activity-log.destroy')->middleware('can:backend.system_tool.destroy');
    Route::delete('activity-logs/delete-all', 'ActivityLogController@deleteAll')->name('activity-log.deleteAll')->middleware('can:backend.system_tool.destroy');

    Route::resource('cleanup-db', 'DatabaseCleanupController');

    Route::get('robots','RobotsController@index')->name('robot.index')->middleware('can:backend.theme_option.index');
    Route::post('robots/update','RobotsController@update')->name('robot.update')->middleware('can:backend.theme_option.index');

    Route::get('import-export', 'ImportExportController@index')->name('import.index');
    Route::get('import-export/{slug}', 'ImportExportController@importExport')->name('import-export.index');

    Route::get('category/export', 'CategoryController@export')->name('category.export')->middleware('can:backend.service_category.index');
    Route::post('category/import', 'CategoryController@import')->name('category.import')->middleware('can:backend.service_category.create');

    Route::get('/clear-cache', function () {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('optimize:clear');
        Artisan::call('clear-compiled');
        Artisan::call('storage:link');

        return back()->with('message', 'Cache was successfully cleared.');
    })->name('clear-cache');
});
