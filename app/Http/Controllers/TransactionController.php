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
}
