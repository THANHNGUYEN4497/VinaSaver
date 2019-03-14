<?php

namespace App\Http\Controllers;

use App\BusinessField;
use App\Company;
use App\CompanyFile;
use App\WorkConnection;
use App\Staff;
use App\Payment;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use App\Http\Support\FileProcess;

use App\Jobs\SQSJob;

use Log;

class CompanyController extends Controller
{
    private $success = false;
    private $data = null;
    private $error = null;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * @SWG\Post(
     *   path="/admin/company/add",
     *   summary="Company Add",
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
     *         name="admin_id",
     *         in="formData",
     *         description="Admin ID",
     *         required=true,
     *         type="string",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *          name="address",
     *          in="formData",
     *          required=true,
     *          description="Address",
     *          type="string",
     *          default=""
     *     ),
     *     @SWG\Parameter(
     *          name="company_name",
     *          in="formData",
     *          required=true,
     *          description="Company name",
     *          type="string",
     *          default=""
     *     ),
     *     @SWG\Parameter(
     *          name="phone_number",
     *          in="formData",
     *          required=true,
     *          description="Phone number",
     *          type="number",
     *          default=""
     *     ),
     *     @SWG\Parameter(
     *          name="email",
     *          in="formData",
     *          required=true,
     *          description="Email",
     *          type="string",
     *          default=""
     *     ),
     *     @SWG\Parameter(
     *          name="email_staff",
     *          in="formData",
     *          required=true,
     *          description="Email staff",
     *          type="string",
     *          default=""
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
     *          description="Confirm Password",
     *          type="string",
     *          default=""
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
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'admin_id' => 'required',
                'company_name' => 'required|max:255',
                'address' => 'required|max:255',
                'phone_number' => 'numeric|required',
                'email' => 'required|max:255|unique:companies|email',
                'url' => 'max:255',
                'agency_name' => 'max:255',
                'business_field' => 'max:255',
                'latitude' => 'numeric',
                'longitude' => 'numeric',
                'password' => 'required|max:255|min:6|required_with:password_confirmation|same:password_confirmation',
                'password_confirmation' => 'max:255|min:6',
                'business_field' => 'nullable|max:255|numeric',
                'username_staff' => 'required|max:255'
            ]);
            $staff_validator = Validator::make([
                'email' => $request->email_staff
            ], [
                'email' => 'required|max:255|unique:staffs|email',
            ]);
            if ($validator->fails()) {
                $this->error = $validator->errors();
                $this->success = false;
            } elseif ($staff_validator->fails()) {
                $errors = $staff_validator->errors();
                if ($errors->has('email')) {
                    $email_messages = $errors->get('email');
                    $raw_messages = $errors->getMessages();
                    unset($raw_messages['email']);
                    $raw_messages['email_staff'] = $email_messages;
                    $errors = new MessageBag($raw_messages);
                }
                $this->error = $errors;
                $this->success = false;
            } else {
                $company_id = Company::store($request->except(['login_id', 'password', 'password_confirmation', '_token', 'api_token', 'email_staff', 'username_staff']));
                Staff::store([
                    'email' => $request->email_staff,
                    'password' => bcrypt($request->password),
                    'company_id' => $company_id,
                    'privilege' => 1,
                    'username' => $request->username_staff
                ]);
                $this->data = $company_id;
                $this->success = true;
            }
        } catch (QueryException $exception) {
            $this->success = false;
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $exception->getMessage());
            $this->error = $exception->getMessage();
        } catch (\Exception $exception) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $exception->getMessage());
            $this->error = $exception->getMessage();
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Get(
     *   path="/admin/company/detail/{id}?api_token={api_token}",
     *   summary="Company Get",
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
    public function show($id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|exists:companies|numeric',
            ]);
            if ($validator->fails()) {
                $this->error = $validator->errors();
                $this->success = false;
            } else {
                $company = Company::getDetailCompany($id);
                $this->data = $company;
                if ($this->data) {
                    $this->data->images = CompanyFile::getCompanyFile($id, 1);
                    $this->data->videos = CompanyFile::getCompanyFile($id, 2);
                }
                $this->success = true;
            }
        } catch (QueryException $exception) {
            $this->success = false;
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $exception->getMessage());
            $this->error = $exception->getMessage();
        } catch (\Exception $exception) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $exception->getMessage());
            $this->error = $exception->getMessage();
        }

        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * @SWG\Post(
     *   path="/admin/company/edit/{id}",
     *   summary="Company Update",
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
     *         default="8",
     *     ),
     *     @SWG\Parameter(
     *          name="address",
     *          in="formData",
     *          required=true,
     *          description="Address",
     *          type="string",
     *          default=""
     *     ),
     *     @SWG\Parameter(
     *          name="company_name",
     *          in="formData",
     *          required=true,
     *          description="Company name",
     *          type="string",
     *          default=""
     *     ),
     *     @SWG\Parameter(
     *          name="phone_number",
     *          in="formData",
     *          required=true,
     *          description="Phone number",
     *          type="number",
     *          default=""
     *     ),
     *     @SWG\Parameter(
     *          name="url",
     *          in="formData",
     *          required=true,
     *          description="URL",
     *          type="string",
     *          default=""
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
    public function update(Request $request, $id)
    {
        try {
            $request->request->add(['id' => $id]);
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:companies',
                'company_name' => 'required|max:255',
                'address' => 'required|max:255',
                'phone_number' => 'numeric|required',
                'url' => 'max:255',
                'business_field' => 'required|max:255|numeric'
            ]);
            if ($validator->fails()) {
                $this->error = $validator->errors();
                $this->success = false;
            } else {
                $company = Company::where('id', $request->id)->first()->update([
                    'company_name' => $request->company_name,
                    'address' => $request->address,
                    'phone_number' => $request->phone_number,
                    'url' => $request->url,
                    'business_field' => $request->business_field
                ]);
                $this->data = $request->id;
                $this->success = true;
            }
        } catch (QueryException $exception) {
            $this->success = false;
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $exception->getMessage());
            $this->error = $exception->getMessage();
        } catch (\Exception $exception) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $exception->getMessage());
            $this->error = $exception->getMessage();
        }


        return $this->doResponse($this->success, $this->data, $this->error);
    }



    /**
     * @SWG\Post(
     *   path="/admin/company/delete/{id}?api_token={api_token}",
     *   summary="Company Delete",
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
     *         default="2"
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
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|exists:companies',
            ]);
            if ($validator->fails()) {
                $this->error = $validator->errors();
                $this->success = false;
            } else {
                Company::deleteById($id);
                Staff::deleteByCompanyId($id);
                $files = CompanyFile::deleteFileCompany($id);
                if (!empty($files)) {
                    foreach ($files as $file) {
                        dispatch((new SQSJob([
                            'key' => $file->path,
                            'processType' => 'delete',
                        ])))
                        ->onConnection('file_uploaded_queue');
                    }
                }
                //NOTE: Job/JobFile remain

                $this->success = true;
                $this->data = $id;
            }
        } catch (QueryException $exception) {
            $this->success = false;
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $exception->getMessage());
            $this->error = $exception->getMessage();
        } catch (\Exception $exception) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $exception->getMessage());
            $this->error = $exception->getMessage();
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Get(
     *   path="/admin/company/list?api_token={api_token}&date_begin={date_begin}&date_end={date_end}&keyword={keyword}",
     *   summary="Company Search",
     *     tags={"Admin"},
     *     @SWG\Parameter(
     *         name="date_begin",
     *         in="path",
     *         description="Date begin( YYYY-mm-dd )",
     *         type="string",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="date_end",
     *         in="path",
     *         description="Date end( YYYY-mm-dd )",
     *         default="",
     *         type="string",
     *
     *     ),
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
        try {
            $validator = Validator::make($request->all(), [
                'date_begin' =>  'date|nullable|before:tomorrow',
                'date_end'  =>  'date|nullable',
                'keyword'   =>  'string|nullable|max:255',
                'per_page'  =>  'numeric|nullable|max:50',
                'business_field' => 'nullable|max:255|numeric'
            ]);
            if ($validator->fails() || ($request->has('date_end') && $request->has('date_begin') && ((new \DateTime($request->date_begin))->format('U') > (new \DateTime($request->date_end))->format('U')))) {
                $this->error = $validator->errors();
                $this->success = false;
            } else {
                $companies = DB::table('companies');
                if (($request->has('date_end') && $request->date_end != "" && $request->date_end != "undefined") && ($request->has('date_begin') && $request->date_begin != "" && $request->date_begin != "undefined")) {
                    $companies->where(function ($query) use ($request) {
                        return $query->where('created_at', '<', (new \DateTime($request->date_end))->modify('+1 day')->format('U'))
                                    ->where('created_at', '>=', (((new \DateTime($request->date_begin)))->format('U')));
                    });
                }
                if ($request->has('keyword') && $request->keyword != "" && $request->keyword != "undefined") {
                    $companies->where(function ($query) use ($request) {
                        return $query->where('phone_number', 'like', "%$request->keyword%")
                            ->orWhere('email', 'like', "%$request->keyword%")
                            ->orWhere('company_name', "like", "%$request->keyword%");
                    });
                }
                if ($request->has('business_field') && $request->business_field != 0) {
                    $companies->where('business_field', $request->business_field);
                }
                $this->success = true;
                $this->data = $companies->select('company_name', 'id', 'address', 'phone_number', 'business_field')->paginate(($request->has('per_page') ? $request->per_page : 15));
            }
        } catch (QueryException $exception) {
            $this->success = false;
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $exception->getMessage());
            $this->error = $exception->getMessage();
        } catch (\Exception $exception) {
            \Log::error("[" . __METHOD__ . "][" . __LINE__ . "]" . "error:" . $exception->getMessage());
            $this->error = $exception->getMessage();
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Post(
     *   path="/company/edit/{id}",
     *   summary="Edit company by staff",
     *    tags={"Company"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of company",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="company_name",
     *         in="formData",
     *         description="Name of company",
     *         required=true,
     *         type="string",
     *         default="saver",
     *     ),
     *     @SWG\Parameter(
     *         name="address",
     *         in="formData",
     *         description="Address of campany",
     *         required=true,
     *         type="string",
     *         default="Da nang",
     *     ),
     *     @SWG\Parameter(
     *         name="email",
     *         in="formData",
     *         description="Email of staff",
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
     *         name="url",
     *         in="formData",
     *         description="Websie url of company",
     *         required=false,
     *         type="string",
     *         default="",
     *     ),
     *     @SWG\Parameter(
     *         name="agency_name",
     *         in="formData",
     *         description="Agency name of company",
     *         required=false,
     *         type="string",
     *         default="",
     *     ),
     *     @SWG\Parameter(
     *         name="business_field",
     *         in="formData",
     *         description="Business field of company",
     *         required=false,
     *         type="string",
     *         default="",
     *     ),
     *     @SWG\Parameter(
     *         name="latitude",
     *         in="formData",
     *         description="Latitude of company",
     *         required=false,
     *         type="number",
     *         default="",
     *     ),
     *     @SWG\Parameter(
     *         name="longitude",
     *         in="formData",
     *         description="Longitude of company",
     *         required=false,
     *         type="number",
     *         default="",
     *     ),
     *     @SWG\Parameter(
     *         name="introduction",
     *         in="formData",
     *         description="Introductionitude of company",
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
    public function editByStaff(Request $request, $company_id)
    {
        if (!(Auth::guard('staff-api')->user())->can('update', Staff::class)) {
            $this->error = "You do not have permission";
            return $this->doResponse($this->success, $this->data, $this->error);
        }
        try {
            $validator = Validator::make($request->all(), [
                'company_name' => 'required|max:255',
                'address' => 'max:255',
                'email' => 'max:255',
                'phone_number' => 'max:255',
                'url' => 'max:255',
                'agency_name' => 'max:255',
                'business_field' => 'max:255',
                'latitude' => 'numeric',
                'longitude' => 'numeric',
            ], [
                'required' => \Lang::get('common_message.error.MISS_PARAM'),
                'numeric' => \Lang::get('common_message.error.TYPE_INCORRECT'),
                'max' => \Lang::get('common_message.error.MAX_CONTENT')
            ]);

            if ($validator->fails()) {
                $this->error = $this->messgeValidate($validator->errors()->all());
            } else {
                $checkCompany = Company::checkExistCompany($company_id);
                if ($checkCompany) {
                    $company_name = $request->input('company_name');
                    $address = $request->input('address');
                    $email = $request->input('email');
                    $phone_number = $request->input('phone_number');
                    $url = $request->input('url');
                    $agency_name = $request->input('agency_name');
                    $business_field = $request->input('business_field');
                    $latitude = $request->input('latitude');
                    $longitude = $request->input('longitude');
                    $introduction = $request->input('introduction');

                    $this->data = Company::editByStaff($company_id, $company_name, $address, $email, $phone_number, $url, $agency_name, $business_field, $latitude, $longitude, $introduction);
                    if ($this->data) {
                        $company_id = $this->data;
                        $update_file_ids = array_map('intval', explode(',', $request->update_file_ids));
                        foreach ($update_file_ids as $file_id) {
                            $file = CompanyFile::detailFile($file_id);
                            if ($file) {
                                if (!$request->has("update_file_$file_id")) {
                                    $delete = CompanyFile::deleteFile($file_id);
                                    if ($delete) {
                                        dispatch((new SQSJob([
                                            'key' => $file->path,
                                            'processType' => 'delete',
                                        ])))
                                        ->onConnection('file_uploaded_queue');
                                    }
                                }
                            }
                        }
                    }
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

    public function randContentId($company_id)
    {
        return $company_id . rand(1, 10000);
    }

    public function saveImage($company_id, $file, $file_name)
    {
        $file_extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $file_name = $company_id . '_' . $file_name . '.' . $file_extension;
        $upload_image = FileProcess::upload('company/' . $file_name, $file->path());
        if ($upload_image) {
            return $file_name;
        }
    }

    /**
     * @SWG\Post(
     *   path="/company/report",
     *   summary="Company report about employee after one month",
     *    tags={"Company"},
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
                                                    &emsp;  'data': 1|...,
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
                $this->data = WorkConnection::edit($work_connection_id, null, null, null, null, $report, null, null);
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
                $message .= " & " . $err;
            }
            if (($i > 0) && ($i < $len - 1)) {
                $message .= ", " . $err;
            }
            $i++;
        }
        return $message;
    }

    public function getBusinessField()
    {
        $this->success = true;
        $this->data = BusinessField::select('id', 'business_name')->get();
        return $this->doResponse($this->success, $this->data, $this->error);
        ;
    }
}
