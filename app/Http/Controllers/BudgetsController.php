<?php

namespace App\Http\Controllers;

use App\Models\Budgets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class BudgetsController extends Controller
{
    /**
     * Add Budget
     */
    public function add(Request $request){
        $validate = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'category_id' => 'integer',
            'date' => 'date_format:Y-m-d',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan",
                'data' => $validate->errors(),
            ]);
        }
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Gagal melakukan aksi, user belum login",
                'data' => [],
            ], 400);
        }

        try {     
            $budget = new Budgets();
            $budget->user_id = $user->id;
            $budget->amount = $request->amount;
            $budget->date = (!empty($request->date)) ? $request->date : date('Y-m-d');
            $budget->category_id = (!empty($request->category_id)) ? $request->category_id : null;
            $budget->description = (!empty($request->description)) ? $request->description : null;
            $budget->save();

            $data = Budgets::find($budget->id);

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
