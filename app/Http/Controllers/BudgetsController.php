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
            'description' => 'string'
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

    /**
     * Get All Budget For A Month
     */
    public function get(Request $request){
        $validate = Validator::make($request->all(),[
            'date' => 'date_format:Y-m-d'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan",
                'data' => $validate->errors(),
            ]);
        }
        $user = auth('sanctum')->user();

        try {
            $date = (!empty($request->date)) ? $request->date : date("Y-m-d");
            $first_date = date("Y-m-01", strtotime($date));
            $last_date = date("Y-m-t", strtotime($date));

            $budget = Budgets::where('user_id', $user->id)
                  ->whereBetween('date', [$first_date, $last_date])
                  ->get();

            return response()->json([
                'success' => true,
                'data' => $budget
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan",
                'data' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get Total Budget For A Month
     */
    public function get_month_calculation(Request $request){
        $validate = Validator::make($request->all(),[
            'date' => 'date_format:Y-m-d'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan",
                'data' => $validate->errors(),
            ]);
        }
        $user = auth('sanctum')->user();

        try {
            $date = (!empty($request->date)) ? $request->date : date("Y-m-d");
            $first_date = date("Y-m-01", strtotime($date));
            $last_date = date("Y-m-t", strtotime($date));

            $budget = Budgets::where('user_id', $user->id)
                  ->whereBetween('date', [$first_date, $last_date])
                  ->select('amount')
                  ->get();

            $calculate = 0;
            for ($i=0; $i < count($budget); $i++) { 
                $calculate += (float)$budget[$i]->amount;
            }

            return response()->json([
                'success' => true,
                'data' => $calculate
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan",
                'data' => $e->getMessage()
            ]);
        }
    }

    /**
     * Edit Budget
     */
    public function edit(Request $request) {
        $validate = Validator::make($request->all(),[
            'id' => 'required|integer',
            'category_id' => 'integer',
            'amount' => 'numeric',
            'date' => 'date_format:Y-m-d',
            'description' => 'string', 
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'terjadi kesalahan',
                'date' => $validate->errors()
            ], 400);
        }

        $user = auth('sanctum')->user();
        $data = $request->all();
        $data['user_id'] = $user->id;
        try {
           Budgets::where('id',$request->id)->update($data);

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
            ]);
        }
    }

    /**
     * Delete budget
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
            ]);
        }

        try {
            $budget = Budgets::find($request->id);
            $budget->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil dihapus",
                'data' => $budget
            ], 200);
            
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => "Terjadi kesalahan",
                'data' => $e->getMessage(),
            ]);
        }
    }
}