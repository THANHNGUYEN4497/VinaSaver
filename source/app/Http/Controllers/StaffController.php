<?php

namespace App\Http\Controllers;

use App\Position;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Staff;
use App\Company;
use DB;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    private $success = false;
    private $data = null;
    private $error = null;
    /**
        * @SWG\Post(
        *   path="/company/login",
        *   summary="Login staff",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="email",
        *         in="formData",
        *         description="Email of staff",
        *         required=true,
        *         type="string",
        *         default="staff2@gmail.com",
        *     ),
        *     @SWG\Parameter(
        *         name="password",
        *         in="formData",
        *         description="Password of staff",
        *         required=true,
        *         type="string",
        *         default="123456789",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {
                                                            &emsp;&emsp;&emsp;   'id': ,
                                                            &emsp;&emsp;&emsp;   'api_token': ,
                                                            &emsp;&emsp;&emsp;   'username': ,
                                                            &emsp;&emsp;&emsp;   'email': ,
                                                            &emsp;&emsp;&emsp;   'privilege': ,
                                                            &emsp;&emsp;&emsp;   'company_id': ,
                                                            &emsp;&emsp;&emsp;   'company_name': ,
                                                        &emsp;&emsp;    } | null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function login(Request $request)
    {
        try {
            if (!empty($request->input('email')) && !empty($request->input('password'))) {
                if (Auth::guard('staff-web')->once(['email' => $request->input('email'), 'password' => $request->input('password')])) {
                    $info_staff = array();
                    $api_token = str_random(60);

                    $staff = Auth::guard('staff-web')->user();
                    $staff->login($api_token);
                    $info_staff['id'] = $staff->id;
                    $info_staff['api_token'] = $staff->api_token;
                    $info_staff['username'] = $staff->username;
                    $info_staff['email'] = $staff->email;
                    $info_staff['privilege'] = $staff->privilege;
                    $info_staff['company_id'] = $staff->company_id;
                    $info_staff['company_name'] = Company::getCompanyName($staff->company_id);

                    $this->success = true;
                    $this->data = $info_staff;
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
        *   path="/company/logout",
        *   summary="Logout staff",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="staff_id",
        *         in="formData",
        *         description="ID of staff",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="api_token",
        *         in="formData",
        *         description="Token of staff",
        *         required=true,
        *         type="string",
        *         default="BbI0hUMAsEWmWzNZbSiyPP7sgbpHBJly8hEzI8PjYS6jt8hGrTdrGxlcktIT",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': null | null,
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
            if (!empty($request->input('staff_id'))) {
                $info_staff = Staff::find($request->input('staff_id'));
                if ($info_staff) {
                    $info_staff->logout();
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

        return $this->doResponse($this->success, $this->data, $this->error);
    }
    /**
        * @SWG\Get(
        *   path="/company/staff/list?page_number={page_number}&page_limit={page_limit}&keyword={keyword}&phone_number={phone_number}&position={position}&api_token={api_token}",
        *   summary="List staff of a company",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="page_number",
        *         in="path",
        *         description="Page number",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="page_limit",
        *         in="path",
        *         description="Page limit",
        *         required=true,
        *         type="number",
        *         default="5",
        *     ),
        *     @SWG\Parameter(
        *         name="keyword",
        *         in="path",
        *         description="Keyword to search",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="phone_number",
        *         in="path",
        *         description="Phone number to search",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="position",
        *         in="path",
        *         description="Position to search (1 is manager, 2 is staff)",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="api_token",
        *         in="path",
        *         description="Token of staff",
        *         required=true,
        *         type="string",
        *         default="YpDzBW3KRzyl5v1toyqvtNebuqvEU6VaTqZvfC03iko4KuGW9eQOpXirl6dq",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {
                                                        &emsp;      'data': [{
                                                        &emsp;              'id': ,
                                                        &emsp;              'company_id': ,
                                                        &emsp;              'username': ,
                                                        &emsp;              'email': ,
                                                        &emsp;              'office': ,
                                                        &emsp;              'phone_number': ,
                                                        &emsp;              'privilege': ,
                                                        &emsp;              'position_name':
                                                        &emsp;              },
                                                        &emsp;              ...
                                                        &emsp;              ],
                                                        &emsp;       'total': ,
                                                        &emsp;   } | null,
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
        if (!Auth::guard('staff-api')->user()->can('listByStaff', Staff::class)) {
            $this->error = \Lang::get('common_message.error.NOT_PERMISSION');
            return $this->doResponse($this->success, $this->data, $this->error);
        }
        try {
            $company_id = null;
            if (Auth::guest()) {
                $user = Auth::guard('staff-api')->user();
                $company_id = $user->company_id;
            }
            $page_limit = $request->input('page_limit');
            $page_number = $request->input('page_number');
            $keyword = $request->input('keyword');
            $phone_number = $request->input('phone_number');
            $position = $request->input('position');
            if (empty($company_id) || empty($page_limit) || empty($page_number)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Staff::getStaffOfCompany($company_id, $page_limit, $page_number, $keyword, $phone_number, $position);
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
        *   path="/company/staff/detail/{staff_id}?api_token={api_token}",
        *   summary="Detail Staff",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="staff_id",
        *         in="path",
        *         description="Staff id",
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *          name="api_token",
        *          in="path",
        *          description="API token",
        *          type="string",
        *          default="YpDzBW3KRzyl5v1toyqvtNebuqvEU6VaTqZvfC03iko4KuGW9eQOpXirl6dq",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {
                                                        &emsp;          'id': ,
                                                        &emsp;          'company_id': ,
                                                        &emsp;          'login_id': ,
                                                        &emsp;          'username': ,
                                                        &emsp;          'email': ',
                                                        &emsp;          'phone_number': ,
                                                        &emsp;          'privilege': ,
                                                        &emsp;          'office':
                                                        &emsp;      } | null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function detail($staff_id)
    {
        try {
            if (empty($staff_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Staff::detail($staff_id);

                if ($this->data) {
                    $this->success = true;
                } else {
                    $this->error = \Lang::get('common_message.error.OBJECT_NOT_EXIST');
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
        *   path="/company/staff/add",
        *   summary="Add staff",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="email",
        *         in="formData",
        *         description="Email of staff",
        *         required=true,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="password",
        *         in="formData",
        *         description="Password of staff",
        *         required=true,
        *         type="string",
        *         default="123456789",
        *     ),
        *     @SWG\Parameter(
        *         name="username",
        *         in="formData",
        *         description="Username of staff",
        *         required=true,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="position",
        *         in="formData",
        *         description="Position of staff",
        *         required=true,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="office",
        *         in="formData",
        *         description="Office of staff",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="phone_number",
        *         in="formData",
        *         description="Phone number of staff",
        *         required=false,
        *         type="number",
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
                                                        &emsp;  'data':  10 | null,
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
        $staff = Auth::guard('staff-api')->user();
        if (!$staff->can('create', Staff::class)) {
            $this->error = \Lang::get('common_message.error.NOT_PERMISSION');
            return $this->doResponse($this->success, $this->data, $this->error);
        }

        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required',
                'username' => 'required',
                'position' => 'required'
            ], [
                'required' => 'error_required',
                'numeric' => 'error_numeric'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                $error_required = 0;
                $error_numeric = 0;
                foreach ($errors->all() as $message) {
                    if ($message == 'error_required') {
                        $error_required = 1;
                    }
                    if ($message == 'error_numeric') {
                        $error_numeric = 1;
                    }
                }
                if ($error_required) {
                    $this->error = \Lang::get('common_message.error.MISS_PARAM');
                }
                if ($error_numeric) {
                    $this->error = \Lang::get('common_message.error.TYPE_INCORRECT');
                }
                if ($error_required && $error_numeric) {
                    $this->error = \Lang::get('common_message.error.MISS_PARAM') . " & " . \Lang::get('common_message.error.TYPE_INCORRECT');
                };
            } else {
                $email = $request->input('email');
                $check = Staff::checkEmail($email);
                if ($check) {
                    $company_id = null;
                    if (Auth::guest()) {
                        $user = Auth::guard('staff-api')->user();
                        $company_id = $user->id;
                    }
                    $password = $request->input('password');
                    $username = $request->input('username');
                    $phone_number = $request->input('phone_number');
                    $office = $request->input('office');
                    $position = $request->input('position');
                    $this->data = Staff::add($company_id, $email, $password, $username, $phone_number, $office, $position);
                    $this->success = true;
                } else {
                    $this->error = \Lang::get('common_message.error.EMAIL_EXIST');
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
        *   path="/company/staff/edit/{id}",
        *   summary="Edit staff",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="id",
        *         in="path",
        *         description="ID of staff",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="password",
        *         in="formData",
        *         description="Password of staff",
        *         required=false,
        *         type="string",
        *         default="123456789",
        *     ),
        *     @SWG\Parameter(
        *         name="email",
        *         in="formData",
        *         description="Email of staff",
        *         required=true,
        *         type="string",
        *         default="staff10@gmail.com",
        *     ),
        *     @SWG\Parameter(
        *         name="username",
        *         in="formData",
        *         description="Username of staff",
        *         required=true,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="position",
        *         in="formData",
        *         description="Position of staff",
        *         required=true,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="office",
        *         in="formData",
        *         description="Office of staff",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="phone_number",
        *         in="formData",
        *         description="Phone number of staff",
        *         required=false,
        *         type="number",
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
                                                        &emsp;  'data': 10 | null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function edit(Request $request, $staff_id)
    {
        if (!(Auth::guard('staff-api')->user())->can('update', Staff::class)) {
            $this->error = \Lang::get('common_message.error.NOT_PERMISSION');
            return $this->doResponse($this->success, $this->data, $this->error);
        }
        try {
            $username = $request->input('username');
            $email = $request->input('email');
            $position = $request->input('position');
            if (empty($staff_id) || empty($email) || empty($username) || empty($position)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $check = Staff::checkStaff($staff_id);
                if ($check) {
                    $check_mail = Staff::checkEmail($email);
                    $check_mail_2 = Staff::checkEmailUpdate($staff_id, $email);
                    if ($check_mail || $check_mail_2) {
                        $password = $request->input('password');
                        $phone_number = $request->input('phone_number');
                        $office = $request->input('office');
                        $this->data = Staff::edit($staff_id, $email, $password, $username, $phone_number, $office, $position);
                        $this->success = true;
                    } else {
                        $this->error = \Lang::get('common_message.error.EMAIL_EXIST');
                    }
                } else {
                    $this->error = \Lang::get('common_message.error.OBJECT_NOT_EXIST');
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
     * @SWG\Get(
     *   path="/admin/staff/detail/{id}?api_token={api_token}",
     *   summary="Staff Get",
     *    tags={"Admin"},
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="API token",
     *         required=true,
     *         type="string",
     *         default="lypqyENjHEhGZ2Grt45HNiOCyb9vunjzfZYtuxV3r2RZaPFc05X2B4MbEwpv",
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Company ID",
     *         required=true,
     *         type="number",
     *         default=""
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
    public function show(Request $request)
    {
        $id = $request->id;
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:staffs|numeric',
        ]);
        if ($validator->fails()) {
            $this->error = $validator->errors();
            $this->success = false;
        } else {
            $staff = Staff::find($id);
            $this->data = $staff;
            $this->success = true;
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Get(
     *   path="/admin/staff/list?api_token={api_token}&keyword={keyword}&phone_number={phone_number}",
     *   summary="Staff Search",
     *     tags={"Admin"},
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="API token",
     *         required=true,
     *         type="string",
     *         default="lypqyENjHEhGZ2Grt45HNiOCyb9vunjzfZYtuxV3r2RZaPFc05X2B4MbEwpv",
     *     ),
     *     @SWG\Parameter(
     *         name="keyword",
     *         in="path",
     *         description="Keyword",
     *         type="string",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="phone_number",
     *         in="path",
     *         description="Phone number",
     *         type="number",
     *         default=""
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
     *   @SWG\Response( response=404, description="Building not found"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' =>  'required|numeric|exists:companies,id',
            'phone_number' =>  'numeric|nullable|max:999999999999999',
            'keyword'   =>  'string|nullable|max:255',
            'per_page'  =>  'numeric|nullable|max:50',
            'position' => 'nullable|max:255|numeric'
        ]);
        if ($validator->fails()) {
            $this->error = $validator->errors();
            $this->success = false;
        } else {
            $staff = DB::table('staffs');
            $staff->select('id', 'username', 'phone_number', 'privilege');
            if ($request->has('keyword') && $request->keyword != "") {
                $staff->where(function ($query) use ($request) {
                    return $query->where('email', 'like', "%$request->keyword%")
                        ->orWhere('username', 'like', "%$request->keyword%");
//                    ->orWhere('login_id', "like", "%$request->keyword%");
                });
            }
            if ($request->has('phone_number') && $request->phone_number != "") {
                $staff->orWhere('phone_number', 'like', "%$request->phone_number%");
            }
            if ($request->has('position') && $request->position != 0) {
                $staff->orWhere('privilege', $request->position);
            }
            $this->success = true;
            $this->data = $staff->where('company_id', $request->company_id)->paginate(($request->has('per_page') ? $request->per_page : 15));
        }

        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
        * @SWG\Post(
        *   path="/company/staff/delete/{id}",
        *   summary="Delete staff",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="id",
        *         in="path",
        *         description="ID of staff",
        *         required=true,
        *         type="number",
        *         default="2",
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
                                                        &emsp;  'data': null | null,
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
            if (!Auth::guard('staff-api')->user()->can('delete', Staff::class)) {
                $this->error = \Lang::get('common_message.error.NOT_PERMISSION');
                return $this->doResponse($this->success, $this->data, $this->error);
            }

            $check = Staff::checkStaff($id);
            if ($check) {
                $remove = Staff::remove($id);
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

        return $this->doResponse($this->success, null, $this->error);
    }

    public function getPositions()
    {
        $this->success = true;
        $this->data = Position::select('id', 'position_name')->get();
        return $this->doResponse($this->success, $this->data, $this->error);
        ;
    }
}
