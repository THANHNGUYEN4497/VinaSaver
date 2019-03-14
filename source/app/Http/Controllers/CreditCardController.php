<?php

namespace App\Http\Controllers;

use App\CreditCard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CreditCardController extends Controller
{
    private $success = false;
    private $data = null;
    private $error = null;

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required| numeric',
            'bank_type' => 'required| numeric',
            'account_type' => 'required| numeric',
            'branch_code' => 'required| numeric',
            'card_number' => 'required| max:255',
            'security_code' => 'required| numeric',
            'type' => 'required| numeric',
            'first_name' => 'required| max:255',
            'last_name' => 'required| max:255'
        ], [
            'required' => \Lang::get('common_message.error.MISS_PARAM'),
            'numeric' => \Lang::get('common_message.error.TYPE_INCORRECT'),
            'max' => \Lang::get('common_message.error.MAX_CONTENT')
        ]);

        if ($validator->fails()) {
            $this->error = $this->messgeValidate($validator->errors()->all());
        } else {
            try {
                $customer_id = $request->input('customer_id');
                $bank_type = $request->input('bank_type');
                $account_type = $request->input('account_type');
                $branch_code = $request->input('branch_code');
                $card_number = $request->input('card_number');
                $security_code = $request->input('security_code');
                $first_name = $request->input('first_name');
                $last_name = $request->input('last_name');
                $type = $request->input('type');
                $this->data = CreditCard::add($customer_id, $bank_type, $account_type, $branch_code, $card_number, $security_code, $first_name, $last_name, $type);
                $this->success = true;
            } catch (\Illuminate\Database\QueryException $ex) {
                \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
                $this->error = $ex->getMessage();
            } catch (\Illuminate\Exception $ex) {
                \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
                $this->error = $ex->getMessage();
            }
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    public function edit(Request $request, $credit_card_id)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required| numeric',
            'bank_type' => 'required| numeric',
            'account_type' => 'required| numeric',
            'branch_code' => 'required| numeric',
            'card_number' => 'required| max:255',
            'security_code' => 'required| numeric',
            'first_name' => 'required| max:255',
            'last_name' => 'required| max:255'
        ], [
            'required' => \Lang::get('common_message.error.MISS_PARAM'),
            'numeric' => \Lang::get('common_message.error.TYPE_INCORRECT'),
            'max' => \Lang::get('common_message.error.MAX_CONTENT')
        ]);

        if ($validator->fails()) {
            $error = $this->messgeValidate($validator->errors()->all());
        } else {
            try {
                $check = CreditCard::checkExist($credit_card_id);
                if ($check) {
                    $customer_id = $request->input('customer_id');
                    $bank_type = $request->input('bank_type');
                    $account_type = $request->input('account_type');
                    $branch_code = $request->input('branch_code');
                    $card_number = $request->input('card_number');
                    $security_code = $request->input('security_code');
                    $first_name = $request->input('first_name');
                    $last_name = $request->input('last_name');
                    $type = $request->input('type');
                    $this->data = CreditCard::edit($credit_card_id, $customer_id, $bank_type, $account_type, $branch_code, $card_number, $security_code, $first_name, $last_name, $type);
                    $this->success = true;
                } else {
                    $this->error = \Lang::get('common_message.error.OBJECT_NOT_EXIST');
                }
            } catch (\Illuminate\Database\QueryException $ex) {
                \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
                $this->error = $ex->getMessage();
            } catch (\Illuminate\Exception $ex) {
                \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
                $this->error = $ex->getMessage();
            }
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    public function messgeValidate($errors)
    {
        $arr_errors = array_unique($errors);
        $message = "";
        $i = 0;
        $len = count($arr_errors);
        foreach ($arr_errors as $err) {
            if ($i == 0) {
                $message .= $err;
            }
            if (($i > 0) && ($i == $len - 1)) {
                $message .=  " & " . $err;
            }
            if (($i > 0) && ($i < $len - 1)) {
                $message .=  ", " . $err;
            }
            $i++;
        }
        return $message;
    }

    public function show($id)
    {
        $success = false;
        $data = null;
        $error = null;
        try {
            $check = CreditCard::checkExist($id);
            if ($check) {
                $data = CreditCard::detail($id);
                $success = true;
            } else {
                $error = \Lang::get('common_message.error.OBJECT_NOT_EXIST');
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $success = false;
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $error = $ex->getMessage();
        } catch (\Illuminate\Exception $ex) {
            $success = false;
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $error = $ex->getMessage();
        }
        return $this->doResponse($success, $data, $error);
    }

    public function index()
    {
        try {
            $company_id = null;
            if (Auth::guest()) {
                $user = Auth::guard('staff-api')->user();
                $company_id = $user->company_id;
                $this->data = CreditCard::list($company_id);
                $this->success = true;
            } else {
                $this->error = \Lang::get('common_message.error.QUERY_FAIL');
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $this->success = false;
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $this->error = $ex->getMessage();
        } catch (\Illuminate\Exception $ex) {
            $this->success = false;
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $error = $ex->getMessage();
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    public function delete($id)
    {
        try {
            $check = CreditCard::checkExist($id);
            if ($check) {
                $this->data = CreditCard::remove($id);
                $this->success = true;
            } else {
                $this->error = \Lang::get('common_message.error.OBJECT_NOT_EXIST');
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $this->success = false;
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $this->error = $ex->getMessage();
        } catch (\Illuminate\Exception $ex) {
            $this->success = false;
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $this->error = $ex->getMessage();
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }
}
