<?php
namespace App\Http\Controllers;

use App\Area;
use App\Job;
use App\JobFile;
use App\Favorite;
use App\IntroductionStatus;
use App\Connector;
use App\Category;
use App\Company;
use App\Chat;
use App\WorkConnection;
use Illuminate\Http\Request;
use Validator;
use App\Http\Support\FileProcess;
use Illuminate\Support\Facades\Auth;

use App\Jobs\SQSJob;

class JobController extends Controller
{
    protected $success = false;
    protected $data = null;
    protected $error = null;
    /**
        * @SWG\Get(
        *   path="/admin/job/list?page_limit={page_limit}&page_number={page_number}&keyword={keyword}&category={category}&start_date={start_date}&end_date={end_date}&api_token={api_token}",
        *   summary="Get list of jobs",
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
        *         default="DfCyLAmqKCggIb6R9T6diRetEdSahYDU4oODHQLGiGg3sKKtfr2NvXWRqInT",
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
        *         name="category",
        *         in="path",
        *         description="category",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="start_date",
        *         in="path",
        *         description="start_date",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="end_date",
        *         in="path",
        *         description="end_date",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': [{
                                                        &emsp;&emsp;&emsp;'address': ,
                                                        &emsp;&emsp;&emsp;'category_id': ,
                                                        &emsp;&emsp;&emsp;'category_name': ,
                                                        &emsp;&emsp;&emsp;'company_id': ,
                                                        &emsp;&emsp;&emsp;'company_name': ,
                                                        &emsp;&emsp;&emsp;'created_at': ,
                                                        &emsp;&emsp;&emsp;'id': 11,
                                                        &emsp;&emsp;&emsp;'title': ,
                                                        },{},...] | null,
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
            $category = $request->input('category');
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            if (empty($page_limit) || empty($page_number)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Job::getListJobs($page_limit, $page_number, $keyword, $category, $start_date, $end_date);
                $this->success = true;
                $this->error = null;
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
        *   path="/connector/job/new?api_token={api_token}&page_number={page_number}&page_limit={page_limit}",
        *   summary="Get new jobs",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="api_token",
        *         in="path",
        *         description="Api token of Connector",
        *         required=true,
        *         type="string",
        *         default="DfCyLAmqKCggIb6R9T6diRetEdSahYDU4oODHQLGiGg3sKKtfr2NvXWRqInT",
        *     ),
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
        *         description="Number item",
        *         required=true,
        *         type="number",
        *         default="4",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {
                                                        &emsp;      'total_items': [{'id': 11,
                                                        &emsp;      'data': [{
                                                        &emsp;          'id': ,
                                                        &emsp;          'company_id': ,
                                                        &emsp;          'company_name': ,
                                                        &emsp;          'title': ,
                                                        &emsp;          'main_image': ,
                                                        &emsp;          'category_id': ,
                                                        &emsp;          'category_name': ,
                                                        &emsp;      },{},...]
                                                        &emsp;  }| null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function new(Request $request)
    {
        try {
            $page_number = $request->input('page_number');
            $page_limit = $request->input('page_limit');
            if (empty($page_number) || empty($page_limit)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Job::getNewJobs($page_number, $page_limit);
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
        *   path="/admin/job/detail/{job_id}?api_token={api_token}",

        *   summary="Get info Connector",
        *    tags={"Admin"},
        *     @SWG\Parameter(
        *         name="job_id",
        *         in="path",
        *         description="Id of Job",
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
        *         default="DfCyLAmqKCggIb6R9T6diRetEdSahYDU4oODHQLGiGg3sKKtfr2NvXWRqInT",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {'id': 11,
                                                        &emsp;&emsp;&emsp;'company_id': ,
                                                        &emsp;&emsp;&emsp;'company_name': ,
                                                        &emsp;&emsp;&emsp;'staff_id': ,
                                                        &emsp;&emsp;&emsp;'username': ,
                                                        &emsp;&emsp;&emsp;'birthday': ,
                                                        &emsp;&emsp;&emsp;'title': ,
                                                        &emsp;&emsp;&emsp;'category_id': ,
                                                        &emsp;&emsp;&emsp;'category_name': ,
                                                        &emsp;&emsp;&emsp;'salary': ,
                                                        &emsp;&emsp;&emsp;'traffic': ,
                                                        &emsp;&emsp;&emsp;'introduction_title': ,
                                                        &emsp;&emsp;&emsp;'description': ,
                                                        &emsp;&emsp;&emsp; 'store_name':,
                                                        &emsp;&emsp;&emsp;'hours': ,
                                                        &emsp;&emsp;&emsp;'time_start': ,
                                                        &emsp;&emsp;&emsp;'time_end': ,
                                                        &emsp;&emsp;&emsp;'requirements': ,
                                                        &emsp;&emsp;&emsp;'treatment': ,
                                                        &emsp;&emsp;&emsp;'release_start_date': ,
                                                        &emsp;&emsp;&emsp;'release_end_date': ,
                                                        &emsp;&emsp;&emsp;'management_staff': ,
                                                        }| null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function detail(Request $request, $job_id)
    {
        $this->data = array();
        try {
            if (empty($job_id)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Job::getDetailById($job_id);
                if ($this->data) {
                    $this->data->images = JobFile::getJobFile($job_id, 1);
                    $this->data->videos = JobFile::getJobFile($job_id, 2);
                }
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
        * @SWG\Get(
        *   path="/company/job/detail/{job_id}?api_token={api_token}",

        *   summary="Get info Connector",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="job_id",
        *         in="path",
        *         description="Id of Job",
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
        *         default="DfCyLAmqKCggIb6R9T6diRetEdSahYDU4oODHQLGiGg3sKKtfr2NvXWRqInT",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {
                                                        &emsp;      'id': ,
                                                        &emsp;      'company_id': ,
                                                        &emsp;      'company_name': ,
                                                        &emsp;      'phone_number': ,
                                                        &emsp;      'latitude': ,
                                                        &emsp;      'longitude': ,
                                                        &emsp;      'url': ,
                                                        &emsp;      'staff_id': ,
                                                        &emsp;      'username': ,
                                                        &emsp;      'management_staff_name': ,
                                                        &emsp;      'title': ,
                                                        &emsp;      'category': ,
                                                        &emsp;      'job_category': ,
                                                        &emsp;      'description': ,
                                                        &emsp;      'traffic': ,
                                                        &emsp;      'introduction_title': ,
                                                        &emsp;      'introduction_content': ,
                                                        &emsp;      'store_name': ,
                                                        &emsp;      'hours': ,
                                                        &emsp;      'working_time': ,
                                                        &emsp;      'welcome': ,
                                                        &emsp;      'requirements': ,
                                                        &emsp;      'treatment': ,
                                                        &emsp;      'release_start_date': ,
                                                        &emsp;      'release_end_date': ,
                                                        &emsp;      'management_staff': ,
                                                        &emsp;      'workplace_status': ,
                                                        &emsp;      'job_content': ,
                                                        &emsp;      'job_type': ,
                                                        &emsp;      'area': ,
                                                        &emsp;      'area_id': ,
                                                        &emsp;      'job_type_id': ,
                                                        &emsp;      'category_id': ,
                                                        &emsp;      'job_category_id': ,
                                                        &emsp;      'salary': ,
                                                        &emsp;      'age_min': ,
                                                        &emsp;      'age_max': ,
                                                        &emsp;      'gender_ratio': ,
                                                        &emsp;      'address': ,
                                                        &emsp;      'images': [
                                                        &emsp;          {
                                                        &emsp;              'id': ,
                                                        &emsp;              'path': 
                                                        &emsp;          },
                                                        &emsp;          ...
                                                        &emsp;      ],
                                                        &emsp;      'videos': [
                                                        &emsp;          {
                                                        &emsp;              'id': ,
                                                        &emsp;              'path': 
                                                        &emsp;          },
                                                        &emsp;          ...
                                                        &emsp;      ]
                                                        &emsp;   }| null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function detailForCompany(Request $request, $job_id)
    {
        try {
            if (empty($job_id)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $staff_id = 0;
                $staff_privilege = 0;
                $company_id = 0;
                if (Auth::guest()) {
                    $user = Auth::guard('staff-api')->user();
                    $staff_id = $user->id;
                    $staff_privilege = $user->privilege;
                    $company_id = $user->company_id;
                }
                $check = Job::checkJobPermission($job_id, $staff_id, $staff_privilege, $company_id);
                if ($check) {
                    $this->data = Job::getDetailById($job_id);
                    if ($this->data) {
                        $this->data->images = JobFile::getJobFile($job_id, 1);
                        $this->data->videos = JobFile::getJobFile($job_id, 2);
                    }
                    $this->success = true;
                    $this->error = null;
                } else {
                    $this->success = false;
                    $this->error = \Lang::get('common_message.error.OBJECT_NOT_EXIST');
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

    /**
        * @SWG\Get(
        *   path="/connector/job/detail/{job_id}?connector_id={connector_id}&api_token={api_token}",

        *   summary="Get info Connector",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="job_id",
        *         in="path",
        *         description="Id of Job",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
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
        *         default="DfCyLAmqKCggIb6R9T6diRetEdSahYDU4oODHQLGiGg3sKKtfr2NvXWRqInT",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {
                                                        &emsp;      'id': ,
                                                        &emsp;      'company_id': ,
                                                        &emsp;      'company_name': ,
                                                        &emsp;      'phone_number': ,
                                                        &emsp;      'latitude': ,
                                                        &emsp;      'longitude': ,
                                                        &emsp;      'url': ,
                                                        &emsp;      'staff_id': ,
                                                        &emsp;      'username': ,
                                                        &emsp;      'management_staff_name': ,
                                                        &emsp;      'title': ,
                                                        &emsp;      'category': ,
                                                        &emsp;      'job_category': ,
                                                        &emsp;      'description': ,
                                                        &emsp;      'traffic': ,
                                                        &emsp;      'introduction_title': ,
                                                        &emsp;      'introduction_content': ,
                                                        &emsp;      'store_name': ,
                                                        &emsp;      'hours': ,
                                                        &emsp;      'working_time': ,
                                                        &emsp;      'welcome': ,
                                                        &emsp;      'requirements': ,
                                                        &emsp;      'treatment': ,
                                                        &emsp;      'release_start_date': ,
                                                        &emsp;      'release_end_date': ,
                                                        &emsp;      'management_staff': ,
                                                        &emsp;      'workplace_status': ,
                                                        &emsp;      'job_content': ,
                                                        &emsp;      'job_type': ,
                                                        &emsp;      'area': ,
                                                        &emsp;      'area_id': ,
                                                        &emsp;      'job_type_id': ,
                                                        &emsp;      'category_id': ,
                                                        &emsp;      'job_category_id': ,
                                                        &emsp;      'salary': ,
                                                        &emsp;      'age_min': ,
                                                        &emsp;      'age_max': ,
                                                        &emsp;      'gender_ratio': ,
                                                        &emsp;      'address': ,
                                                        &emsp;      'is_favorite': ,
                                                        &emsp;      'images': [
                                                        &emsp;          ...,
                                                        &emsp;      ],
                                                        &emsp;      'video': ,
                                                        &emsp;      'base_path': 
                                                        &emsp;  }| null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function detailByConnector(Request $request, $job_id)
    {
        $this->data = array();
        try {
            $connector_id = $request->input('connector_id');
            if (empty($job_id) || empty($connector_id)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Job::getDetailByConnector($job_id, $connector_id);
                $this->data['base_path'] = Job::getBasePathJob();
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
        *   path="/admin/job/delete/{id}",
        *   summary="Delete job",
        *    tags={"Admin"},
        *     @SWG\Parameter(
        *         name="id",
        *         in="path",
        *         description="Id of Job",
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

    /**
        * @SWG\Post(
        *   path="/company/job/delete/{id}",
        *   summary="Delete job",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="id",
        *         in="path",
        *         description="Id of Job",
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
        *          default="UnJlBBetMaOEJRpTcZDS1yGnG34MD94CKD3FxDFHPkT808WYbHjAhgKMHJEn",
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
            if (empty($id)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $check = Job::checkExistJob($id);
                if ($check) {
                    $this->data = Job::deleteById($id);
                    if ($this->data) {
                        $this->success = true;
                        $this->error = null;

                        $files = JobFile::deleteFileJob($id);
                        if (!empty($files)) {
                            foreach ($files as $file) {
                                dispatch((new SQSJob([
                                    'key' => $file->path,
                                    'processType' => 'delete',
                                ])))
                                ->onConnection('file_uploaded_queue');
                            }
                        }
                    } else {
                        $this->success = false;
                        $this->error = \Lang::get('common_message.error.DELETE_FAIL');
                    }
                } else {
                    $this->error = \Lang::get('common_message.error.OBJECT_NOT_EXIST');
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
        return $this->doResponse($this->success, null, $this->error);
    }

    /**
        * @SWG\Post(
        *   path="/company/job/add",
        *   summary="Add job",
        *   tags={"Company"},
        *     @SWG\Parameter(
        *         name="title",
        *         in="formData",
        *         description="Title of job",
        *         required=true,
        *         type="string",
        *         default="Laravel",
        *     ),
        *     @SWG\Parameter(
        *         name="company_id",
        *         in="formData",
        *         description="Company of job",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="staff_id",
        *         in="formData",
        *         description="Staff created job",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="area_id",
        *         in="formData",
        *         description="Area",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="description",
        *         in="formData",
        *         description="Description",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="category_id",
        *         in="formData",
        *         description="Category of job id",
        *         required=true,
        *         type="string",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="job_category_id",
        *         in="formData",
        *         description="Job Category of job id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="job_type_id",
        *         in="formData",
        *         description="Type of job of job id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="salary",
        *         in="formData",
        *         description="Salary",
        *         required=false,
        *         type="string",
        *         default="2000",
        *     ),
        *     @SWG\Parameter(
        *         name="age_min",
        *         in="formData",
        *         description="Age min",
        *         required=false,
        *         type="number",
        *         default="20",
        *     ),
        *     @SWG\Parameter(
        *         name="age_max",
        *         in="formData",
        *         description="Age max",
        *         required=false,
        *         type="number",
        *         default="50",
        *     ),
        *     @SWG\Parameter(
        *         name="gender_ratio",
        *         in="formData",
        *         description="Gender ratio",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="address",
        *         in="formData",
        *         description="Address",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="traffic",
        *         in="formData",
        *         description="Traffic",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="introduction_title",
        *         in="formData",
        *         description="Introduction title",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="introduction_content",
        *         in="formData",
        *         description="Introduction content",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="job_content",
        *         in="formData",
        *         description="Content of job",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="store_name",
        *         in="formData",
        *         description="Store name",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="workplace_status",
        *         in="formData",
        *         description="Workplace Status",
        *         required=false,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="hours",
        *         in="formData",
        *         description="Hours",
        *         required=false,
        *         type="number",
        *         default="8",
        *     ),
        *     @SWG\Parameter(
        *         name="welcome",
        *         in="formData",
        *         description="Welcome",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="working_time",
        *         in="formData",
        *         description="Working time",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="requirements",
        *         in="formData",
        *         description="Requirements",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="treatment",
        *         in="formData",
        *         description="Treatment",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="release_start_date",
        *         in="formData",
        *         description="Release start date",
        *         required=false,
        *         type="string",
        *         default="2018-12-12",
        *     ),
        *     @SWG\Parameter(
        *         name="release_end_date",
        *         in="formData",
        *         description="Release end date",
        *         required=false,
        *         type="string",
        *         default="2018-12-30",
        *     ),
        *     @SWG\Parameter(
        *         name="management_staff",
        *         in="formData",
        *         description="Manager id of staff",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="image1",
        *         in="formData",
        *         description="Image of company",
        *         required=false,
        *         type="file",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="image2",
        *         in="formData",
        *         description="Image of company",
        *         required=false,
        *         type="file",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="image3",
        *         in="formData",
        *         description="Image of company",
        *         required=false,
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
    public function add(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'company_id' => 'required | numeric',
                'staff_id' => 'required | numeric',
                'category_id' => 'required | numeric',
                'job_category_id' => 'required | numeric',
                'job_type_id' => 'numeric | nullable',
                'area_id' => 'numeric | nullable',
                'age_min' => 'numeric | nullable',
                'age_max' => 'numeric | nullable',
                'gender_ratio' => 'numeric | nullable',
                'management_staff' => 'required | numeric',
                'hours' => 'numeric | nullable',
                'release_start_date' => 'required',
                'release_end_date' => 'required',
            ], [
                'required' => 'error_required',
                'numeric' => 'error_numeric'
            ]);
            if ($validator->fails()) {
                $this->success = false;
                $this->error = null;
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
                $job_title = $request->input('title');
                $job_company_id = $request->input('company_id');
                $job_staff_id = $request->input('staff_id');
                $job_category_id = $request->input('category_id');
                $job_job_category_id = $request->input('job_category_id');
                $job_job_type_id = $request->input('job_type_id');
                $job_area_id = $request->input('area_id');
                $job_description = $request->input('description');
                $job_address = $request->input('address');
                $job_gender_ratio = $request->input('gender_ratio');
                $job_salary = $request->input('salary');
                $job_age_min = $request->input('age_min');
                $job_age_max = $request->input('age_max');
                $job_traffic = $request->input('traffic');
                $job_introduction_title = $request->input('introduction_title');
                $job_introduction_content = $request->input('introduction_content');
                $job_content = $request->input('job_content');
                $job_store_name = $request->input('store_name');
                $job_workplace_status = $request->input('workplace_status');
                $job_hours = $request->input('hours');
                $job_working_time = $request->input('working_time');
                $job_welcome = $request->input('welcome');
                $job_requirements = $request->input('requirements');
                $job_treatment = $request->input('treatment');
                $job_release_start_date = $request->input('release_start_date');
                $job_release_end_date = $request->input('release_end_date');
                $job_management_staff = $request->input('management_staff');

                $staff_privilege = 1;
                if (Auth::guest()) {
                    $user = Auth::guard('staff-api')->user();
                    $staff_privilege = $user->privilege;
                }
                if ($staff_privilege == 2) {
                    $job_management_staff = $job_staff_id;
                }

                $this->data = Job::add($job_title, $job_company_id, $job_staff_id, $job_category_id, $job_job_category_id, $job_job_type_id, $job_area_id, $job_description, $job_address, $job_salary, $job_age_min, $job_gender_ratio, $job_age_max, $job_traffic, $job_introduction_title, $job_introduction_content, $job_content, $job_store_name, $job_workplace_status, $job_hours, $job_working_time, $job_welcome, $job_requirements, $job_treatment, $job_release_start_date, $job_release_end_date, $job_management_staff);
                if ($this->data) {
                    $job_id = $this->data;
                    if ($request->file_length) {
                        for ($i = 1; $i <= $request->file_length; $i++) {
                            if ($request->hasFile("image$i")) {
                                $file = $request->file("image$i");
                                $content_id = $this->randContentId($job_id);
                                $image_name = $this->saveImage($job_id, $file, $content_id);
                                JobFile::addFile($content_id, $job_id, $image_name, 1);
                            }
                            if ($request->hasFile("video$i")) {
                                $file = $request->file("video$i");
                                $content_id = $this->randContentId($job_id);
                                $image_name = $this->saveImage($job_id, $file, $content_id);
                                JobFile::addFile($content_id, $job_id, $image_name, 2);
                            }
                        }
                    }
                    // if ($request->image_length) {
                    //     for ($i=1; $i <= $request->image_length; $i++) {
                    //         if ($request->hasFile("image$i")) {
                    //             $file = $request->file("image$i");
                    //             $content_id = $this->randContentId($job_id);
                    //             $image_name = $this->saveImage($job_id, $file, $content_id);
                    //             JobFile::addFile($content_id, $job_id, $image_name, 1);
                    //         }
                    //     }
                    // }
                    // if ($request->hasFile('video')) {
                    //     $file = $request->file('video');
                    //     $content_id = $this->randContentId($job_id);
                    //     $image_name = $this->saveImage($job_id, $file, $content_id);
                    //     JobFile::addFile($content_id, $job_id, $image_name, 2);
                    // }
                }
                $this->success = true;
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

    public function randContentId($job_id)
    {
        return $job_id . rand(1, 10000);
    }
    
    public function saveImage($job_id, $file, $file_name)
    {
        $file_extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        $file_name = $job_id . '_' . $file_name . '.' . $file_extension;
        $upload_image = FileProcess::upload('job/' . $file_name, $file->path());
        if ($upload_image) {
            return $file_name;
        }
    }

    /**
        * @SWG\Post(
        *   path="/company/job/edit/{id}",
        *   summary="Edit job",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="id",
        *         in="path",
        *         description="Id of job",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="title",
        *         in="formData",
        *         description="Title of job",
        *         required=true,
        *         type="string",
        *         default="Laravel edit",
        *     ),
        *     @SWG\Parameter(
        *         name="company_id",
        *         in="formData",
        *         description="Company of job",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="staff_id",
        *         in="formData",
        *         description="Staff created job",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="area_id",
        *         in="formData",
        *         description="Area",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="description",
        *         in="formData",
        *         description="Description",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="category_id",
        *         in="formData",
        *         description="Category of job",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="job_category_id",
        *         in="formData",
        *         description="Job category id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="job_type_id",
        *         in="formData",
        *         description="Type id of job",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="salary",
        *         in="formData",
        *         description="Salary",
        *         required=false,
        *         type="string",
        *         default="1000",
        *     ),
        *     @SWG\Parameter(
        *         name="age_min",
        *         in="formData",
        *         description="Age min",
        *         required=false,
        *         type="number",
        *         default="20",
        *     ),
        *     @SWG\Parameter(
        *         name="age_max",
        *         in="formData",
        *         description="Age max",
        *         required=false,
        *         type="number",
        *         default="20",
        *     ),
        *     @SWG\Parameter(
        *         name="gender_ratio",
        *         in="formData",
        *         description="Gender ratio",
        *         required=false,
        *         type="number",
        *         default="50",
        *     ),
        *     @SWG\Parameter(
        *         name="address",
        *         in="formData",
        *         description="Adress",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="traffic",
        *         in="formData",
        *         description="Traffic",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="introduction_title",
        *         in="formData",
        *         description="Introduction title",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="introduction_content",
        *         in="formData",
        *         description="Introduction content",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="job_content",
        *         in="formData",
        *         description="Job content",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="store_name",
        *         in="formData",
        *         description="Store name",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="workplace_status",
        *         in="formData",
        *         description="Workplace Status",
        *         required=false,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="hours",
        *         in="formData",
        *         description="Hours",
        *         required=false,
        *         type="number",
        *         default="8",
        *     ),
        *     @SWG\Parameter(
        *         name="welcome",
        *         in="formData",
        *         description="Welcome",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="working_time",
        *         in="formData",
        *         description="Time working",
        *         required=false,
        *         type="string",
        *         default="9am - 18pm",
        *     ),
        *     @SWG\Parameter(
        *         name="requirements",
        *         in="formData",
        *         description="Requirements",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="treatment",
        *         in="formData",
        *         description="Treatment",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="release_start_date",
        *         in="formData",
        *         description="Release start date",
        *         required=true,
        *         type="string",
        *         default="2018-12-12",
        *     ),
        *     @SWG\Parameter(
        *         name="release_end_date",
        *         in="formData",
        *         description="Release end date",
        *         required=true,
        *         type="string",
        *         default="2018-12-31",
        *     ),
        *     @SWG\Parameter(
        *         name="management_staff",
        *         in="formData",
        *         description="Manager id of staff",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="image1",
        *         in="formData",
        *         description="Image of company",
        *         required=false,
        *         type="file",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="image2",
        *         in="formData",
        *         description="Image of company",
        *         required=false,
        *         type="file",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="image3",
        *         in="formData",
        *         description="Image of company",
        *         required=false,
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
    public function edit(Request $request, $job_id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'company_id' => 'required | numeric',
                'staff_id' => 'required | numeric',
                'category_id' => 'required | numeric',
                'job_category_id' => 'required | numeric',
                'job_type_id' => 'required | numeric',
                'management_staff' => 'required | numeric',
                'hours' => 'numeric | nullable',
                'area_id' => 'numeric | nullable',
                'age_min' => 'numeric | nullable',
                'age_max' => 'numeric | nullable',
                'release_start_date' => 'required',
                'release_end_date' => 'required',
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
                }
            } else {
                $check_exist_job = Job::checkExistJob($job_id);
                if ($check_exist_job) {
                    $job_title = $request->input('title');
                    $job_category_id = $request->input('category_id');
                    $job_job_category_id = $request->input('job_category_id');
                    $job_job_type_id = $request->input('job_type_id');
                    $job_area_id = $request->input('area_id');
                    $job_description = $request->input('description');
                    $job_address = $request->input('address');
                    $job_gender_ratio = $request->input('gender_ratio');
                    $job_salary = $request->input('salary');
                    $job_age_min = $request->input('age_min');
                    $job_age_max = $request->input('age_max');
                    $job_traffic = $request->input('traffic');
                    $job_introduction_title = $request->input('introduction_title');
                    $job_introduction_content = $request->input('introduction_content');
                    $job_job_content = $request->input('job_content');
                    $job_store_name = $request->input('store_name');
                    $job_workplace_status = $request->input('workplace_status');
                    $job_hours = $request->input('hours');
                    $job_working_time = $request->input('working_time');
                    $job_welcome = $request->input('welcome');
                    $job_requirements = $request->input('requirements');
                    $job_treatment = $request->input('treatment');
                    $job_release_start_date = $request->input('release_start_date');
                    $job_release_end_date = $request->input('release_end_date');
                    $job_management_staff = $request->input('management_staff');
                    
                    $this->data = Job::edit($job_id, $job_title, $job_category_id, $job_job_category_id, $job_job_type_id, $job_area_id, $job_description, $job_address, $job_salary, $job_age_min, $job_gender_ratio, $job_age_max, $job_traffic, $job_introduction_title, $job_introduction_content, $job_job_content, $job_store_name, $job_workplace_status, $job_hours, $job_working_time, $job_welcome, $job_requirements, $job_treatment, $job_release_start_date, $job_release_end_date, $job_management_staff);
                    if ($this->data) {
                        $job_id = $this->data;
                        $update_file_ids = array_map('intval', explode(',', $request->update_file_ids));
                        foreach ($update_file_ids as $file_id) {
                            $file = JobFile::detailFile($file_id);
                            if ($file) {
                                if (!$request->has("update_file_$file_id")) {
                                    $delete = JobFile::deleteFile($file_id);
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
                    $this->success = false;
                    $this->error = \Lang::get('common_message.error.OBJECT_NOT_EXIST');
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
    /**
        * @SWG\Get(
        *   path="/company/job/list?page_limit={page_limit}&page_number={page_number}&keyword={keyword}&category_id={category_id}&start_date={start_date}&end_date={end_date}&api_token={api_token}",
        *   summary="Get list job by staff",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="page_limit",
        *         in="path",
        *         description="Limit job in one page",
        *         required=true,
        *         type="number",
        *         default="5",
        *     ),
        *     @SWG\Parameter(
        *         name="page_number",
        *         in="path",
        *         description="Current page",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="keyword",
        *         in="path",
        *         description="Title of job",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="category_id",
        *         in="path",
        *         description="Category Id",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="start_date",
        *         in="path",
        *         description="Date created start",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="end_date",
        *         in="path",
        *         description="Date created end",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *          name="api_token",
        *          in="path",
        *          required=true,
        *          description="API token of Connector",
        *          type="string",
        *          default="UnJlBBetMaOEJRpTcZDS1yGnG34MD94CKD3FxDFHPkT808WYbHjAhgKMHJEn",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {
                                                        &emsp;       'total': ,
                                                        &emsp;       'data': [{
                                                        &emsp;                'id': ,
                                                        &emsp;                'address': ,
                                                        &emsp;                'title': ,
                                                        &emsp;                'created_at': ,
                                                        &emsp;                'category_name': ,
                                                        &emsp;                'job_category_name': 
                                                        &emsp;              },
                                                        &emsp;              ...
                                                        &emsp;       ]|...,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function getByStaff(Request $request)
    {
        try {
            $page_limit = $request->input('page_limit');
            $page_number = $request->input('page_number');
            if (empty($page_limit) || empty($page_number)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $privilege = 2;
                $staff_id = null;
                $company_id = null;
                if (Auth::guest()) {
                    $user = Auth::guard('staff-api')->user();
                    $privilege = $user->privilege;
                    $staff_id = $user->id;
                    $company_id = $user->company_id;
                }
                $keyword = $request->input('keyword');
                $category_id = $request->input('category_id');
                $start_date = $request->input('start_date');
                $end_date = $request->input('end_date');

                $this->data = Job::getByStaff($staff_id, $privilege, $company_id, $page_limit, $page_number, $keyword, $category_id, $start_date, $end_date);
                $this->success = true;
                $this->error = null;
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
        *   path="/connector/job/set-favorite",
        *   summary="Set favorite job",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="job_id",
        *         in="formData",
        *         description="Job Id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="connector_id",
        *         in="formData",
        *         description="Connector Id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="is_favorite",
        *         in="formData",
        *         description="Favorite status",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *          name="api_token",
        *          in="formData",
        *          required=true,
        *          description="API token of Connector",
        *          type="string",
        *          default="U1n8N6yLQbDNhQL0Gl3y1WXHV0fp9XOudgpGcYQy14PGVfV0LMemtMegto6m",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': 1| null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function setFavorite(Request $request)
    {
        try {
            $job_id = $request->input('job_id');
            $connector_id = $request->input('connector_id');
            $is_favorite = $request->input('is_favorite');
            if (empty($job_id) || empty($connector_id)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Favorite::add($job_id, $connector_id, $is_favorite);
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
        * @SWG\Get(
        *   path="/connector/job/favorite?connector_id={connector_id}&page_number={page_number}&page_limit={page_limit}&api_token={api_token}",
        *   summary="Get list favorite job",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="connector_id",
        *         in="path",
        *         description="Connector Id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
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
        *         description="Number item",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *          name="api_token",
        *          in="path",
        *          required=true,
        *          description="API token of Connector",
        *          type="string",
        *          default="UnJlBBetMaOEJRpTcZDS1yGnG34MD94CKD3FxDFHPkT808WYbHjAhgKMHJEn",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': [{
                                                        &emsp;      'total_items': ,
                                                        &emsp;      'data': [{
                                                        &emsp;               'id': 1,
                                                        &emsp;               'title': ,
                                                        &emsp;               'category_id': ,
                                                        &emsp;               'category_name': ,
                                                        &emsp;               'salary': ,
                                                        &emsp;               'is_favorite': ,
                                                        &emsp;               'main_image': ,
                                                        &emsp;               'base_path': ,
                                                        &emsp;         },
                                                        &emsp;         ...
                                                        &emsp;      ]
                                                        &emsp;  } | ...,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function favorite(Request $request)
    {
        try {
            $connector_id = $request->input('connector_id');
            $page_number = $request->input('page_number');
            $page_limit = $request->input('page_limit');
            if (empty($connector_id) || empty($page_number) || empty($page_limit)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Favorite::getFavoriteJob($connector_id, $page_number, $page_limit);
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
        * @SWG\Get(
        *   path="/admin/job/category?api_token={api_token}",
        *   summary="Get list of categories",
        *    tags={"Admin"},
        *     @SWG\Parameter(
        *         name="api_token",
        *         in="path",
        *         description="Api token of Admin",
        *         required=true,
        *         type="string",
        *         default="YccJm3KSKJcRA7gP3Uh3haNiVLm9ei1EyYeRkUXkojlpkwoC5Wi8KmOzsXQl",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': [{'id': 11,
                                                        &emsp;&emsp;&emsp;'category_name': ,
                                                        },{},...] | null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function getListCategory()
    {
        try {
            $this->data = Category::getList();
            $this->success = true;
            $this->error = null;
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
        *   path="/connector/job/apply",
        *   summary="Connector apply a job",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="job_id",
        *         in="formData",
        *         description="Job Id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="connector_id",
        *         in="formData",
        *         description="Connector Id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="introduction_code",
        *         in="formData",
        *         description="Introduction code",
        *         required=false,
        *         type="string",
        *         default="342rhxamso87",
        *     ),
        *     @SWG\Parameter(
        *          name="api_token",
        *          in="formData",
        *          required=true,
        *          description="API token of Connector",
        *          type="string",
        *          default="U1n8N6yLQbDNhQL0Gl3y1WXHV0fp9XOudgpGcYQy14PGVfV0LMemtMegto6m",
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
    public function apply(Request $request)
    {
        try {
            $job_id = $request->input('job_id');
            $connector_id = $request->input('connector_id');
            $introduction_code = $request->input('introduction_code');
            if (empty($job_id) || empty($connector_id)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $check_exist_apply = WorkConnection::checkAppliedJob($connector_id, $job_id);
                if ($check_exist_apply) {
                    if (!empty($introduction_code)) {
                        $introduction_id = Connector::get_id_by_code($introduction_code);
                        if (!empty($introduction_id)) {
                            $this->data = WorkConnection::add($job_id, $connector_id, $introduction_id);
                            // IntroductionStatus::add($job_id, $connector_id, null, $introduction_id, 2);
                            $name_of_friend = Connector::getNameById($connector_id);
                            $info_company = Company::getCompanyByJobId($job_id);
                            $content = $name_of_friend . ' さんが、あなたのコードで' . $info_company['company_name'] . '店へ応募しました。';
                            IntroductionStatus::add($connector_id, $introduction_id, $job_id, 2, null, $content, null);
                        } else {
                            $this->data = WorkConnection::add($job_id, $connector_id, null);
                        }
                    } else {
                        $this->data = WorkConnection::add($job_id, $connector_id, null);
                    }
                    if ($this->data) {
                        Chat::add($connector_id, $job_id);
                    }
                    $this->success = true;
                } else {
                    $this->success = false;
                    $this->error = \Lang::get('common_message.error.APPLIED_JOB');
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
  

    /**
        * @SWG\Get(
        *   path="/connector/job/list-by-category?connector_id={connector_id}&category_id={category_id}&page_number={page_number}&page_limit={page_limit}&api_token={api_token}",
        *   summary="Get list job of categories",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="api_token",
        *         in="path",
        *         description="Api token of Connector",
        *         required=true,
        *         type="string",
        *         default="YccJm3KSKJcRA7gP3Uh3haNiVLm9ei1EyYeRkUXkojlpkwoC5Wi8KmOzsXQl",
        *     ),
        *     @SWG\Parameter(
        *         name="connector_id",
        *         in="path",
        *         description="connector_id",
        *         required=true,
        *         type="number",
        *         default="10",
        *     ),
        *     @SWG\Parameter(
        *         name="category_id",
        *         in="path",
        *         description="category_id",
        *         required=true,
        *         type="number",
        *         default="10",
        *     ),
        *     @SWG\Parameter(
        *         name="page_number",
        *         in="path",
        *         description="Page number",
        *         required=false,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="page_limit",
        *         in="path",
        *         description="Number item",
        *         required=false,
        *         type="number",
        *         default="4",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp; 'data':{
                                                        &emsp;      'total_items': ,
                                                        &emsp;      'data': [
                                                        &emsp;          {
                                                        &emsp;              'id': ,
                                                        &emsp;              'title': ,
                                                        &emsp;              'category_id': ,
                                                        &emsp;              'category_name': ,
                                                        &emsp;              'salary': ,
                                                        &emsp;              'is_favorite': ,
                                                        &emsp;              'main_image': ,
                                                        &emsp;              'base_path': 
                                                        &emsp;          },
                                                        &emsp;          {},...,
                                                        &emsp;       ],
                                                        &emsp;  }
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function getListJobsByCategoryId(Request $request)
    {
        try {
            $connector_id = $request->input('connector_id');
            $category_id = $request->input('category_id');
            $page_number = $request->input('page_number');
            $page_limit = $request->input('page_limit');

            if (empty($connector_id) || empty($category_id) || empty($page_number) || empty($page_limit)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Job::getListJobsByCategoryId($connector_id, $category_id, $page_number, $page_limit);
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
        *   path="/connector/job/list-with-category?connector_id={connector_id}&number_item={number_item}&api_token={api_token}",
        *   summary="Get list job of each category",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="api_token",
        *         in="path",
        *         description="Api token of Connector",
        *         required=true,
        *         type="string",
        *         default="YccJm3KSKJcRA7gP3Uh3haNiVLm9ei1EyYeRkUXkojlpkwoC5Wi8KmOzsXQl",
        *     ),
        *     @SWG\Parameter(
        *         name="connector_id",
        *         in="path",
        *         description="connector_id",
        *         required=true,
        *         type="number",
        *         default="10",
        *     ),
        *     @SWG\Parameter(
        *         name="number_item",
        *         in="path",
        *         description="Number item",
        *         required=true,
        *         type="number",
        *         default="4",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp; 'data':[
                                                        &emsp;          {
                                                        &emsp;              'category_id': ,
                                                        &emsp;              'category_name': ,
                                                        &emsp;              'total_items': ,
                                                        &emsp;              'jobs': [
                                                        &emsp;                  {
                                                        &emsp;                      'id': ,
                                                        &emsp;                      'title': ,
                                                        &emsp;                      'category_id': ,
                                                        &emsp;                      'category_name': ,
                                                        &emsp;                      'salary': ,
                                                        &emsp;                      'is_favorite': ,
                                                        &emsp;                      'main_image': ,
                                                        &emsp;                      'base_path': 
                                                        &emsp;                  },
                                                        &emsp;                  {},...
                                                        &emsp;              ],
                                                        &emsp;          },
                                                        &emsp;          {},...
                                                        &emsp;  ] | null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function getListJobsWithCategory(Request $request)
    {
        try {
            $connector_id = $request->input('connector_id');
            $number_item = $request->input('number_item');

            if (empty($connector_id) || empty($number_item)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Job::getListJobsWithCategory($connector_id, $number_item);
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
        *   path="/connector/job/search?connector_id={connector_id}&category_id={category_id}&page_number={page_number}&page_limit={page_limit}&area_id={area_id}&job_category_id={job_category_id}&age={age}&gender_ratio={gender_ratio}&workplace_status={workplace_status}&keyword={keyword}&job_type_id={job_type_id}&api_token={api_token}",
        *   summary="Search job",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="api_token",
        *         in="path",
        *         description="Api token of Connector",
        *         required=true,
        *         type="string",
        *         default="YccJm3KSKJcRA7gP3Uh3haNiVLm9ei1EyYeRkUXkojlpkwoC5Wi8KmOzsXQl",
        *     ),
        *     @SWG\Parameter(
        *         name="connector_id",
        *         in="path",
        *         description="connector_id",
        *         required=true,
        *         type="number",
        *         default="10",
        *     ),
        *     @SWG\Parameter(
        *         name="page_number",
        *         in="path",
        *         description="Page Number",
        *         required=true,
        *         type="number",
        *         default="4",
        *     ),
        *     @SWG\Parameter(
        *         name="page_limit",
        *         in="path",
        *         description="Number item",
        *         required=true,
        *         type="number",
        *         default="4",
        *     ),
        *     @SWG\Parameter(
        *         name="area_id",
        *         in="path",
        *         description="Id of Area",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="job_category_id",
        *         in="path",
        *         description="Id of Job Category",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="age",
        *         in="path",
        *         description="Age",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="gender_ratio",
        *         in="path",
        *         description="Gender ratio",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="workplace_status",
        *         in="path",
        *         description="Workplase Status",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="keyword",
        *         in="path",
        *         description="Keyword",
        *         required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="job_type_id",
        *         in="path",
        *         description="Id of Job Type",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="category_id",
        *         in="path",
        *         description="Id of Category",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp; 'data':{
                                                        &emsp;      'total_items': ,
                                                        &emsp;      'data': [
                                                        &emsp;          {
                                                        &emsp;              'id': ,
                                                        &emsp;              'title': ,
                                                        &emsp;              'category_id': ,
                                                        &emsp;              'category_name': ,
                                                        &emsp;              'salary': ,
                                                        &emsp;              'is_favorite': ,
                                                        &emsp;              'main_image': ,
                                                        &emsp;              'base_path': 
                                                        &emsp;          },
                                                        &emsp;          {},...
                                                        &emsp;      ],
                                                        &emsp;  } | null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function search(Request $request)
    {
        try {
            $connector_id = $request->input('connector_id');
            $area_id = $request->input('area_id');
            $job_category_id = $request->input('job_category_id');
            $age = $request->input('age');
            $gender_ratio = $request->input('gender_ratio');
            $workplace_status = $request->input('workplace_status');
            $keyword = $request->input('keyword');
            $job_type_id = $request->input('job_type_id');
            $category_id = $request->input('category_id');
            $page_number = $request->input('page_number');
            $page_limit = $request->input('page_limit');

            if (empty($connector_id) || empty($page_number) || empty($page_limit)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Job::getListJobsWithCondition($connector_id, $page_number, $page_limit, $area_id, $job_category_id, $age, $gender_ratio, $workplace_status, $keyword, $job_type_id, $category_id);
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
        *   path="/connector/job/list-applied?connector_id={connector_id}&page_number={page_number}&page_limit={page_limit}&api_token={api_token}",
        *   summary="Get list of categories",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="api_token",
        *         in="path",
        *         description="Api token of Connector",
        *         required=true,
        *         type="string",
        *         default="YccJm3KSKJcRA7gP3Uh3haNiVLm9ei1EyYeRkUXkojlpkwoC5Wi8KmOzsXQl",
        *     ),
        *     @SWG\Parameter(
        *         name="connector_id",
        *         in="path",
        *         description="connector_id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="page_number",
        *         in="path",
        *         description="Number Page",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="page_limit",
        *         in="path",
        *         description="Number item",
        *         required=true,
        *         type="number",
        *         default="4",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp; 'data': {
                                                        &emsp;      'total_date': ,
                                                        &emsp;      'data': [
                                                        &emsp;          {
                                                        &emsp;              'date': ,
                                                        &emsp;              'jobs': [
                                                        &emsp;                  {
                                                        &emsp;                      'id': ,
                                                        &emsp;                      'title': ,
                                                        &emsp;                      'status': ,
                                                        &emsp;                      'category_name': ,
                                                        &emsp;                      'chat_id': ,
                                                        &emsp;                      'is_favorite': ,
                                                        &emsp;                      'main_image': ,
                                                        &emsp;                      'base_path': ,
                                                        &emsp;                      'date_at': 
                                                        &emsp;                  },
                                                        &emsp;                  {},...
                                                        &emsp;              ]
                                                        &emsp;          },
                                                        &emsp;          {},...
                                                        &emsp;      ],
                                                        &emsp;  } | null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function getListJobsAppliedByConnectorId(Request $request)
    {
        try {
            $connector_id = $request->input('connector_id');
            $page_number = $request->input('page_number');
            $page_limit = $request->input('page_limit');
            if (empty($connector_id) || empty($page_number) || empty($page_limit)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = WorkConnection::getListJobsAppliedByConnectorId($connector_id, $page_number, $page_limit);
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
}
