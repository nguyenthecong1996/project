<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Food;
use App\Models\Store;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function createCategory(Request $request)
    {
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

    public function listHome()
    {
        $listCate = Category::where('active', 1)->limit(5)->get();
        $listBreakfask = Food::with(['store'])->whereHas('category', function ($query) {
            $query->where('type', '=', 0);
        })->limit(2)->get();

        $listlunch = Food::with(['store'])->whereHas('category', function ($query) {
            $query->where('type', '=', 1);
        })->limit(2)->get();

        $listdinner = Food::with(['store'])->whereHas('category', function ($query) {
            $query->where('type', '=', 2);
        })->limit(2)->get();

        $listOrder = Food::with(['store'])->whereHas('category', function ($query) {
            $query->where('type', '=', 3);
        })->limit(2)->get();


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

    public function listFood(Request $request)
    {
        $list = Food::with(['store', 'foodTag']);
        if (isset($request->search)) {
            $list =  $list->where('name', 'LIKE', "%$request->search%");
        }
        $list = $list->paginate(4);
        return response()->json([
            'data' =>  $list,
        ], 200);
    }
}
