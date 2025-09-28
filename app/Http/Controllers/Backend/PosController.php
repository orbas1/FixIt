<?php

namespace App\Http\Controllers\Backend;

use App\Enums\CategoryType;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use App\Repositories\Backend\PosRepository;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public $repository;

    public function __construct(PosRepository $repository)
    {
        // $this->authorizeResource(Service::class, 'service');
        $this->repository = $repository;
    }

    public function create(Request $request)
    {

        $categories = Category::where('parent_id' , null)->where('status' , true)->where('deleted_at' , null)->where('category_type' ,CategoryType::SERVICE)->get();

        $providers = User::role(RoleEnum::PROVIDER)->with('media')->get();
        $consumers = User::role(RoleEnum::CONSUMER)->with('media')->get();
        $cartItems = Cart::where('customer_id' , Helpers::getCurrentUserId())->get();

        return view('backend.pos.create' , ['categories' => $categories , 'providers' => $providers , 'consumers' => $consumers,'cartItems' => $cartItems]);
    }

    public function filterServices(Request $request)
    {
        $providerId = $request->provider_id;
        $search = $request->search;

        $categories = Category::where('parent_id', null)
            ->where('status', true)
            ->whereNull('deleted_at')
            ->where('category_type', CategoryType::SERVICE)
            ->with(['services' => function ($query) use ($providerId, $search) {
                if ($providerId) {
                    $query->where('user_id', $providerId);
                }
                if ($search) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
            }])
            ->get();

            $firstActiveIndex = $categories->search(function ($category) {
                return $category->services->isNotEmpty();
            });


            $cartItems = Cart::where('customer_id' , Helpers::getCurrentUserId())->get();

            return response()->json([
                'html' => view('backend.pos.service-list', compact('categories' , 'cartItems'))->render(),
                'activeIndex' => $firstActiveIndex ?? 0
            ]);
    }

    public function getAddresses(Request $request)
    {
        $consumerId = $request->consumer_id;


        if($consumerId) {
            $user = User::where('id' , $consumerId)->first();
        }

        return response()->json([
            'html' => view('backend.pos.addresses', compact('user'))->render(),
        ]);

    }

    public function serviceBooking(Request $request)
    {
        return $this->repository->serviceBooking($request);
    }

    public function serviceCheckout(Request $request)
    {
        return $this->repository->serviceCheckout($request);
    }

    public function clearCart(Request $request)
    {
        return $this->repository->clearCart($request);
    }
    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }

    public function addAddress(Request $request)
    {
        return $this->repository->addAddress($request);
    }

}
