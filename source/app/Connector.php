<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\ConnectorDynamoDB;

class Connector extends Authenticatable
{
    protected $table = 'connectors';
    protected $timstamps = true;
    protected $dateFormat = 'U';
    protected $fillable = ['phone_number', 'password','username', 'email', 'avatar', 'address', 'birthday', 'gender', 'connector_code', 'introduction_code', 'auth_number', 'available_status', 'username_phonetic', 'current_work', 'current_work_place'];
    public static $minAuthNumRand = 10000;
    public static $maxAuthNumRand = 99999;

    public static function getListConnector($page_limit, $page_number, $keyword, $phone_number)
    {
        $page_number = ($page_number - 1) * $page_limit;
        $connector = Connector::query();
        if (!empty($phone_number)) {
            $connector = $connector ->where('phone_number', 'LIKE', '%' . $phone_number . '%');
        }
        if (!empty($keyword)) {
            $connector = $connector ->where('username', 'LIKE', '%' . $keyword . '%');
            $connector = $connector ->orwhere('email', 'LIKE', '%' . $keyword . '%');
        }
        $data = array();
        $data['total_items']  = $connector->count();
        $connector = $connector ->orderBy('id', 'desc')
                               ->offset($page_number)
                               ->limit($page_limit)
                               ->get();
        $data['data'] = $connector;
        if ($data) {
            return $data;
        } else {
            return null;
        }
    }
    public function login($api_token)
    {
        $this->api_token = $api_token;
        $this->save();

        $connector_dynamo = ConnectorDynamoDB::find($this->id);
        $connector_dynamo->apiToken = $api_token;
        $connector_dynamo->save();
    }
    public function logout()
    {
        $this->api_token = null;
        $this->save();

        $connector_dynamo = ConnectorDynamoDB::find($this->id);
        $connector_dynamo->apiToken = null;
        $connector_dynamo->save();
    }

    public static function add($phone_number, $password, $username, $gender, $address)
    {
        $result = null;
        $exist_connector_id = Connector::getIdByPhoneNumber($phone_number);
        if (!empty($exist_connector_id)) {
            $result = $exist_connector_id;
        } else {
            $connector = new Connector();
            $connector->phone_number = $phone_number;
            $connector->password = bcrypt($password);
            $connector->username = (!empty($username)) ? $username : null;
            $connector->gender = (!empty($gender)) ? $gender : null;
            $connector->address = (!empty($address)) ? $address : null;
            $connector->connector_code = Connector::generate_connector_code();  //TODO:SHA256
            $connector->auth_number = 99999;    //TODO: RFC6238
            $connector->available_status = 0;   //0: unavailable(un-verified), 1: available(verified), 2: unavailable(frozen)
            $connector->save();

            $connector_dynamodb = new ConnectorDynamoDB();
            $connector_dynamodb->connectorId = $connector->id;
            $connector_dynamodb->save();

            $result = $connector->id;
        }
        return $result;
    }
    public static function changeAvailableStatus($id, $status)
    {
        return Connector::find($id)->update(['available_status' => $status]);
    }

    public static function getIdByPhoneNumber($phone_number)
    {
        $connector = Connector::query();
        $connector->where('phone_number', $phone_number);
        $result = $connector->first();
        if (!empty($result)) {
            return $result->id;
        } else {
            return null;
        }
    }

    public static function get_id_by_code($code)
    {
        $connector = Connector::query();
        $connector->where('connector_code', $code);
        $result = $connector->first();
        if (!empty($result)) {
            return $result->id;
        } else {
            return null;
        }
    }

    public static function getNameById($id)
    {
        $connector = Connector::query();
        $connector->where('id', $id);
        $result = $connector->first();
        if (!empty($result)) {
            return $result->username;
        } else {
            return null;
        }
    }
    
    public static function getDetailByAdmin($id)
    {
        $connector = Connector::select(
            'connectors.id',
            'connectors.username',
            'connectors.username_phonetic',
            'connectors.email',
            'connectors.password',
            'connectors.phone_number',
            'connectors.birthday',
            'connectors.gender',
            'connectors.current_work',
            'connectors.current_work_place',
            'connectors.connector_code'
        )
            ->where('connectors.id', $id)
            ->first();
        return $connector;
    }

    public static function getDetailByConnector($id)
    {
        $connector = Connector::select('id', 'username', 'email', 'avatar', 'phone_number', 'birthday', 'gender', 'address', 'connector_code', 'username_phonetic', 'current_work', 'current_work_place')
                    ->where('id', $id)
                    ->first();
        $path = '';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $connector['base_path'] = 'https://' . $_SERVER['HTTP_HOST'] . '/upload/connector/';
        } else {
            $connector['base_path'] = 'http://' . $_SERVER['HTTP_HOST'] . '/upload/connector/';
        }
         
        return $connector;
    }

    public static function getApplicantById($id)
    {
        $connector = Connector::select('id', 'username', 'email', 'phone_number', 'birthday', 'gender', 'connector_code')
                    ->where('id', $id)
                    ->first();
        return $connector;
    }
    
    public static function deleteById($id)
    {
        $connector = Connector::destroy($id);
        $result_dynamodb = ConnectorDynamoDB::remove($id);
        if ($connector) {
            return true;
        } else {
            return false;
        }
    }

    //TODO: SHA256
    public static function generate_connector_code()
    {
        $seed = str_split('0123456789' . 'abcdefghijklmnopqrstuvwxyz');
        shuffle($seed);
        $connector_code = '';
        $unique = false;
        while (!$unique) {
            foreach (array_rand($seed, 12) as $k) {
                $connector_code .= $seed[$k];
            }
            $connector = Connector::query();
            $connector->where('connector_code', $connector_code);
            $query = $connector->get();
            if (count($query) == 0) {
                $unique = true;
            } else {
                $connector_code = '';
            }
        }
        return $connector_code;
    }

    public static function updateAvatar($connector_id, $avatar)
    {
        $connector = Connector::find($connector_id);
        if (!empty($avatar)) {
            $connector->avatar = $avatar;
        }
        $connector->save();
        return ($connector) ? true : false;
    }

    public function saveImage($connector_id, $file)
    {
        $file_extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $file_name = $connector_id . '.' . $file_extension;
        $upload_image = FileProcess::upload('connector/' . $file_name, $file->path());
        if ($upload_image) {
            return $file_name;
        }
    }

    public static function checkExistConnector($connector_id)
    {
        $connector = Connector::find($connector_id);
        if (empty($connector)) {
            return false;
        }
        return true;
    }

    public static function getBasePath()
    {
        $path = '';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $path = 'https://' . $_SERVER['HTTP_HOST'] . '/upload/connector/';
        } else {
            $path = 'http://' . $_SERVER['HTTP_HOST'] . '/upload/connector/';
        }
        return $path;
    }
}
