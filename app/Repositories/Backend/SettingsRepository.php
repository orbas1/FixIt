<?php

namespace App\Repositories\Backend;

use App\Enums\ModuleEnum;
use App\Helpers\Helpers;
use App\Mail\TestMail;
use App\Models\Currency;
use App\Models\Setting;
use App\Models\SystemLang;
use App\Models\Tax;
use App\Models\TimeZone;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Nwidart\Modules\Facades\Module;
use Prettus\Repository\Eloquent\BaseRepository;

class SettingsRepository extends BaseRepository
{
    protected $timeZone;

    protected $tax;

    protected $currency;

    protected $systemlang;

    protected $paymentGateWays;

    public function model()
    {
        $this->currency = new Currency();
        $this->timeZone = new TimeZone();
        $this->systemlang = new SystemLang();
        $this->tax = new Tax();

        return Setting::class;
    }

    public function index()
    {
        $module = $this->isModuleEnable();
        $settings = $this->model->pluck('values')->first();
        $settingsId = $this->model->pluck('id')->first();
        $timeZones = $this->timeZone->pluck('name', 'code');
        $currencies = $this->currency->pluck('code', 'id');
        $systemlangs = $this->systemlang->where('status', true)->pluck('name', 'id');
        $taxes = $this->tax->pluck('name', 'id');

        return view('backend.settings.index', [
            'settings' => $settings,
            'timeZones' => $timeZones,
            'currencies' => $currencies,
            'settingsId' => $settingsId,
            'systemlangs' => $systemlangs,
            'taxes' => $taxes,
        ]);
    }

    public function isModuleEnable()
    {
        $module = Module::find(ModuleEnum::PAYMENT_GATEWAYS);
        if (! is_null($module) && $module?->isEnabled()) {
            return true;
        } else {
            return false;
        }
    }

