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
            ], 400);
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
            ], 400);
        }
    }

    /**
     * Get Category
     */
    public function get(){
        $category = Category::all();

        if (empty($category)) {
            return response()->json([
                'success' => true,
                'message' => "Data Kosong",
            ], 201);
        } else {
            return response()->json([
                'success' => true,
                'data' => $category
            ], 200);
        }
    }

    /**
     * Edit Category
     */
    public function edit(Request $request){
        $validate = Validator::make($request->all(),[
            'id' => 'required|integer',
            'name' => 'required|string'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan",
                'data' => $validate->errors(),
            ], 400);
        }

        $data = $request->all();
        
        try {
            Category::where('id', $request->id)->update($data);
            return response()->json([
                'success' => true,
                'message' => "Berhasil diupdate",
                'data' => $data
            ], 200);
            
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan",
                'data' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete Category
     */
    public function delete(Request $request) {
        $validate = Validator::make($request->all(),[
            'id' => 'required|integer'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan",
                'data' => $validate->errors(),
            ], 400);
        }

        try {
            $category = Category::find($request->id);
            $category->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil dihapus",
                'data' => $category
            ], 200);
            
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan",
                'data' => $e->getMessage(),
            ], 400);
        }
    }
}
