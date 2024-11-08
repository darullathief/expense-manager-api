<?php

namespace App\Http\Controllers;

use App\Models\Budgets;
use App\Models\Category;
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
                return $this->get_custom_range_transaction($request->start_date, $request->end_date, $user->id);
            default:
                return response()->json([
                    'success' => false,
                    'message' => "Terjadi Kesalahan",
                ], 400);
                break;
        }
    }

    /**
     * Get Calculation
     */
    public function get_calculation(Request $request){
        $validate = Validator::make($request->all(),[
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'group' => 'required|in:date,category'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validate->errors(),
            ], 400);
        }
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Gagal melakukan aksi, user belum login",
            ], 400);
        }

        $data = array();
        $total_expense = (float)Transaction::where('user_id', $user->id)
                ->whereBetween('date',[$request->start_date, $request->end_date])
                ->sum('amount');

        $data['total'] = $total_expense;
        $data['group_by'] = $request->group;

        switch ($request->group) {
            case 'date':
                $all_data = $this->group_calculation_by_date(
                    $user->id, $total_expense, $request->start_date, $request->end_date);

                $data['all_data'] = $all_data;

                return response()->json([
                    'success' => true,
                    'data' => $data
                ], 200);
            case 'category':
                $all_data = $this->group_calculation_by_category(
                    $user->id, $total_expense, $request->start_date, $request->end_date);

                    $data['all_data'] = $all_data;

                    return response()->json([
                        'success' => true,
                        'data' => $data
                    ], 200);
        }
    }

    /**
     * Get Balance
     */
    public function balance(Request $request){
        $validate = Validator::make($request->all(),[
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validate->errors(),
            ], 400);
        }
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Gagal melakukan aksi, user belum login",
            ], 400);
        }

        $budget = (float)Budgets::where('user_id', $user->id)
                ->whereBetween('date', [$request->start_date, $request->end_date])
                ->sum('amount');

        $transaction = (float)Transaction::where('user_id', $user->id)
                ->whereBetween('date', [$request->start_date, $request->end_date])
                ->sum('amount');

        $balance = $budget - $transaction;

        return response()->json([
            'success' => true,
            'data' => $balance
        ], 200);
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

    private function get_custom_range_transaction($start_date, $last_date, $user_id){
        $transaction = Transaction::where('user_id', $user_id)
                  ->whereBetween('date', [$start_date, $last_date])
                  ->get();

        return response()->json([
            "success" => true,
            "data" => $transaction
        ], 200);
    }

    private function group_calculation_by_date($user_id, $total, $start_date, $end_date){
        $all_data = array();

        for ($i = strtotime($start_date); $i <= strtotime($end_date); $i += 86400) {
            $date = date('Y-m-d', $i);

            $transaction = (float)Transaction::where([
                ['user_id', $user_id],
                ['date', $date]
            ])->sum('amount');

            if (!empty($transaction) && $transaction > 0) {
                $percent = ($transaction / $total) * 100;
                $all_data[$date]['amount'] = $transaction;
                $all_data[$date]['percent'] = $percent;
            } else {
                $all_data[$date]['amount'] = 0;
                $all_data[$date]['percent'] = 0;
            }
        }

        return $all_data;
    }

    private function group_calculation_by_category($user_id, $total, $start_date, $end_date){
        $all_data = array();
        $category = Category::where('type', 'expense')
                ->select('id','name')
                ->get();

        $items = json_decode($category, true);

        foreach ($items as $i => $item) {
            $transaction = (float)Transaction::where([
                    ['user_id', $user_id], 
                    ['category_id', $item['id']]
                ])
                ->whereBetween('date', [$start_date, $end_date])
                ->sum('amount');

            if (!empty($transaction) && $transaction > 0) {
                $percent = ($transaction / $total) * 100;
                $all_data[$item['name']]['amount'] = $transaction;
                $all_data[$item['name']]['percent'] = $percent;
            } else {
                $all_data[$item['name']]['amount'] = 0;
                $all_data[$item['name']]['percent'] = 0;
            }
        }
        return $all_data;
    }
}
