<?php
namespace App\Http\Controllers;

use App\IntroductionStatus;
use App\Connector;
use App\Company;
use App\Payment;
use Illuminate\Http\Request;

class IntroductionStatusController extends Controller
{
    private $success = false;
    private $data = null;
    private $error = null;
    /**
        * @SWG\Get(
        *   path="/connector/introduction-status?connector_id={connector_id}&page_number={page_number}&page_limit={page_limit}&api_token={api_token}",
        *   summary="Get list introduction status",
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
                                                        &emsp;  'data':{
                                                        &emsp;&emsp;        'data': [{
                                                        &emsp;&emsp;&emsp;        'id': ,
                                                        &emsp;&emsp;&emsp;        'connector_id': ,
                                                        &emsp;&emsp;&emsp;        'job_title': ,
                                                        &emsp;&emsp;&emsp;        'job_id': ,
                                                        &emsp;&emsp;&emsp;        'status': ,
                                                        &emsp;&emsp;&emsp;        'type': ,
                                                        &emsp;&emsp;&emsp;        'introduction_id':,
                                                        &emsp;&emsp;&emsp;        'introduction_code':,
                                                        &emsp;&emsp;&emsp;        'type':,
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
            $connector_id = $request->input('connector_id');
            $page_number = $request->input('page_number');
            $page_limit = $request->input('page_limit');
            if (empty($connector_id) || empty($page_number) || empty($page_limit)) {
                $error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = IntroductionStatus::getListIntroductionStatus($connector_id, $page_number, $page_limit);
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
        *   path="/connector/request",
        *   summary="Connector request for tranfer money",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="introduction_status_id",
        *         in="formData",
        *         description="Introduction Status Id",
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
    public function request(Request $request)
    {
        try {
            $introduction_status_id = $request->input('introduction_status_id');
            if (empty($introduction_status_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $introduction_status_info = IntroductionStatus::find($introduction_status_id);
                if (!empty($introduction_status_info)) {
                    if ($introduction_status_info['type'] == 1) {
                        $name_of_friend = Connector::getNameById($introduction_status_info['connector_id']);
                        $content = $name_of_friend . ' があなたのコードでアカウントを登録しました。';
                        Payment::add($introduction_status_info['introduction_id'], null, null, 1, $content, 100, 0);
                    } elseif ($introduction_status_info['type'] == 3) {
                        $name_of_friend = Connector::getNameById($introduction_status_info['connector_id']);
                        $info_company = Company::getCompanyByJobId($introduction_status_info['job_id']);
                        $content = $name_of_friend . ' さんが、あなたのコードで' . $info_company['company_name'] . '店に採用されました。';
                        Payment::add($introduction_status_info['introduction_id'], $info_company['company_id'], $introduction_status_info['job_id'], 1, $content, 100, 0);
                    }
                }
                $introduction_status = IntroductionStatus::edit($introduction_status_id, 1);//status = 1 when connector request payment;
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
}
