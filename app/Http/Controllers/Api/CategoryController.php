<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    //


    public function rootList()
    {
        $category = Category::where('aParentId', '0')->withCount('sub_category')->get();
        return response()->json(['status' => 1, 'message' => 'root category', 'category' => $category]);
    }

    public function getCategoryChild(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return response()->json(['status' => 0, 'message' => $firstError]);
        }

        $categories = Category::where('aParentId', $request->category_id)->with('sub_category')->get();

        // foreach(){

        // }

        return response()->json(['status' => 1, 'message' => 'root category', 'category' => $categories]);
    }
}
