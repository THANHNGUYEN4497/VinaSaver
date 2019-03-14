<?php

namespace App;

use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

use App\StaffDynamoDB;

class Staff extends Authenticatable
{
    protected $table = 'staffs';
    protected $dateFormat = 'U';
    public $timestamps = true;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id','username', 'email', 'phone_number', 'privilege', 'password','api_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'created_at', 'updated_at'
    ];

    public static function getStaffOfCompany($company_id, $page_limit, $page_number, $keyword, $phone_number, $position)
    {
        $staffs = Staff::select('staffs.id', 'company_id', 'username', 'email', 'office', 'phone_number', 'privilege', 'positions.position_name')
                    ->orderBy('id', 'DESC')
                    ->where('company_id', $company_id)
        ->leftjoin('positions', 'positions.id', '=', 'staffs.privilege');
        if ($keyword) {
            $staffs->where(function ($query) use ($keyword) {
                $query->where('username', 'like', "%$keyword%")
                    ->orWhere('email', 'like', "%$keyword%");
            });
        }
        if ($position) {
            $staffs->where('privilege', $position);
        }
        if ($phone_number) {
            $staffs->where('phone_number', 'like', "%$phone_number%");
        }
        $count = $staffs->count();
        $staffs->offset(($page_number - 1) * $page_limit)
                ->limit($page_limit);
        return array('data' => $staffs->get(), 'total' => $count);
    }

    public static function detail($staff_id)
    {
        $staff = Staff::select('id', 'company_id', 'username', 'email', 'phone_number', 'privilege', 'office')
            ->where('id', $staff_id)
            ->first();
        return $staff;
    }
    public function login($api_token)
    {
        $this->api_token = $api_token;
        $this->save();

        $staff_dynamo = StaffDynamoDB::find($this->id);
        $staff_dynamo->apiToken = $api_token;
        $staff_dynamo->save();
    }
    public function logout()
    {
        $this->api_token = null;
        $this->save();

        $staff_dynamo = StaffDynamoDB::find($this->id);
        $staff_dynamo->apiToken = null;
        $staff_dynamo->save();
    }

    public static function add($company_id, $email, $password, $username, $phone_number, $office, $position)
    {
        $staff = new Staff();
        $staff->company_id = $company_id;
        $staff->email = $email;
        $staff->password = bcrypt($password);
        $staff->username = $username;
        $staff->phone_number = $phone_number;
        $staff->office = $office;
        $staff->privilege = $position;
        $staff->save();

        StaffDynamoDB::add($staff);

        return $staff->id;
    }
    public static function edit($staff_id, $email, $password, $username, $phone_number, $office, $position)
    {
        $staff = Staff::find($staff_id);
        if (!empty($password)) {
            $staff->password = bcrypt($password);
        }
        $staff->email = $email;
        $staff->username = $username;
        $staff->phone_number = $phone_number;
        $staff->office = $office;
        $staff->privilege = $position;
        $staff->save();
        return $staff->id;
    }

    public static function checkEmail($email)
    {
        $check = Staff::select('email')
                        ->where('email', $email)
                        ->first();
        return ($check) ? false : true;
    }
    public static function checkEmailUpdate($staff_id, $email)
    {
        $check = Staff::select('id')
                        ->where('id', $staff_id)
                        ->where('email', $email)
                        ->first();
        return ($check) ? true : false;
    }

    public static function checkStaff($staff_id)
    {
        $check = Staff::find($staff_id);
        return ($check) ? true : false;
    }

    public static function remove($staff_id)
    {
        $staff = Staff::find($staff_id);
        $staff->delete();

        StaffDynamoDB::remove($staff_id);

        return true;
    }


    public static function getAllChildStaff($staff_id, $privilege, $company_id)
    {
        $result = Staff::select('id', 'username')
                ->where('company_id', $company_id);
        if ($privilege == 2) {
            $result->where('id', $staff_id);
        } else {
            $result->where('privilege', 2);
            $result->orwhere('id', $staff_id);
        }
        return $result->get();
    }
    public static function store($update_parameters)
    {
        $next_staff_id = DB::table('staffs')->max('id') + 1;
        //FIXME: 同姓同名が居たらどうなる??
        $staff = Staff::updateOrCreate($update_parameters);
        if ($staff->id == $next_staff_id) {
            StaffDynamoDB::add($staff);
        }
        return $staff->id;
    }
    public static function deleteByCompanyId($company_id)
    {
        $staffs = Staff::where('company_id', $company_id)->get();
        foreach ($staffs as $staff) {
            $result = StaffDynamoDB::remove($staff->id);
            $staff->delete();
        }
    }
}
