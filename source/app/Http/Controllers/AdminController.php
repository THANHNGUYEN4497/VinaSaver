<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Admin;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    private $success = false;
    private $data = null;
    private $error = null;
    /**
     * @SWG\Post(
     *   path="/admin/login",
     *   tags={"Admin"},
     *   summary="Admin Login",
     *     @SWG\Parameter(
     *         name="email",
     *         in="formData",
     *         description="email",
     *         required=true,
     *         type="string",
     *         default="lanh.nguyen1@saver.jp",
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
    &emsp;            'email': null
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
            if (!empty($request->input('email')) && !empty($request->input('password'))) {
                if (Auth::guard('admin-web')->once(['email' => $request->input('email'), 'password' => $request->input('password')])) {
                    $info_admin = array();
                    $api_token = str_random(60);

                    $admin = Auth::guard('admin-web')->user();
                    $admin->api_token = $api_token;
                    $admin->save();
                    $info_admin['id'] = $admin->id;
                    $info_admin['api_token'] = $admin->api_token;
                    $info_admin['username'] = $admin->username;
                    $info_admin['email'] = $admin->email;

                    $this->success = true;
                    $this->data = $info_admin;
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
     *   path="/admin/logout",
     *   summary="Logout Admin",
     *    tags={"Admin"},
     *     @SWG\Parameter(
     *         name="admin_id",
     *         in="formData",
     *         description="Id of Admin",
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
            if (!empty($request->input('admin_id'))) {
                $info_admin = Admin::find($request->input('admin_id'));
                if ($info_admin) {
                    $info_admin->api_token = null;
                    $info_admin->save();
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
     * @SWG\Post(
     *   path="/admin/add",
     *   summary="Admin Add",
     *     tags={"Admin"},
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="formData",
     *         description="API token",
     *         required=true,
     *         type="string",
     *         default="lypqyENjHEhGZ2Grt45HNiOCyb9vunjzfZYtuxV3r2RZaPFc05X2B4MbEwpv",
     *     ),
     *     @SWG\Parameter(
     *         name="username",
     *         in="formData",
     *         description="Username",
     *         required=true,
     *         type="string",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *          name="password",
     *          in="formData",
     *          required=true,
     *          description="Password",
     *          type="string",
     *          default=""
     *     ),
     *     @SWG\Parameter(
     *          name="password_confirmation",
     *          in="formData",
     *          required=true,
     *          description="Password Confirmation",
     *          type="string",
     *          default=""
     *     ),
     *     @SWG\Parameter(
     *          name="phone_number",
     *          in="formData",
     *          description="Phone number",
     *          type="number",
     *          default=""
     *     ),
     *     @SWG\Parameter(
     *          name="email",
     *          in="formData",
     *          description="Email",
     *          type="string",
     *          default=""
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|max:255|unique:admins',
            'email' => 'required|max:255|unique:admins|email|nullable',
            'phone_number' => 'numeric|max:999999999999999',
            'password' => 'required|max:255|min:6',
//            'password_confirmation' => 'max:255|min:6',
        ]);
        if ($validator->fails()) {
            $this->error = $validator->errors();
            $this->success = false;
        } else {
            $admin = Admin::updateOrCreate([
                'password' => bcrypt($request->password),
                'email' => $request->email,
                'username' => $request->username,
                'phone_number' => $request->phone_number
            ]);
            $this->data = $admin->id;
            $this->success = true;
            Mail::to($admin->email)->send(new \App\Http\Support\Mail\AccountCreated($admin->email, $request->password));
        }

        return $this->doResponse($this->success, $this->data, $this->error);
    }


    /**
     * @SWG\Get(
     *   path="/admin/detail/{id}?api_token={api_token}",
     *   summary="Admin Get",
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
     *          default=""
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data': {
    &emsp;            'username': 'admin1231212',
    &emsp;            'email': 'admin@gmail.com',
    &emsp;            'phone_number': 1321212,
    &emsp;            'created_at': '1546823398',
    &emsp;            'updated_at': '1548920526'
    &emsp;          } | null,
    &emsp;  'error': null | ...</pre>}
    "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:admins|numeric',
        ]);
        if ($validator->fails()) {
            $this->error = $validator->errors();
            $this->success = false;
        } else {
            $admin = Admin::where('id', $id)->select('username', 'email', 'phone_number', 'created_at', 'updated_at')->first();
            $this->data = $admin;
            $this->success = true;
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }


    /**
     * @SWG\Post(
     *   path="/admin/edit/{id}",
     *   summary="Admin Edit",
     *     tags={"Admin"},
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="formData",
     *         description="API token",
     *         required=true,
     *         type="string",
     *         default="lypqyENjHEhGZ2Grt45HNiOCyb9vunjzfZYtuxV3r2RZaPFc05X2B4MbEwpv",
     *     ),
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID",
     *         required=true,
     *         type="string",
     *         default="3",
     *     ),
     *     @SWG\Parameter(
     *         name="username",
     *         in="formData",
     *         description="Username",
     *         required=true,
     *         type="string",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *          name="password",
     *          in="formData",
     *          description="Password",
     *          type="string",
     *          default=""
     *     ),
     *     @SWG\Parameter(
     *          name="password_confirmation",
     *          in="formData",
     *          description="Password Confirmation",
     *          type="string",
     *          default=""
     *     ),
     *     @SWG\Parameter(
     *          name="phone_number",
     *          in="formData",
     *          description="Phone number",
     *          type="number",
     *          default=""
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data': '5',
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
            'username' => 'max:255',
            'phone_number' => 'numeric|max:999999999999999',
            'password' => 'max:255|min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'max:255|min:6',
            'id' => 'required|numeric|exists:admins'
        ]);
        if ($validator->fails()) {
            $this->error = $validator->errors();
        } else {
            $data = [
                'username' => $request->username,
                'phone_number' => $request->phone_number
            ];
            if ($request->has('password') && $request->password != "") {
                $data['password'] = $request->password;
            }
            $admin = Admin::where('id', $id)->update($data);
            $this->data = $id;
            $this->success = true;
        }

        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Get(
     *   path="/admin/list?api_token={api_token}&keyword={keyword}",
     *   summary="Admin Search",
     *     tags={"Admin"},
     *     @SWG\Parameter(
     *         name="keyword",
     *         in="path",
     *         description="Key word",
     *         type="string",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="API token",
     *         required=true,
     *         type="string",
     *         default="lypqyENjHEhGZ2Grt45HNiOCyb9vunjzfZYtuxV3r2RZaPFc05X2B4MbEwpv",
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
            'keyword' => 'string|nullable|max:255',
            'per_page'  =>  'numeric|nullable|max:50',

        ]);
        if ($validator->fails()) {
            $this->error = $validator->errors();
            $this->success = false;
        } else {
            $admin = DB::table('admins');
            if ($request->has('keyword') && $request->keyword != "" && $request->keyword != "undefined") {
                $admin->where(function ($query) use ($request) {
                    return $query->where('phone_number', 'like', "%$request->keyword%")
                        ->orWhere('email', 'like', "%$request->keyword%")
                        ->orWhere('username', "like", "%$request->keyword%");
                });
            }
            $this->success = true;
            $this->data = $admin->select(['id', 'username', 'email', 'created_at', 'updated_at', 'phone_number'])->paginate(($request->has('per_page') ? $request->per_page : 15));
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Post(
     *   path="/admin/delete/{id}?api_token={api_token}",
     *   summary="Admin Delete",
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
     *         name="id",
     *         in="path",
     *         description="ID",
     *         required=true,
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
    public function destroy($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:admins',
        ]);
        if ($validator->fails()) {
            $this->error = $validator->errors();
            $this->success = false;
        } else {
            Admin::destroy($id);
            $this->data = $id;
            $this->success = true;
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }
}
