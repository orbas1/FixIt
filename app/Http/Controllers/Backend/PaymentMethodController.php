<?php

namespace App\Http\Controllers\Backend;

use Exception;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Nwidart\Modules\Facades\Module;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;


class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = Helpers::getPaymentMethodConfigs();
        return view('backend.payment-methods.index', ['paymentMethods' => $paymentMethods]);
    }

    public function update(Request $request, $payment)
    {
        try {

            $configs = null;
            $title = $request->title ?? null;
            $paymentGatewayCharge = (double) $request->processing_fee ?? 0;
            $Subscription = (int) $request->subscription ?? 0;
            $paymentMethods = Helpers::getPaymentMethodConfigs();
            $paymentFile = module_path($payment, 'config/payment.php');
            if (file_exists($paymentFile)) {
                $paymentConfig = include $paymentFile;

                $paymentConfig['title'] = $title;
                $paymentConfig['subscription'] = $Subscription;
                $paymentConfig['processing_fee'] = $paymentGatewayCharge;
                $content = "<?php\n\nreturn ".var_export($paymentConfig, true).";\n";
                File::put($paymentFile, $content);
                Artisan::call('cache:clear');
            }

            foreach ($paymentMethods as $paymentMethod) {
                if ($paymentMethod['slug'] == $payment) {
                    $configs = $paymentMethod;
                }
            }

            if ($configs) {
                foreach ($configs['fields'] as $fieldKey => $fieldAttributes) {
                    $envKey = strtoupper($fieldKey);
                    $newValue = Helpers::decryptKey($request->$fieldKey);
                    DotenvEditor::setKey($envKey, $newValue);
                    DotenvEditor::save();
                }

                return to_route('backend.paymentmethods.index');
            }

            return redirect()->back()->with('error', __('static.payment_methods.config_file_not_found'));

        } catch (Exception $e) {

            return redirect()->back()->with('error', __('static.payment_methods.something_went_wrong'));
        }
    }

    public function status(Request $request, $payment)
    {
        try {

            $paymentMethods = Helpers::getPaymentMethodConfigs();
            foreach ($paymentMethods as $paymentMethod) {
                if ($paymentMethod['slug'] == $payment) {
                    if (Module::has($paymentMethod['name'])) {

                        if ((int) $request->status) {

                            Module::enable($paymentMethod['name']);
                        } else {

                            Module::disable($paymentMethod['name']);
                        }

                        return response()->json([
                            'message' => __('static.payment_methods.updated_msg', ['name' => $paymentMethod['name']]),
                            'success' => true,
                        ], 200);
                    }
                }
            }

            return response()->json(['error' => __('static.payment_methods.invalid_msg')], 400);

        } catch (Exception $e) {

            return response()->json(['error' => __('static.something_went_wrong')], 500);
        }
    }
}
