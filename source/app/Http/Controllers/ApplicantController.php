<?php
namespace App\Http\Controllers;

use App\WorkConnection;
use App\Connector;
use App\Company;
use App\Payment;
use App\Job;
use App\Notification;
use App\IntroductionStatus;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;

class ApplicantController extends Controller
{
    private $success = false;
    private $data = null;
    private $error = null;

    /**
        * @SWG\Get(
        *   path="/admin/job/applicant/list?job_id={job_id}&page_limit={page_limit}&page_number={page_number}&keyword={keyword}&status={status}&api_token={api_token}",
        *   summary="Get list apllicant by admin",
        *    tags={"Admin"},
        *     @SWG\Parameter(
        *         name="job_id",
        *         in="path",
        *         description="Job Id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="page_limit",
        *         in="path",
        *         description="Limit number in one page",
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
        *         description="keyword",
         *        required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="status",
        *         in="path",
        *         description="status",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *          name="api_token",
        *          in="path",
        *          required=true,
        *          description="API token of Admin",
        *          type="string",
        *          default="YccJm3KSKJcRA7gP3Uh3haNiVLm9ei1EyYeRkUXkojlpkwoC5Wi8KmOzsXQl",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': [{
                                                        &emsp;               'id': ,
                                                        &emsp;               'apply_date': ,
                                                        &emsp;               'username': ,
                                                        &emsp;               'email': ,
                                                        &emsp;               'phone_number': ,
                                                        &emsp;               'birthday': ,
                                                        &emsp;               'gender': ,
                                                        &emsp;               'connector_id': ,
                                                        &emsp;               'job_title':
                                                        &emsp;         },
                                                        &emsp;         {
                                                        &emsp;         },...]|...,
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
            $job_id = $request->input('job_id');
            $page_limit = $request->input('page_limit');
            $page_number = $request->input('page_number');
            $keyword = $request->input('keyword');
            $status = $request->input('status');
            if (empty($job_id) || empty($page_limit) || empty($page_number)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = WorkConnection::getListApplicantByJobId($job_id, $page_limit, $page_number, $keyword, $status);
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
       *   path="/company/job/applicant/accept",
       *   summary="Connector's CV is accepted",
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
       *         name="note",
       *         in="formData",
       *         description="Reason to accept",
       *         required=false,
       *         type="string",
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
    public function accept(Request $request)
    {
        try {
            $work_connection_id = $request->input('work_connection_id');
            if (empty($work_connection_id)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $note = $request->input('note');
                $this->data = WorkConnection::edit($work_connection_id, 1, null, null, null, null, $note, null); //accept CV: status = 1;
                if($this->data) {
                    $info = WorkConnection::infoToNotify($this->data);
                    if($info) {
                       $content = "あなたのプロフィールは\"".$info->title."\"の仕事に採用されました。";
                        Notification::add($info->connector_id, $content, 1, $work_connection_id, $info->company_id);
                    }
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
       * @SWG\Post(
       *   path="/company/job/applicant/ignore",
       *   summary="Dot accept CV or interview failed",
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
       *          name="api_token",
       *          in="formData",
       *          required=true,
       *          description="API token",
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
    public function ignore(Request $request)
    {
        try {
            $work_connection_id = $request->input('work_connection_id');
            if (empty($work_connection_id)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $status = WorkConnection::getStatusApplicant($work_connection_id);
                $this->data = WorkConnection::edit($work_connection_id, -1, null, null, null, null, null, null);// not accept: status = -1;
                if($this->data) {
                    $info = WorkConnection::infoToNotify($this->data);
                    if($info) {
                        $content = "";
                        if ($status == 0) {
                            $content = "あなたのプロフィールは\"".$info->title."\"作品には受け入れられていません";
                            Notification::add($info->connector_id, $content, 2, $work_connection_id, $info->company_id);
                        }
                        if ($status == 1) {
                            $content = "あなたは\"".$info->title."\"の仕事をするために募集されていません";
                            Notification::add($info->connector_id, $content, 2, $work_connection_id, $info->company_id);
                        }
                    }
                }
                $this->success = true;
                $this->error = null;
            }
        } catch (\Illuminate\Database\QueryException $ex) {
            $this->success = false;
            \Log::error("[".__METHOD__."][".__LINE__."]"."error:".$ex->getMessage());
            $this->error = $ex->getMessage();
        } catch (\Illuminate\Exception $ex) {
            $this->success = false;
            \Log::error("[".__METHOD__."][".__LINE__."]"."error:".$ex->getMessage());
            $this->error = $ex->getMessage();
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
        * @SWG\Post(
        *   path="/company/job/applicant/recruit",
        *   summary="Recruit applicant",
        *    tags={"Company"},
         *     @SWG\Parameter(
        *         name="work_connection_id",
        *         in="formData",
        *         description="work_connection Id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="recruited_time_by_company",
        *         in="formData",
        *         description="recruited_time_by_company",
        *         required=false,
        *         type="string",
        *         format ="date",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="recruitment_reason",
        *         in="formData",
        *         description="recruitment_reason",
        *         required=false,
        *         type="string",
        *         default="",
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
    public function recruit(Request $request)
    {
        $this->data = null;
        try {
            $work_connection_id = $request->input('work_connection_id');
            $recruited_time_by_company = $request->input('recruited_time_by_company');
            $recruitment_reason = $request->input('recruitment_reason');
            if (empty($work_connection_id)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                if(strtotime($recruited_time_by_company)){
                    $this->data = WorkConnection::edit($work_connection_id, 2, null, $recruited_time_by_company, null, null, null, $recruitment_reason);//is recruited: status = 2;
                    if (!empty($this->data)) {
                        $info_work_connection = WorkConnection::detailWorkConnection($work_connection_id);
                        $name_of_friend = Connector::getNameById($info_work_connection['connector_id']);
                        $info_company = Company::getCompanyByJobId($info_work_connection['job_id']);
                        $content = $name_of_friend . ' さんが、あなたのコードで' . $info_company['company_name'] . '店に採用されました。';
                        if (!empty($info_work_connection['introduction_id'])) {
                            IntroductionStatus::add($info_work_connection['connector_id'], $info_work_connection['introduction_id'], $info_work_connection['job_id'], 3, 4500, $content, 0);
                        }
                        Payment::add($info_work_connection['connector_id'], $info_company['id'], $info_work_connection['job_id'], 2, $content, 3000, 0);
                        Payment::add($info_work_connection['introduction_id'], $info_company['id'], $info_work_connection['job_id'], 1, $content, 4500, 0);
    
                        $info = WorkConnection::infoToNotify($this->data);
                        if($info) {
                           $content = "あなたは\"".$info->title."\"の仕事のために募集されています。";
                           Notification::add($info->connector_id, $content, 3, $work_connection_id, $info->company_id);
                        }
                    }
                    $this->success = true;
                    $this->error = null;
                }
                else{
                    $this->error = \Lang::get('common_message.error.DATE_INCORRECT_FORMAT');
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
        * @SWG\Post(
        *   path="/company/job/applicant/report",
        *   summary="Report applicant",
        *    tags={"Company"},
         *     @SWG\Parameter(
        *         name="work_connection_id",
        *         in="formData",
        *         description="Work connection Id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="report_by_company",
        *         in="formData",
        *         description="report type (1 is Receive, 2 is Dismiss, 3 is Resign yourself)",
        *         required=true,
        *         type="number",
        *         default="",
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
    public function report(Request $request)
    {
        try {
            $work_connection_id = $request->input('work_connection_id');
            $report_by_company = $request->input('report_by_company');
            if (empty($work_connection_id) || empty($report_by_company)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = WorkConnection::edit($work_connection_id, 3, null, null, null, $report_by_company, null, null);//is report: status = 3;
                if ($this->data) {
                    $info_work_connection = WorkConnection::detailWorkConnection($work_connection_id);
                    $name_of_friend = Connector::getNameById($info_work_connection['connector_id']);
                    $info_company = Company::getCompanyByJobId($info_work_connection['job_id']);

                    $content = $name_of_friend . 'さんの作業報告';
                    Payment::add($info_work_connection['connector_id'], $info_company['id'], $info_work_connection['job_id'], 2, $content, 20000, 0);
                    Payment::add($info_work_connection['connector_id'], $info_company['id'], $info_work_connection['job_id'], 1, $content, 3000, 0);
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
        * @SWG\Post(
        *   path="/company/job/applicant/bonus",
        *   summary="Nonus applicant",
        *    tags={"Company"},
         *     @SWG\Parameter(
        *         name="work_connection_id",
        *         in="formData",
        *         description="Work connection Id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="amount",
        *         in="formData",
        *         description="Amount",
        *         required=true,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="content",
        *         in="formData",
        *         description="Content",
        *         required=false,
        *         type="string",
        *         default="",
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
    public function bonus(Request $request)
    {
        try {
            $work_connection_id = $request->input('work_connection_id');
            $amount = $request->input('amount');
            $content = $request->input('content');
            if (empty($work_connection_id) || empty($amount)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $info_work_connection = WorkConnection::detailWorkConnection($work_connection_id);
                if (!empty($info_work_connection['connector_id'])) {
                    // $this->data = IntroductionStatus::add($job_id, $connector_id, 0, $introduction_id, 3);
                    IntroductionStatus::add(null, $info_work_connection['connector_id'], $info_work_connection['job_id'], 4, $amount, $content, 1);
                    Payment::add($info_work_connection['connector_id'], $info_work_connection['company_id'], $info_work_connection['job_id'], 3, $content, $amount, 1);
                    $edit = WorkConnection::edit($work_connection_id, null, null, null, null, null, null, null);
                    if($edit) {
                        $info = WorkConnection::infoToNotify($edit);
                        if($info && $info->company_id) {
                           $content = $info->company_name."カンパニーがあなたに報いました";
                           Notification::add($info->connector_id, $content, 4, $work_connection_id, $info->company_id);
                        }
                    }
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

    /**
     * @SWG\Get(
     *   path="/admin/job/applicant/detail/{connector_id}?api_token={api_token}",
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
     *         description="Api token of Connector",
     *         required=true,
     *         type="string",
     *         default="YccJm3KSKJcRA7gP3Uh3haNiVLm9ei1EyYeRkUXkojlpkwoC5Wi8KmOzsXQl",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {
                                                        &emsp;               'id': ,
                                                        &emsp;               'username': ,
                                                        &emsp;               'email': ,
                                                        &emsp;               'phone_number': ,
                                                        &emsp;               'birthday': ,
                                                        &emsp;               'gender': ,
                                                        &emsp;               'connector_code': ,
                                                        &emsp;         },
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
            if (empty($connector_id)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Connector::getApplicantById($connector_id);
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
     *   path="/company/job/applicant/delete/{id}",
     *   summary="Delete applicant",
     *    tags={"Company"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="work_connection_id",
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
    public function delete($work_connection_id)
    {
        try {
            if (empty($work_connection_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $status = WorkConnection::getStatusApplicant($work_connection_id);
                $info = WorkConnection::infoToNotify($work_connection_id);
                $this->data = WorkConnection::deleteApplicant($work_connection_id);
                if ($this->data) {
                    if($info) {
                        $content = "";
                        if ($status == 0) {
                            $content = "あなたのプロフィールは\"".$info->title."\"ジョブから削除されました";
                            Notification::add($info->connector_id, $content, 5, $work_connection_id, $info->company_id);
                        }
                        if ($status == 1) {
                            $content = "あなたは\"".$info->title."\"求人応募リストから削除されました";
                            Notification::add($info->connector_id, $content, 5, $work_connection_id, $info->company_id);
                        }
                    }
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
        * @SWG\Get(
        *   path="/company/job/applicant/list?job_id={job_id}&page_limit={page_limit}&page_number={page_number}&keyword={keyword}&phone_number={phone_number}&api_token={api_token}",
        *   summary="Get list applicant by staff",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="job_id",
        *         in="path",
        *         description="Job Id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="page_limit",
        *         in="path",
        *         description="Limit number in one page",
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
        *         description="keyword",
         *        required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *         name="status",
        *         in="path",
        *         description="Status of applicant",
        *         required=false,
        *         type="number",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *          name="api_token",
        *          in="path",
        *          required=true,
        *          description="API token of Company",
        *          type="string",
        *          default="UnJlBBetMaOEJRpTcZDS1yGnG34MD94CKD3FxDFHPkT808WYbHjAhgKMHJEn",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {
                                                        &emsp;      'total': ,
                                                        &emsp;      'data': [{
                                                        &emsp;           'id': ,
                                                        &emsp;           'job_id': ,
                                                        &emsp;           'connector_id': ,
                                                        &emsp;           'introduction_id': ,
                                                        &emsp;           'status': ,
                                                        &emsp;           'apply_date': ,
                                                        &emsp;           'is_new': ,
                                                        &emsp;           'username': ,
                                                        &emsp;           'email': ,
                                                        &emsp;           'phone_number': ,
                                                        &emsp;           'birthday': ,
                                                        &emsp;           'gender': ,
                                                        &emsp;           'chat_id': 
                                                        &emsp;         },
                                                        &emsp;         ...
                                                        &emsp;      ]
                                                        &emsp;   }|...,
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
            $job_id = $request->input('job_id');
            $page_limit = $request->input('page_limit');
            $page_number = $request->input('page_number');
            if (empty($job_id) || empty($page_limit) || empty($page_number)) {
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
                    $keyword = $request->input('keyword');
                    $status = $request->input('status');
                    $this->data = WorkConnection::getByStaff($job_id, $page_limit, $page_number, $keyword, $status);
                    $this->success = true;
                } else {
                    $this->error = \Lang::get('common_message.error.OBJECT_NOT_EXIST');
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
        *   path="/company/job/applicant/new?page_limit={page_limit}&page_number={page_number}&keyword={keyword}&phone_number={phone_number}&api_token={api_token}",
        *   summary="Get list new applicants by staff",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="page_limit",
        *         in="path",
        *         description="Limit number in one page",
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
        *         description="keyword",
         *        required=false,
        *         type="string",
        *         default="",
        *     ),
        *     @SWG\Parameter(
        *          name="api_token",
        *          in="path",
        *          required=true,
        *          description="API token of Company",
        *          type="string",
        *          default="UnJlBBetMaOEJRpTcZDS1yGnG34MD94CKD3FxDFHPkT808WYbHjAhgKMHJEn",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {
                                                        &emsp;      'data': [{
                                                        &emsp;           'id': ,
                                                        &emsp;           'job_id': ,
                                                        &emsp;           'connector_id': ,
                                                        &emsp;           'status': ,
                                                        &emsp;           'is_new': ,
                                                        &emsp;           'apply_date': ,
                                                        &emsp;           'username': ,
                                                        &emsp;           'email': ,
                                                        &emsp;           'phone_number': ,
                                                        &emsp;           'birthday': ,
                                                        &emsp;           'gender': ,
                                                        &emsp;           'company_id': ,
                                                        &emsp;           'job_title': ,
                                                        &emsp;           'chat_id': ,
                                                        &emsp;         },
                                                        &emsp;         ...
                                                        &emsp;      ],
                                                        &emsp;      'total': 7
                                                        &emsp;   }|...,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function getNewByStaff(Request $request)
    {
        try {
            $page_limit = $request->input('page_limit');
            $page_number = $request->input('page_number');
            if (empty($page_limit) || empty($page_number)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $keyword = $request->input('keyword');
                $company_id = 0;
                if (Auth::guest()) {
                    $user = Auth::guard('staff-api')->user();
                    $company_id = $user->company_id;
                }
                if ($company_id) {
                    $this->data = WorkConnection::getNewByStaff($company_id, $page_limit, $page_number, $keyword);
                    $this->success = true;
                } else {
                    $this->error = \Lang::get('common_message.error.QUERY_FAIL');
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

    public function detailJob($job_id)
    {
        $this->data = array();
        try {
            $check = Job::checkExistJob($job_id);
            if ($check) {
                $this->data = Job::detailForApplicant($job_id);
                $this->success = true;
            } else {
                $this->success = false;
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

    public function updateIsNew($id)
    {
        $this->data = array();
        try {
            $check = WorkConnection::checkWorkConnection($id);
            if ($check) {
                $this->data = WorkConnection::updateIsNew($id);
                $this->success = true;
            } else {
                $this->success = false;
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

    /**
     * @SWG\Get(
     *   path="/company/job/applicant/detail/{id}?api_token={api_token}",
     *   summary="Get info Work connection",
     *    tags={"Company"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id of Work appliction",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="Api token of Company",
     *         required=true,
     *         type="string",
     *         default="YccJm3KSKJcRA7gP3Uh3haNiVLm9ei1EyYeRkUXkojlpkwoC5Wi8KmOzsXQl",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {
                                                        &emsp;      'id': ,
                                                        &emsp;      'connector_id': ,
                                                        &emsp;      'introduction_id': ,
                                                        &emsp;      'job_id': ,
                                                        &emsp;      'status': ,
                                                        &emsp;      'recruited_time_by_connector': ,
                                                        &emsp;      'recruited_time_by_company': ,
                                                        &emsp;      'report_by_connector': ,
                                                        &emsp;      'report_by_company': ,
                                                        &emsp;      'note': ,
                                                        &emsp;      'recruitment_reason': ,
                                                        &emsp;      'is_new': ,
                                                        &emsp;      'created_at': ,
                                                        &emsp;      'updated_at': ,
                                                        &emsp;      'username': ,
                                                        &emsp;      'email': ,
                                                        &emsp;      'phone_number': ,
                                                        &emsp;      'birthday': ,
                                                        &emsp;      'gender': 
                                                        &emsp;   } | ...,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function detailWorkConnection($id)
    {
        $this->data = array();
        try {
            $check = WorkConnection::checkWorkConnection($id);
            if ($check) {
                $this->data = WorkConnection::detailWorkConnection($id);
                $this->success = true;
            } else {
                $this->success = false;
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

    public function countNew()
    {
        try {
            $company_id = 0;
            if (Auth::guest()) {
                $user = Auth::guard('staff-api')->user();
                $company_id = $user->company_id;
            }
            if ($company_id) {
                $this->data = WorkConnection::countNew($company_id);
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
            $this->error = $ex->getMessage();
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }
}