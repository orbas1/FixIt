<?php

namespace App\Repositories\Backend;
use Exception;
use App\SMS\SMS;
use App\Enums\TimeZone;
use App\Helpers\Helpers;
use App\Models\CustomSmsGateway;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionHandler;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Prettus\Repository\Eloquent\BaseRepository;

class CustomSmsGatewayRepository extends BaseRepository
{
    public function model()
    {
        return CustomSmsGateway::class;
    }

    public function index()
    {
        return view('backend.custom-sms-gateway.index', [
            'settings' => Helpers::getSmsGatewaySettings(),
            'id' => $this->model->pluck('id')->first()
        ]);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            // Check if 'configs' is in the request
            if (in_array('configs', $request['is_config'])) {
                $customKeys = $this->extractKeyValuePairs($request, 'key', 'value');
                $request['custom_keys'] = $customKeys;
            }

            if (isset($request['body'])) {

                $decodedBody = json_decode($request['body'], true);
        
                if (empty($decodedBody) || !is_array($decodedBody)) {
                    throw new Exception("Body is not in json Format.", 400);
                }

            } elseif (isset($request['body_key']) && isset($request['body_value'])) {

                $body = $this->extractKeyValuePairs($request, 'body_key', 'body_value');
                $request['body'] = $body;

            }
            
            // Extract body parameters
            // if (isset($request['body_key']) && isset($request['body_value'])) {
            //     $body = $this->extractKeyValuePairs($request, 'body_key', 'body_value');
            //     $request['body'] = $body;
            // }                        

            // Extract parameters
            if (isset($request['param_key']) && isset($request['param_value'])) {
                $param = $this->extractKeyValuePairs($request, 'param_key', 'param_value');
                $request['params'] = $param;
            }

            // Extract headers
            if (isset($request['header_key']) && isset($request['header_value'])) {
                $header = $this->extractKeyValuePairs($request, 'header_key', 'header_value');
                $request['headers'] = $header;
            }

            $settings = $this->model->findOrFail($id);
            $settings->update($request);
            $this->env($request);

            DB::commit();
            return to_route('backend.custom-sms-gateway.index')->with('success', __('static.custom_sms_gateways.update_successfully'));

        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function extractKeyValuePairs($request, $keyField, $valueField)
    {
        $result = [];
        if (isset($request[$keyField]) && isset($request[$valueField])) {
            foreach ($request[$keyField] as $index => $key) {
                $result[$key] = $request[$valueField][$index];
            }
        }
        return $result;
    }

    public function env($value)
    {
        if (isset($value['sid'])) {
            DotenvEditor::setKeys([
                'SMS_SID' => $value['sid'],
            ]);

            DotenvEditor::save();
        }

        if (isset($value['auth_token'])) {
            DotenvEditor::setKeys([
                'SMS_AUTH_TOKEN' => $value['auth_token'],
            ]);

            DotenvEditor::save();
        }

        if (isset($value['custom_keys'])) {
            foreach ($value['custom_keys'] as $key => $value) {
                DotenvEditor::setKeys([
                    $key => $value,
                ]);
    
                DotenvEditor::save();
            }
        }
    }

    public function test($request){
        $sms = new SMS();
        $data = [
            'to' => $request->phoneNumber,
            'message' => $request->testMessage,
        ];
        $sms->sendSMS($data);

        
        return redirect()->back()->with('success', 'Test SMS sent successfully!');
    }
}


