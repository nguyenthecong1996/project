<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Food;
use App\Models\Store;
use App\Models\Order;
use App\Http\Resources\UserCollection;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function createCategory(Request $request)
    {
        $stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET')
          );

        //   $data = $stripe->customers->all(['limit' => 3]);
        //   dd($data['data']);
        //   $stripe->customers->create([
        //     'description' => 'My First Test Customer (created for API docs at https://www.stripe.com/docs/api)',
        //   ]);

        // $stripe->customers->createSource(
        //     'cus_LuOhW6puNYKRSR',
        //     ['source' => 'tok_visa',
        //     ]
        //   );

        // $stripe->customers->updateSource(
        //     'cus_LuOhW6puNYKRSR',
        //     'card_1LCanQGWhquycQGHf4wjamqy',
        //     ['name' => 'Jenny Rosen']
        //   );

        // $stripe->customers->update(
        //     'cus_LuOhW6puNYKRSR',
        //     ['default_source' => 'card_1LCanQGWhquycQGHf4wjamqy']
        // );

        // \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        // $charge = \Stripe\Charge::create([
        //     'amount' => 1000,
        //     'currency' => 'usd',
        //     'customer' => 'cus_LuOhW6puNYKRSR',
        //     'source' => 'card_1LCanQGWhquycQGHf4wjamqy',
        // ]);

        // $stripe->accounts->create([
        //     'type' => 'express',
        //     'country' => 'US',
        //     'email' => 'thecong1996@gmail.com',
        //     'capabilities' => [
        //       'card_payments' => ['requested' => true],
        //       'transfers' => ['requested' => true],
        //     ],
        //   ]);

        $data = $stripe->accountLinks->create(
            [
              'account' => 'acct_1LCfP42eJ6niEmXb',
              'refresh_url' => 'https://24ba-222-252-30-49.ap.ngrok.io/test1',
              'return_url' => 'https://24ba-222-252-30-49.ap.ngrok.io/test2',
              'type' => 'account_onboarding',
            ]
          );

        return response()->json([
            'message' =>  $data,
        ], 200);
        $data = [
            'name' => $request->name,
            'type' => $request->type,
            'active' => 1
        ];
        $list = Category::create($data);

        return response()->json([
            'message' =>  $list,
        ], 200);
    }

    public function test1()
    {
        dd(1);
    }

    public function test2()
    {
        dd(2);
    }

    public function listHome()
    {
        $listCate = Category::where('active', 1)->limit(5)->get();
        $listBreakfask = Food::with(['store'])->whereHas('category', function ($query) {
            $query->where('type', '=', 0);
        })->get();

        $listlunch = Food::with(['store'])->whereHas('category', function ($query) {
            $query->where('type', '=', 1);
        })->get();

        $listdinner = Food::with(['store'])->whereHas('category', function ($query) {
            $query->where('type', '=', 2);
        })->get();

        $listOrder = Food::with(['store'])->whereHas('category', function ($query) {
            $query->where('type', '=', 3);
        })->get();


        $store = Store::where('active', 1)->limit(3)->get();
        return response()->json([
            'list_cate' =>  $listCate,
            'list_breakfask' =>  $listBreakfask,
            'listlunch' =>  $listlunch,
            'listdinner' =>  $listdinner,
            'listOrder' =>  $listOrder,
            'store' =>  $store,
        ], 200);
    }

    public function listCate()
    {
        $listCate = Category::paginate(5);
        return response()->json($listCate);
    }

    public function listFood(Request $request)
    {
        $list = Food::with(['store', 'foodTag']);
        if (isset($request->search)) {
            $list =  $list->where('name', 'LIKE', "%$request->search%");
        }
        $list = $list->paginate(10);
        return response()->json([
            'data' =>  $list,
        ], 200);
    }

    public function detailFood($id)
    {
        $list = Food::with(['store', 'foodTag', 'ingredient'])->where('id', $id)->first();
        return response()->json([
            'data' =>  $list,
        ], 200);
    }

    public function listFoodOrder(Request $request)
    {
        $userId = $request->user()->id;
        $list = Order::with(['store', 'itemFood'])
            ->where('user_id', $userId)
            ->where('status', 1)->paginate(10);

        return response()->json([
            'data' => new UserCollection($list),
        ], 200);  
    }

    public function deleteCategory($id)
    {
        Category::find($id)->delete();
        return response()->json([
            'delete' =>  'Ok'
        ], 200);
    }
}
