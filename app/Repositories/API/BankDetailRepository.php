<?php

namespace App\Repositories\API;

use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\BankDetail;
use Exception;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;

class BankDetailRepository extends BaseRepository
{
    public function model()
    {
        return BankDetail::class;
    }


    public function index($request)
    {
        try {
            $bankDetail = $this->model->where('user_id', Helpers::getCurrentUserId())->first();
            if(!$bankDetail){
                return response()->json(['success' => false, 'message' => 'Details Not Found!'], 400);
            }
            return response()->json(['success' => true, 'data' => $bankDetail]);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $bankDetail = $this->model->create([
                'user_id' => Helpers::getCurrentUserId(),
                'bank_name' => $request->bank_name,
                'holder_name' => $request->holder_name,
                'account_number' => $request->account_number,
                'branch_name' => $request->branch_name,
                'ifsc_code' => $request->ifsc_code,
                'swift_code' => $request->swift_code,
            ]);

            if (isset($request->bank_passbook) && $request->hasFile('bank_passbook')) {
                $bankDetail->addMediaFromRequest('bank_passbook')->toMediaCollection('bank_passbook');
            }

            DB::commit();

            return response()->json([
                'message' => __('static.bank_details.detail_submitted'),
                'bankDetails' => $bankDetail,
            ]);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $user_id)
    {
        DB::beginTransaction();
        try {
            $bankDetail = $this->model->where('user_id', $user_id)->first();
            if($bankDetail){
                $bankDetail = $bankDetail->update([
                    'user_id' => Helpers::getCurrentUserId(),
                    'bank_name' => $request->bank_name,
                    'holder_name' => $request->holder_name,
                    'account_number' => $request->account_number,
                    'branch_name' => $request->branch_name,
                    'ifsc_code' => $request->ifsc_code,
                    'swift_code' => $request->swift_code,
                ]);
            } else {
                $bankDetail = $this->model->create([
                    'user_id' => $user_id,
                    'bank_name' => $request->bank_name,
                    'holder_name' => $request->holder_name,
                    'account_number' => $request->account_number,
                    'branch_name' => $request->branch_name,
                    'ifsc_code' => $request->ifsc_code,
                    'swift_code' => $request->swift_code,
                ]);
            }
            DB::commit();
            return response()->json([
                'message' => __('static.bank_details.detail_submitted'),
                'bankDetails' => $bankDetail,
            ]);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
