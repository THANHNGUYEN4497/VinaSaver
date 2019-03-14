<?php
namespace App\Http\Controllers;

use App\Area;
use App\Category;
use App\Connector;
use App\IntroductionStatus;
use App\JobCategory;
use App\JobType;
use App\WorkConnection;
use App\Payment;
use App\Http\Support\FileProcess;
use DemeterChain\C;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ConnectorController extends Controller
{
    private $success = false;
    private $data = null;
    private $error = null;

    /**
     * @SWG\Post(
     *   path="/connector/login",
     *   tags={"App"},
     *   summary="Login Connector",
     *     @SWG\Parameter(
     *         name="phone_number",
     *         in="formData",
     *         description="Phone Number",
     *         required=true,
     *         type="number",
     *         default="0906503546",
     *     ),
     *     @SWG\Parameter(
     *          name="password",
     *          in="formData",
     *          required=true,
     *          description="Password",
     *          type="string",
     *          default="123456789",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data': {
    &emsp;            'id': 1,
    &emsp;            'api_token': 'eFAQOXsVkudj74VBaC0prLrbqBXP7U6TS3KwcJhQYb4SN7oxloWhgL0lMO2E',
    &emsp;            'username': null,
    &emsp;            'email': null,
    &emsp;            'phone_number': 906503546
    &emsp;          } | null,
    &emsp;  'error': null | ...</pre>}
    "),
     *   @SWG\Response( response=404, description="404 page"),
     *   )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            if (!empty($request->input('phone_number')) && !empty($request->input('password'))) {
                if (Auth::guard('connector-web')->once(['phone_number' => $request->input('phone_number'), 'password' => $request->input('password'), 'available_status' => 1])) {
                    $info_connector = array();
                    $api_token = str_random(60);

                    $connector = Auth::guard('connector-web')->user();
                    $connector->login($api_token);
                    $info_connector['id'] = $connector->id;
                    $info_connector['api_token'] = $connector->api_token;
                    $info_connector['username'] = $connector->username;
                    $info_connector['username_phonetic'] = $connector->username_phonetic;
                    $info_connector['gender'] = $connector->gender;
                    $info_connector['current_work'] = $connector->current_work;
                    $info_connector['current_work_place'] = $connector->current_work_place;
                    $info_connector['email'] = $connector->email;
                    $info_connector['phone_number'] = $connector->phone_number;
                    $info_connector['address'] = $connector->address;
                    $info_connector['avatar'] = $connector->avatar;
                    $info_connector['connector_code'] = $connector->connector_code;

                    $this->success = true;
                    $this->data = $info_connector;
                } else {
                    $this->error = \Lang::get('common_message.error.LOGIN_FAIL');
                }
            } else {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $this->error = $ex->getMessage();
        } catch (\Illuminate\Exception $ex) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $this->error = $ex->getMessage();
        }

        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Post(
     *   path="/connector/logout",
     *   summary="Logout Connector",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="connector_id",
     *         in="formData",
     *         description="Id of Connector",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *          name="api_token",
     *          in="formData",
     *          required=true,
     *          description="API token",
     *          type="string",
     *          default="eFAQOXsVkudj74VBaC0prLrbqBXP7U6TS3KwcJhQYb4SN7oxloWhgL0lMO2E",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data': null,
    &emsp;  'error': null | ...</pre>}
    "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        try {
            if (!empty($request->input('connector_id'))) {
                $info_connector = Connector::find($request->input('connector_id'));
                if ($info_connector) {
                    $info_connector->logout();
                    Auth::logout();
                    $this->success = true;
                } else {
                    $this->error = \Lang::get('common_message.error.LOGOUT_FAIL');
                }
            } else {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $this->error = $ex->getMessage();
        } catch (\Illuminate\Exception $ex) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $this->error = $ex->getMessage();
        }

        return $this->doResponse($this->success, null, $this->error);
    }

    /**
     * @SWG\Post(
     *   path="/connector/register",
     *   summary="Add Connector",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="phone_number",
     *         in="formData",
     *         description="Phone number of Connector",
     *         required=true,
     *         type="number",
     *         default="0906503546",
     *     ),
     *     @SWG\Parameter(
     *          name="password",
     *          in="formData",
     *          required=true,
     *          description="Password",
     *          type="string",
     *          default="123456789",
     *     ),
     *     @SWG\Parameter(
     *          name="username",
     *          in="formData",
     *          required=false,
     *          description="Username",
     *          type="string",
     *          default="",
     *     ),
     *     @SWG\Parameter(
     *          name="gender",
     *          in="formData",
     *          required=false,
     *          description="Gender",
     *          type="string",
     *          default="",
     *     ),
     *     @SWG\Parameter(
     *          name="address",
     *          in="formData",
     *          required=false,
     *          description="Address",
     *          type="string",
     *          default="",
     *     ),
     *     @SWG\Parameter(
     *          name="introduction_code",
     *          in="formData",
     *          required=false,
     *          description="Zip code of the referrer",
     *          type="string",
     *          default="",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data': 1 | null,
    &emsp;  'error': null | ...</pre>}
    "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        try {
            $phone_number = $request->input('phone_number');
            $password = $request->input('password');
            $username = $request->input('username');
            $gender = $request->input('gender');
            $address = $request->input('address');
            $introduction_code = $request->input('introduction_code');
            if (empty($phone_number) || empty($password)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $exist_connector_id = Connector::getIdByPhoneNumber($phone_number);
                if (empty($exist_connector_id)) {
                    $connector_id = Connector::add($phone_number, $password, $username, $gender, $address);
                    $this->data = $connector_id;
                    $this->success = true;
                    if (!empty($this->data)) {
                        $name_of_friend = Connector::getNameById($this->data);
                        if (!empty($introduction_code)) {
                            $introduction_id = Connector::get_id_by_code($introduction_code);
                            if (!empty($introduction_id)) {
                                $content = $name_of_friend . 'があなたのコードでアカウントを登録しました。';
                                // IntroductionStatus::add(null, $this->data, null, $introduction_id, 1);
                                IntroductionStatus::add($this->data, $introduction_id, null, 1, 100, $content, 0);
                            }
                        } else {
                            IntroductionStatus::add(null, $this->data, null, 1, 100, $name_of_friend . 'アカウントを登録しました。', 1);
                        }
                        Payment::add($this->data, null, null, 1, $name_of_friend . 'アカウントを登録しました。', 100, 0);
                    }
                } else {
                    $this->error = \Lang::get('common_message.error.ACCOUNT_ALREADY_EXISTS');
                }
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $this->error = $ex->getMessage();
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
        } catch (\Illuminate\Exception $ex) {
            $this->error = $ex->getMessage();
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Get(
     *   path="/admin/connector/list?page_limit={page_limit}&page_number={page_number}&keyword={keyword}&phone_number={phone_number}&api_token={api_token}",
     *   summary="Get list of connectors",
     *    tags={"Admin"},
     *     @SWG\Parameter(
     *         name="page_limit",
     *         in="path",
     *         description="page_limit",
     *         required=true,
     *         type="number",
     *         default="10",
     *     ),
     *     @SWG\Parameter(
     *         name="page_number",
     *         in="path",
     *         description="page_number",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="Api token of Admin",
     *         required=true,
     *         type="string",
     *         default="",
     *     ),
     *     @SWG\Parameter(
     *         name="keyword",
     *         in="path",
     *         description="keyword",
     *         required=false,
     *         type="string",
     *         default="",
     *     ),
     *     @SWG\Parameter(
     *         name="phone_number",
     *         in="path",
     *         description="phone_number",
     *         required=false,
     *         type="string",
     *         default="",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data':{
    &emsp;&emsp;    'total_items': 7,
    &emsp;&emsp;        'data': [{
    &emsp;&emsp;&emsp;        'id': 22,
    &emsp;&emsp;&emsp;        'username': null,
    &emsp;&emsp;&emsp;        'email': null,
    &emsp;&emsp;&emsp;        'password': '$2y$10$VcqJLaI7kROdejJGXKZAveEXbemWXVMD0o5IDasflfINwA6VWTCW.',
    &emsp;&emsp;&emsp;        'phone_number': '0906503544',
    &emsp;&emsp;&emsp;        'birthday': null,
    &emsp;&emsp;&emsp;        'api_token': '0lYjBC81KyOjL8gol2mq8O0jmY41nefi0hdeZbvUwHDIWHJ2xENln0jUt75O',
    &emsp;&emsp;&emsp;        'gender': null,
    &emsp;&emsp;&emsp;        'zip_code': 'hwojguzk3tq2',
    &emsp;&emsp;&emsp;        'introduction_code': null,
    &emsp;&emsp;&emsp;        'del': 0,
    &emsp;&emsp;&emsp;        'created_at': '1544767154',
    &emsp;&emsp;&emsp;        'updated_at': '1544767849'
    &emsp;&emsp;&emsp;         },
    &emsp;&emsp;&emsp;        {},
    &emsp;&emsp;&emsp;        ...
    &emsp;&emsp;&emsp;        ]
    &emsp;&emsp;&emsp;    }| null,
    &emsp;  'error': null | ...</pre>}
    "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $page_limit = $request->input('page_limit');
            $page_number = $request->input('page_number');
            $keyword = $request->input('keyword');
            $phone_number = $request->input('phone_number');
            if (empty($page_limit) || empty($page_number)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Connector::getListConnector($page_limit, $page_number, $keyword, $phone_number);
                $this->success = true;
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $this->error = $ex->getMessage();
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
        } catch (\Illuminate\Exception $ex) {
            $this->error = $ex->getMessage();
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Get(
     *   path="/admin/connector/detail/{connector_id}?&api_token={api_token}",
     *   summary="Get info Connector",
     *    tags={"Admin"},
     *     @SWG\Parameter(
     *         name="connector_id",
     *         in="path",
     *         description="Id of Connector",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="Api token of Admin",
     *         required=true,
     *         type="string",
     *         default="N6stipDS6KkBLGq3WAEK4uUL1ZwlEdQMm6BQT11fnhUfQOsy4BAamkWUDQbD",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data': {'id': 11,
    &emsp;&emsp;&emsp;'username': 'lanh',
    &emsp;&emsp;&emsp;'email': 'lanh@saver.jp',
    &emsp;&emsp;&emsp;'password': '$2y$10$nQNt1DMu553/n/C1D9sUQuC5QEihfIDHRZgfdA/TG0d7gGrnl.0zi',
    &emsp;&emsp;&emsp;'phone_number': '0906503546',
    &emsp;&emsp;&emsp;'birthday': 1544493669,
    &emsp;&emsp;&emsp;'gender': 1,
    &emsp;&emsp;&emsp;'connector_code': '123',
    &emsp;&emsp;&emsp;'bank_type': null,
    &emsp;&emsp;&emsp;'account_type': null,
    &emsp;&emsp;&emsp;'branch_code': null,
    &emsp;&emsp;&emsp;'card_number': null,
    &emsp;&emsp;&emsp; 'first_name': null,
    &emsp;&emsp;&emsp;'last_name': null} | null,
    &emsp;  'error': null | ...</pre>}
    "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function detail($connector_id)
    {
        try {
            //$connector_id = $request->input('connector_id');
            if (empty($connector_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Connector::getDetailByAdmin($connector_id);
                $this->success = true;
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $this->error = $ex->getMessage();
        } catch (\Illuminate\Exception $ex) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $this->error = $ex->getMessage();
        }

        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Get(
     *   path="/connector/detail/{id}?api_token={api_token}",
     *   summary="Get info Connector",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id of Connector",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="Api token of Connector",
     *         required=true,
     *         type="string",
     *         default="N6stipDS6KkBLGq3WAEK4uUL1ZwlEdQMm6BQT11fnhUfQOsy4BAamkWUDQbD",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data': 1 | null,
    &emsp;  'error': null | ...</pre>}
    "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getInfo($connector_id)
    {
        try {
            if (empty($connector_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Connector::getDetailByConnector($connector_id);
                $this->success = true;
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $this->error = $ex->getMessage();
        } catch (\Illuminate\Exception $ex) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $this->error = $ex->getMessage();
        }

        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Post(
     *   path="/admin/connector/delete/{id}",
     *   summary="Delete connector",
     *    tags={"Admin"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id of Connector",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *          name="api_token",
     *          in="formData",
     *          required=true,
     *          description="API token",
     *          type="string",
     *          default="eFAQOXsVkudj74VBaC0prLrbqBXP7U6TS3KwcJhQYb4SN7oxloWhgL0lMO2E",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data': null,
    &emsp;  'error': null | ...</pre>}
    "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $connector_id = $id;
            if (empty($connector_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Connector::deleteById($connector_id);
                if ($this->data) {
                    $this->success = true;
                } else {
                    $this->error = \Lang::get('common_message.error.DELETE_FAIL');
                }
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $this->error = $ex->getMessage();
        } catch (\Illuminate\Exception $ex) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
            $this->error = $ex->getMessage();
        }

        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Post(
     *   path="/connector/verify",
     *   summary="Connector verify",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="phone_number",
     *         in="formData",
     *         description="Phone number",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="auth_number",
     *         in="formData",
     *         description="Auth number",
     *         required=true,
     *         type="string",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         in="formData",
     *         description="Type. 1=request verify for change password. 2=request verify for change password",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data': null,
    &emsp;  'error': null | ...</pre>}
    "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|max:999999999999999|exists:connectors|numeric',
            'auth_number' => 'required|numeric|min:10000|max:99999',
            'type' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            $this->error = $validator->errors();
        } else {
            if ($request->type == 1) {
                $connector = Connector::where('phone_number', $request->phone_number)
                    ->where('auth_number', $request->auth_number)
                    ->first();
                $result = Connector::changeAvailableStatus($connector->id, 1);
                if ($result == 1) {
                    $this->success = true;
                    $this->data = Connector::where('phone_number', $request->phone_number)
                        ->select('id', 'username', 'email', 'phone_number', 'birthday', 'gender', 'available_status')
                        ->first();
                } else {
                    $this->error = (object)['phone_number' => \Lang::get('common_message.error.NOT_FOUND_CONNECTOR')];
                }
            } elseif ($request->type == 2) {
                $connector = Connector::where('phone_number', $request->phone_number)
                    ->where('auth_number', $request->auth_number)
                    ->first();
                if (!empty($connector)) {
                    $this->success = true;
                    $this->data = Connector::where('phone_number', $request->phone_number)
                        ->select('id', 'username', 'email', 'phone_number', 'birthday', 'gender', 'available_status')
                        ->first();
                } else {
                    $this->error = (object)['phone_number' => \Lang::get('common_message.error.NOT_FOUND_CONNECTOR')];
                }
            } else {
                $this->error = (object)['type' => \Lang::get('common_message.error.TYPE_INVALID')];
            }
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Post(
     *   path="/connector/reset-password",
     *   summary="Connector change password",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="formData",
     *         description="ID",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="formData",
     *         description="Password",
     *         required=true,
     *         type="string",
     *         default="",
     *     ),
     *     @SWG\Parameter(
     *         name="password_confirmation",
     *         in="formData",
     *         description="Password confirmation",
     *         required=true,
     *         type="string",
     *         default="",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data': null,
    &emsp;  'error': null | ...</pre>}
    "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'password' => 'required|max:255|min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'max:255|min:6',
        ]);
        if ($validator->fails()) {
            $this->error = $validator->errors();
        } else {
            $connector = Connector::where('id', $request->id)
                ->update(['password' => bcrypt($request->password)]);
            $this->success = ($connector == 1);
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Post(
     *   path="/connector/forgot-password",
     *   summary="Connector forgot password",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="phone_number",
     *         in="formData",
     *         description="Phone number",
     *         required=true,
     *         type="number",
     *         default="0906503546",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data': null,
    &emsp;  'error': null | ...</pre>}
    "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|max:999999999999999|exists:connectors|numeric',
        ]);
        if ($validator->fails()) {
            $this->error = $validator->errors();
        } else {
            // $auth_number = rand(Connector::$minAuthNumRand, Connector::$maxAuthNumRand);
            $auth_number = 99999;
            if (Connector::where('phone_number', $request->phone_number)
                ->update(['auth_number' => $auth_number])
            ) {
                $this->success = true;

            //do something with $auth_number
            } else {
                $this->success = false;
            }
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Get(
     *   path="/connector/total-money?connector_id={connector_id}&api_token={api_token}",
     *   summary="Get total money",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="connector_id",
     *         in="path",
     *         description="Connector Id",
     *         required=true,
     *         type="number",
     *         default="10",
     *     ),
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="Api token of Connector",
     *         required=true,
     *         type="string",
     *         default="",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data':100,
    &emsp;  'error': null | ...</pre>}
    "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTotalMoneyByIntroductionId(Request $request)
    {
        $total = 0;
        try {
            $connector_id = $request->input('connector_id');
            if (empty($connector_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $introduction_statuses = IntroductionStatus::getListIntroductionStatusByIntroductionId($connector_id);
                foreach ($introduction_statuses as $introduction_status) {
                    if ($introduction_status->type == 1) {
                        $total += 100;
                    }
                    if ($introduction_status->type == 3) {
                        $total += 4500;
                    }
                }
                $this->data = $total;
                $this->success = true;
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $this->error = $ex->getMessage();
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
        } catch (\Illuminate\Exception $ex) {
            $this->error = $ex->getMessage();
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $ex->getMessage());
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Post(
     *   path="/connector/report",
     *   summary="Connector report about job after one month",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="work_connection_id",
     *         in="formData",
     *         description="WorkConnection Id",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="report",
     *         in="formData",
     *         description="report status",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *          name="api_token",
     *          in="formData",
     *          required=true,
     *          description="API token",
     *          type="string",
     *          default="U1n8N6yLQbDNhQL0Gl3y1WXHV0fp9XOudgpGcYQy14PGVfV0LMemtMegto6m",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function report(Request $request)
    {
        try {
            $work_connection_id = $request->input('work_connection_id');
            $report = $request->input('report');
            if (empty($work_connection_id) || empty($report)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = WorkConnection::edit($work_connection_id, null, null, null, $report, null, null, null);
                $this->success = true;
                $this->error = null;
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
    
    /**
        * @SWG\Post(
        *   path="/connector/update-avatar/{connector_id}",
        *   summary="Edit avatar",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="connector_id",
        *         in="path",
        *         description="Id of connector",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="avatar",
        *         in="formData",
        *         description="Avatar image",
        *         required=true,
        *         type="file",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *          name="api_token",
        *          in="formData",
        *          description="API token",
        *          required=true,
        *          type="string",
        *          default="UnJlBBetMaOEJRpTcZDS1yGnG34MD94CKD3FxDFHPkT808WYbHjAhgKMHJEn",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': true | false,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function updateAvatar(Request $request, $connector_id)
    {
        $this->data = null;
        $this->error = null;
        $this->success = false;
        $avatar_name = null;
        $avatar = $request->avatar;
        try {
            if (empty($connector_id) || empty($avatar)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $connector = Connector::checkExistConnector($connector_id);
                if ($connector) {
                    $avatar_name = $this->saveAvatar($connector_id, $avatar, 1);
                    $this->data = Connector::updateAvatar($connector_id, $avatar_name);
                    $this->success = true;
                }
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

    public function saveAvatar($connector_id, $file)
    {
        $file_extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $file_name = $connector_id . '.' . $file_extension;
        $upload_image = FileProcess::upload('connector/' . $file_name, $file->path());
        if ($upload_image) {
            return $file_name;
        }
    }

    /**
     * @SWG\Post(
     *   path="/connector/edit/{id}",
     *   summary="Connector Update",
     *     tags={"App"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID",
     *         type="number",
     *         default="5",
     *     ),
     *     @SWG\Parameter(
     *          name="api_token",
     *          in="formData",
     *          description="API Token",
     *          type="string",
     *          default=""
     *     ),
     *     @SWG\Parameter(
     *          name="username",
     *          in="formData",
     *          description="Username",
     *          type="string",
     *          default="username"
     *     ),
     *     @SWG\Parameter(
     *          name="username_phonetic",
     *          in="formData",
     *          description="Username phonetic",
     *          type="string",
     *          default="username phoenetic"
     *     ),
     *     @SWG\Parameter(
     *          name="email",
     *          in="formData",
     *          description="Email",
     *          type="string",
     *          default="usernmail@gmail.com"
     *     ),
     *     @SWG\Parameter(
     *          name="birthday",
     *          in="formData",
     *          description="Birthday",
     *          type="string",
     *          default="2018-01-20"
     *     ),
     *     @SWG\Parameter(
     *          name="gender",
     *          in="formData",
     *          description="Gender",
     *          type="number",
     *          default="1"
     *     ),
     *     @SWG\Parameter(
     *          name="address",
     *          in="formData",
     *          description="Address",
     *          type="string",
     *          default="02 Quang Trung"
     *     ),
     *     @SWG\Parameter(
     *          name="current_work",
     *          in="formData",
     *          description="Current Work",
     *          type="string",
     *          default=" C Work"
     *     ),
     *     @SWG\Parameter(
     *          name="current_work_place",
     *          in="formData",
     *          description="Current Work Place",
     *          type="string",
     *          default=" C Work Place"
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                    &emsp;  'data': 1 | null,
                                                    &emsp;  'error': null | ...</pre>}
                                                    "),
     *   @SWG\Response( response=404, description="Building not found"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->request->add(['id' => $id]);
        $validator = Validator::make($request->all(), [
            'username' => 'max:255|nullable',
            'username_phonetic' => 'max:255|nullable',
            'address' => 'max:255|nullable',
            'email' => 'required|max:255|unique:connectors,email,' . $id . '|email|nullable',
            'birthday' => 'date|nullable|before:tomorrow',
            'gender' => 'numeric|nullable',
            'current_work' => 'max:255|nullable',
            'current_work_place' => 'max:255|nullable',
            'id' => 'required|numeric|exists:connectors'
        ]);

        if ($validator->fails()) {
            $this->error = $validator->errors();
        } else {
            try {
                Connector::where('id', $request->id)->first()->update([
                    'username' => $request->username,
                    'username_phonetic' => $request->username_phonetic,
                    'email' => $request->email,
                    'birthday' => (!empty($request->birthday) ? (new \DateTime($request->birthday))->format('U') : null),
                    'gender' => $request->gender,
                    'address' => $request->address,
                    'current_work' => $request->current_work,
                    'current_work_place' => $request->current_work_place,
                ]);
                $this->data = $request->id;
                $this->success = true;
            } catch (QueryException $exception) {
                \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $exception->getMessage());
                $this->error = $exception->getMessage();
            } catch (\Exception $exception) {
                \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $exception->getMessage());
                $this->error = $exception->getMessage();
            }
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Get(
     *   path="/connector/getAreas?api_token={api_token}",
     *   summary="Get all of Areas",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="Api token of Connector",
     *         required=true,
     *         type="string",
     *         default="",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  [
                                                        &emsp;    {
                                                        &emsp;      'id': ,
                                                        &emsp;      'area_name':
                                                        &emsp;    },
                                                        &emsp;    ...
                                                        &emsp;  ]
                                                        "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAreas()
    {
        return Area::select('id', 'area_name')->get();
    }

    /**
     * @SWG\Get(
     *   path="/connector/getJobCategories?api_token={api_token}",
     *   summary="Get all of Job Categories",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="Api token of Connector",
     *         required=true,
     *         type="string",
     *         default="",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  [
                                                        &emsp;    {
                                                        &emsp;      'id': ,
                                                        &emsp;      'job_category_name':
                                                        &emsp;    },
                                                        &emsp;    ...
                                                        &emsp;  ]
                                                        "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getJobCategories()
    {
        return JobCategory::select('id', 'job_category_name')->get();
    }

    /**
     * @SWG\Get(
     *   path="/connector/getCategories?api_token={api_token}",
     *   summary="Get all of Categories",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="Api token of Connector",
     *         required=true,
     *         type="string",
     *         default="",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  [
                                                        &emsp;    {
                                                        &emsp;      'id': ,
                                                        &emsp;      'category_name':
                                                        &emsp;    },
                                                        &emsp;    ...
                                                        &emsp;  ]
                                                        "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCategories()
    {
        return Category::select('id', 'category_name')->get();
    }

    /**
     * @SWG\Get(
     *   path="/connector/getJobTypes?api_token={api_token}",
     *   summary="Get all of Types",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="Api token of Connector",
     *         required=true,
     *         type="string",
     *         default="",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  [
                                                        &emsp;    {
                                                        &emsp;      'id': ,
                                                        &emsp;      'type_name':
                                                        &emsp;    },
                                                        &emsp;    ...
                                                        &emsp;  ]
                                                        "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getJobTypes()
    {
        return JobType::select('id', 'type_name')->get();
    }
}
