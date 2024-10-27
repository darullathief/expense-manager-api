<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Add Transaction
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
            ], 400);
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
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $request->amount;
            $transaction->date = (!empty($request->date)) ? $request->date : date('Y-m-d');
            $transaction->category_id = (!empty($request->category_id)) ? $request->category_id : null;
            $transaction->description = (!empty($request->description)) ? $request->description : null;
            $transaction->save();

            $data = Transaction::find($transaction->id);

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
     * Edit transaction
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
                'message' => 'Terjadi kesalahan',
                'date' => $validate->errors()
            ], 400);
        }

        $user = auth('sanctum')->user();
        $data = $request->all();
        $data['user_id'] = $user->id;
        try {
           Transaction::where('id',$request->id)->update($data);

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
     * Delete transaction
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
            $transaction = Transaction::find($request->id);
            $transaction->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil dihapus",
                'data' => $transaction
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
     * Get Transaction
     */
    public function get(Request $request){
        $validate = Validator::make($request->all(), [
            'id' => 'integer',
            'span' => 'required_without:id|in:daily,weekly,monthly,custom',
            'start_date' => 'date_format:Y-m-d|required_if:span,custom',
            'end_date' => 'date_format:Y-m-d|required_if:span,custom',
        ]);

        if ($validate->fails()) {
            return response()->json([
                "success" => false,
                "message" => $validate->errors()
            ], 400);
        }
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Gagal melakukan aksi, user belum login",
            ], 400);
        }

        if (!empty($request->id)) {
            $transaction = Transaction::find($request->id);
            if (empty($transaction)) {
                return response()->json([
                    'success' => true,
                    'message' => "Data Kosong",
                ], 201);
            }
            return response()->json([
                "success" => true,
                "data" => $transaction
            ], 200);
        }

        switch ($request->span) {
            case 'daily':
                return $this->get_daily_transaction($request->start_date, $user->id);
            case 'weekly':
               return $this->get_weekly_transaction($request->start_date, $user->id);
            case 'monthly':
                return $this->get_monthly_transaction($request->start_date, $user->id);
            case 'custom':
                # code...
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => "Terjadi Kesalahan",
                ], 400);
                break;
        }
    }

    private function get_daily_transaction($date, $user_id){
        $curr_date = (!empty($date)) ? $date : date("Y-m-d");

        $transaction = Transaction::where([
            ['user_id', $user_id],
            ['date', $curr_date]
        ])->get();

        return response()->json([
            "success" => true,
            "data" => $transaction
        ], 200);
    }

    private function get_weekly_transaction($date, $user_id){
        $curr_date = (!empty($date)) ? $date : date("Y-m-d");
        $start_date = date("Y-m-d", strtotime('monday this week', strtotime($curr_date)));
        
        $transaction = Transaction::where('user_id', $user_id)
                  ->whereBetween('date', [$start_date, $curr_date])
                  ->get();

        return response()->json([
            "success" => true,
            "data" => $transaction
        ], 200);
    }

    private function get_monthly_transaction($date, $user_id){
        $curr_date = (!empty($date)) ? $date : date("Y-m-d");
        $start_date = date("Y-m-01", strtotime($curr_date));
        
        $transaction = Transaction::where('user_id', $user_id)
                  ->whereBetween('date', [$start_date, $curr_date])
                  ->get();

        return response()->json([
            "success" => true,
            "data" => $transaction
        ], 200);
    }
}
