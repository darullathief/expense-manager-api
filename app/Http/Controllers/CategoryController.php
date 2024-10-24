<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Add Category
     */
    public function add(Request $request){
        $validate = Validator::make($request->all(),[
            'name' => 'required|string'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan",
                'data' => $validate->errors(),
            ]);
        }
        
        try {
            $category = new Category();
            $category->name = $request->name;
            $category->save();

            $data = $category->find($category->id);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan",
                'data' => $e->getMessage()
            ]);
        }
    }
}
