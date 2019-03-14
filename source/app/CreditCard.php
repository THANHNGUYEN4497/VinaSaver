<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class CreditCard extends Model
{
    protected $table = 'credit_cards';
    protected $dateFormat = 'U';
    //type=1: company account , type =2: connector account
    protected $fillable = [
        'customer_id','bank_type','account_type', 'branch_code', 'card_number', 'frist_name', 'last_name', 'type'
    ];

    public static function add($customer_id, $bank_type, $account_type, $branch_code, $card_number, $security_code, $first_name, $last_name, $type)
    {
        $credit_card = new CreditCard();
        $credit_card->customer_id = $customer_id;
        $credit_card->bank_type = $bank_type;
        $credit_card->account_type = $account_type;
        $credit_card->branch_code = $branch_code;
        $credit_card->card_number = $card_number;
        $credit_card->security_code = $security_code;
        $credit_card->first_name = $first_name;
        $credit_card->last_name = $last_name;
        $credit_card->type = $type;
        $credit_card->save();
        return $credit_card->id;
    }

    public static function edit($id, $customer_id, $bank_type, $account_type, $branch_code, $card_number, $security_code, $first_name, $last_name)
    {
        $credit_card = CreditCard::find($id);
        $credit_card->customer_id = $customer_id;
        $credit_card->bank_type = $bank_type;
        $credit_card->account_type = $account_type;
        $credit_card->branch_code = $branch_code;
        $credit_card->card_number = $card_number;
        $credit_card->security_code = $security_code;
        $credit_card->first_name = $first_name;
        $credit_card->last_name = $last_name;
        $credit_card->save();
        return $credit_card->id;
    }

    public static function checkExist($id)
    {
        $check = CreditCard::find($id);
        return ($check) ? true : false;
    }

    public static function detail($id)
    {
        return CreditCard::find($id);
    }

    public static function list($company_id)
    {
        return CreditCard::select('id', 'customer_id', 'bank_type', 'account_type', 'branch_code', DB::raw('SUBSTRING(card_number, -4) AS card_number'), 'first_name', 'last_name')
                ->where('customer_id', $company_id)
                ->where('type', 1)
                ->orderBy('id', 'DESC')
                ->get();
    }

    public static function remove($id)
    {
        return CreditCard::destroy($id);
    }
}
