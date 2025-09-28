<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\UpdateAddressRequest;
use App\Repositories\Frontend\AccountRepository;
use App\Http\Requests\Frontend\UpdatePasswordRequest;
use App\DataTables\Frontend\ConsumerTransactionsDataTable;
use Illuminate\Support\Facades\Session;

class AccountController extends Controller
{
    protected $repository;


    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function profile()
    {
        return view('frontend.account.profile');
    }

    public function updateProfile(Request $request)
    {
        return $this->repository->updateProfile($request);
    }

    public function notification()
    {
        return view('frontend.account.notification');
    }

    public function markAsRead(Request $request)
    {
        return $this->repository->markAsRead($request);
    }

    public function webMarkAsRead(Request $request)
    {
        return $this->repository->webMarkAsRead($request);
    }

    public function wallet(ConsumerTransactionsDataTable $dataTable)
    {
        return $this->repository->wallet($dataTable);
    }

    public function walletTopUp(Request $request)
    {
        return $this->repository->walletTopUp($request);
    }

    public function getCustomJobs(Request $request)
    {
        return $this->repository->getCustomJobs($request);
    }

    public function address()
    {
        return view('frontend.account.address');
    }

    public function password()
    {
        return view('frontend.account.password');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        return $this->repository->updatePassword($request);
    }

    public function review()
    {
        $reviews = Review::where('consumer_id', auth()->user()->id)->get();
        return view('frontend.account.review',['reviews'=>$reviews]);
    }

    public function logout()
    {
        Session::forget(['zoneIds', 'location']);
        $keysToKeep = ['zoneIds', 'location'];
        foreach (Session::all() as $key => $value) {
            if (!in_array($key, $keysToKeep)) {
                Session::forget($key);
            }
        }

        return redirect()?->back();
    }
}