    public function test($request)
    {
        try {

            Config::set('mail.default', $request->email['mail_mailer'] ?? 'smtp');

            if ($request->email['mail_mailer'] == 'smtp' || $request->email['mail_mailer'] == 'sendmail') {
                Config::set('mail.mailers.smtp.host', $request->email['mail_host'] ?? '');
                Config::set('mail.mailers.smtp.port', $request->email['mail_port'] ?? 465);
                Config::set('mail.mailers.smtp.encryption', $request->email['mail_encryption'] ?? 'ssl');
                Config::set('mail.mailers.smtp.username', $request->email['mail_username'] ?? '');
                Config::set('mail.mailers.smtp.password', Helpers::decryptKey($request->email['mail_password']) ?? '');
                Config::set('mail.from.name', $request->email['mail_from_name'] ?? env('APP_NAME'));
                Config::set('mail.from.address', $request->email['mail_from_address'] ?? '');
            }
            Mail::to($request->mail)->send(new TestMail());

            return json_encode(['success' => true,   'message' => 'Mail Send Successfully']);

        } catch (Exception $e) {

            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function update($request, $id)
    {

        if ($request->test_mail) {
            return $this->test($request);
        }

        DB::beginTransaction();
        try {
            $settings = $this->model->findOrFail($id);
          
            $requestData = $request->except(['_token', '_method']);
            
            if (array_key_exists('mail_password', $requestData['email'])) {
                $requestData['email']['mail_password'] = Helpers::decryptKey($requestData['email']['mail_password']);
            }

            if (array_key_exists('client_id', $requestData['social_login'])) {
                $requestData['social_login']['client_id'] = Helpers::decryptKey($requestData['social_login']['client_id']);
            }

            if (array_key_exists('client_secret', $requestData['social_login'])) {
                $requestData['social_login']['client_secret'] = Helpers::decryptKey($requestData['social_login']['client_secret']);
            }

            if (array_key_exists('secret', $requestData['google_reCaptcha'])) {
                $requestData['google_reCaptcha']['secret'] = Helpers::decryptKey($requestData['google_reCaptcha']['secret']);
            }

            if (array_key_exists('site_key', $requestData['google_reCaptcha'])) {
                $requestData['google_reCaptcha']['site_key'] = Helpers::decryptKey($requestData['google_reCaptcha']['site_key']);
            }

            if (array_key_exists('google_map_api_key', $requestData['firebase'])) {
                $requestData['firebase']['google_map_api_key'] = Helpers::decryptKey($requestData['firebase']['google_map_api_key']);
            }

            if (array_key_exists('account_id', $requestData['zoom'])) {
                $requestData['zoom']['account_id'] = Helpers::decryptKey($requestData['zoom']['account_id']);
            }

            if (array_key_exists('client_key', $requestData['zoom'])) {
                $requestData['zoom']['client_key'] = Helpers::decryptKey($requestData['zoom']['client_key']);
            }

            if (array_key_exists('client_secret', $requestData['zoom'])) {
                $requestData['zoom']['client_secret'] = Helpers::decryptKey($requestData['zoom']['client_secret']);
            }

            if ($request->hasFile('general.light_logo')) {
                $lightLogo = $settings->addMediaFromRequest('general.light_logo')->toMediaCollection('light_logo');
                $lightLogoURL = '/storage/' . $lightLogo->getPathRelativeToRoot();
                $requestData['general']['light_logo'] = $lightLogoURL;
            } else {
                $requestData['general']['light_logo'] = $settings->values['general']['light_logo'] ?? null;
            }

            if ($request->hasFile('general.splash_screen_logo')) {
                $splashScreenLogo = $settings->addMediaFromRequest('general.splash_screen_logo')->toMediaCollection('splash_screen_logo');
                $splashScreenLogoURL = '/storage/' . $splashScreenLogo->getPathRelativeToRoot();
                $requestData['general']['splash_screen_logo'] = $splashScreenLogoURL;
            } else {
                $requestData['general']['splash_screen_logo'] = $settings->values['general']['splash_screen_logo'] ?? null;
            }

            if ($request->has('onboarding')) {
                foreach ($request->onboarding as $key => $onboardingData) {

                    if (isset($onboardingData['image'])) {
                        $image = $settings->addMedia($onboardingData['image'])->toMediaCollection('onboarding_images');
                        $requestData['onboarding'][$key]['image'] = '/storage/' . $image->getPathRelativeToRoot();
                    } else {
                        $requestData['onboarding'][$key]['image'] = $settings->values['onboarding'][$key]['image'] ?? null;
                    }
                }
            }

            if ($request->hasFile('maintenance.image')) {
                $image = $settings->addMediaFromRequest('maintenance.image')->toMediaCollection('image');
                $imageURL = '/storage/' . $image->getPathRelativeToRoot();

                $requestData['maintenance']['image'] = $imageURL;
            } else {
                $requestData['maintenance']['image'] = $settings->values['maintenance']['image'] ?? null;
            }

            if ($request->hasFile('general.dark_logo')) {
                $darkLogo = $settings->addMediaFromRequest('general.dark_logo')->toMediaCollection('dark_logo');
                $darkLogoURL = '/storage/' . $darkLogo->getPathRelativeToRoot();
                $requestData['general']['dark_logo'] = $darkLogoURL;
            } else {
                $requestData['general']['dark_logo'] = $settings->values['general']['dark_logo'] ?? null;
            }

            if ($request->hasFile('general.favicon')) {
                $favicon = $settings->addMediaFromRequest('general.favicon')->toMediaCollection('favicon');
                $faviconURL = '/storage/' . $favicon->getPathRelativeToRoot();
                $requestData['general']['favicon'] = $faviconURL;
            } else {
                $requestData['general']['favicon'] = $settings->values['general']['favicon'] ?? null;
            }

            if ($request->hasFile('firebase.service_json')) {
                $file = $request->firebase['service_json'];
                $fileContents = file_get_contents($file->getPathname());
                $json = json_decode($fileContents, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->withErrors(['firebase.firebase_json' => 'The file must be a valid JSON.']);
                }

                $existingFilePath = public_path('admin/assets/firebase.json');
                if (file_exists($existingFilePath)) {
                    unlink($existingFilePath);
                }

                $file->move(public_path('admin/assets'), 'firebase.json');
                $requestData['firebase']['service_json'] = $json;

            } else {
                $filePath = public_path('admin/assets/firebase.json');
                $fileContents = file_exists($filePath) ? file_get_contents($filePath) : null;
                $requestData['firebase']['service_json'] = json_decode($fileContents, true);
            }

            $settings->update([
                'values' => $requestData,
            ]);

            $language = $this->getLanguageById($requestData['general']['default_language_id']);
            $this->setAppLocale($language);
            $this->env($requestData);
            DB::commit();

            return redirect()->route('backend.settings.index')->with('message', __('static.settings.update_successfully'));

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

    public function env($value)
    {
        try {

            $keys = [];

            if (isset($value['general'])) {
                $keys['APP_NAME']  = $value['general']['site_name'];
            }

            if (isset($value['activation'])) {
                $keys['APP_DEMO']  = $value['activation']['default_credentials'];
            }
            if (isset($value['maintenance'])) {
                $keys['MAINTENANCE_MODE']  = $value['maintenance']['maintenance_mode'];
            }

            if (isset($value['email'])) {
                $keys = array_merge($keys,[
                    'MAIL_MAILER' => $value['email']['mail_mailer'],
                    'MAIL_HOST' => $value['email']['mail_host'],
                    'MAIL_PORT' => $value['email']['mail_port'],
                    'MAIL_USERNAME' => $value['email']['mail_username'],
                    'MAIL_PASSWORD' => $value['email']['mail_password'],
                    'MAIL_ENCRYPTION' => $value['email']['mail_encryption'],
                    'MAIL_FROM_ADDRESS' => $value['email']['mail_from_address'],
                    'MAIL_FROM_NAME' => $value['email']['mail_from_name'],
                ]);
            }

            if (isset($value['google_reCaptcha'])) {
                $keys = array_merge($keys, [
                    'GOOGLE_RECAPTCHA_SECRET' => $value['google_reCaptcha']['secret'],
                    'GOOGLE_RECAPTCHA_KEY' => $value['google_reCaptcha']['site_key'],
                ]);
            }

            if (isset($value['firebase'])) {
                $keys['GOOGLE_MAP_API_KEY'] = $value['firebase']['google_map_api_key'];
            }

            if (isset($value['zoom'])) {
                $keys = array_merge($keys, [
                    'ZOOM_ACCOUNT_ID'   => $value['zoom']['account_id'] ?? '',
                    'ZOOM_CLIENT_KEY'   => $value['zoom']['client_key'] ?? '',
                    'ZOOM_CLIENT_SECRET'=> $value['zoom']['client_secret'] ?? '',
                ]);
            }
            
            if (!empty($keys)) {
                DotenvEditor::setKeys($keys);
                DotenvEditor::save();
            }

        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
