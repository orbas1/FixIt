<?php

namespace App\Repositories\Frontend;

use Exception;
use App\Models\Subscribe;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;

class SubscribeRepositry extends BaseRepository
{

    public function model()
    {
        return Subscribe::class;
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $subscription = $this->model->where('email', $request->newsletter)->first();
            if ($subscription) {
                throw new Exception('This email is already subscribed.', 400);
            }
            else{

                $this->model->create([
                    'email' => $request->newsletter
                ]);

                DB::commit();
                return redirect()->back()->with('message', value: 'Successfully subscribed.');
            }

        } catch (Exception $e) {
            DB::rollBack();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

}
