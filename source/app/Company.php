<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use App\CompanyDynamoDB;

class Company extends Model
{
    protected $fillable = ['admin_id', 'company_name', 'address', 'phone_number', 'email', 'url', 'agency_name', 'business_field', 'latitude', 'longitude', 'introduction', 'del'];
    protected $dateFormat = 'U';
    public $timestamps = true;

    public static function checkExistCompany($company_id)
    {
        $company = Company::find($company_id);
        return ($company) ? true : false;
    }

    public static function editByStaff($company_id, $company_name, $address, $email, $phone_number, $url, $agency_name, $business_field, $latitude, $longitude, $introduction)
    {
        $company = Company::find($company_id);
        $company->company_name = $company_name;
        $company->address = $address;
        $company->phone_number = $phone_number;
        $company->email = $email;
        $company->url = $url;
        $company->agency_name = $agency_name;
        $company->business_field = $business_field;
        $company->latitude = $latitude;
        $company->longitude = $longitude;
        $company->introduction = $introduction;
        $company->save();
        return $company->id;
    }

    public function getBusinessFields()
    {
        return BusinessField::select('id', 'business_name')->all();
    }

    public static function getDetailCompany($id)
    {
        $result = Company::select(
            'companies.id',
            'companies.company_name',
            'companies.address',
            'companies.email',
            'companies.phone_number',
            'companies.url',
            'companies.agency_name',
            'companies.business_field',
            'business_fields.business_name',
            'companies.latitude',
            'companies.longitude',
            'companies.introduction',
            'business_fields.business_name'
        )
        ->leftjoin('business_fields', 'companies.business_field', '=', 'business_fields.id')
        ->where('companies.id', $id)
        ->first();

        return $result;
    }


    public static function getCompanyByJobId($id)
    {
        $query = Company::select('companies.company_name', 'companies.id')
                        ->leftjoin('jobs', 'jobs.company_id', '=', 'companies.id')
                        ->where('jobs.id', $id)
                        ->first();
        return $query;
    }

    public static function getCompanyName($id)
    {
        $company = Company::find($id);
        return $company->company_name;
    }

    public static function getBasePath()
    {
        $path = '';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $path = 'https://' . $_SERVER['HTTP_HOST'] . '/upload/company/';
        } else {
            $path = 'http://' . $_SERVER['HTTP_HOST'] . '/upload/company/';
        }
        return $path;
    }
    public static function store($update_parameters)
    {
        $next_company_id = DB::table('companies')->max('id') + 1;
        $company = Company::updateOrCreate($update_parameters);
        if ($company->id == $next_company_id) {
            CompanyDynamoDB::add($company->id);
        }
        return $company->id;
    }
    public static function deleteById($id)
    {
        Company::where('id', $id)->delete();
        CompanyDynamoDB::find(intval($id))->delete();
    }
}
