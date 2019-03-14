<?php
namespace App\Http\Controllers;

use App\Company;
use App\Payment;
use App\IntroductionStatus;
use App\CreditCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    private $success = false;
    private $data = null;
    private $error = null;

    /**
     * @SWG\Get(
     *   path="/connector/payment/list?connector_id={connector_id}&status={status}&page_number={page_number}&page_limit={page_limit}&api_token={api_token}",
     *   summary="Get list of payment",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="connector_id",
     *         in="path",
     *         description="Connector id",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="page_number",
     *         in="path",
     *         description="Number page",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="page_limit",
     *         in="path",
     *         description="Number Item",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         in="path",
     *         description="Status",
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
     *         default="U1n8N6yLQbDNhQL0Gl3y1WXHV0fp9XOudgpGcYQy14PGVfV0LMemtMegto6m",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {
                                                        &emsp;      'total_items': ,
                                                        &emsp;      'data': [
                                                        &emsp;          {
                                                        &emsp;               'id': ,
                                                        &emsp;               'connector_id': ,
                                                        &emsp;               'company_id': ,
                                                        &emsp;               'job_id': ,
                                                        &emsp;               'content': ,
                                                        &emsp;               'amount': ,
                                                        &emsp;               'created_at': ,
                                                        &emsp;               'updated_at':
                                                        &emsp;         },
                                                        &emsp;         {},…
                                                        &emsp;      ]
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
    public function getListPaymentByConnectorId(Request $request)
    {
        try {
            $connector_id = $request->input('connector_id');
            $page_number = $request->input('page_number');
            $page_limit = $request->input('page_limit');
            $status = $request->input('status');
            if (empty($connector_id) || empty($page_number) || empty($page_limit) || !isset($status)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Payment::getListPaymentByConnectorId($connector_id, $status, $page_number, $page_limit);
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
     *   path="/admin/payment/list?api_token={api_token}&date_begin={date_begin}&date_end={date_end}&company_name={company_name}&type=1&per_page={per_page}&page={page}",
     *   summary="Transfer Search",
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
     *     ),
     *     @SWG\Parameter(
     *         name="connector_name",
     *         in="path",
     *         description="Connector name",
     *         type="string",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="per_page",
     *         in="path",
     *         description="Per page",
     *         type="number",
     *         default="4"
     *     ),
     *     @SWG\Parameter(
     *         name="page",
     *         in="path",
     *         description="Page",
     *         type="string",
     *         default="1"
     *     ),
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="API token",
     *         required=true,
     *         type="string",
     *         default="1xR3RwLmzhn2QvOcasYePwm1ztoTNJQdyJgXSf9AmjSleUOKgpzTpOluTT8j",
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
    /**
     * @SWG\Get(
     *   path="/admin/payment/list?api_token={api_token}&date_begin={date_begin}&date_end={date_end}&company_name={company_name}&type=2&per_page={per_page}&page={page}",
     *   summary="Payment Search",
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
     *     ),
     *     @SWG\Parameter(
     *         name="company_name",
     *         in="path",
     *         description="Company name",
     *         type="string",
     *         default=""
     *     ),
     *     @SWG\Parameter(
     *         name="per_page",
     *         in="path",
     *         description="Per page",
     *         type="number",
     *         default="4"
     *     ),
     *     @SWG\Parameter(
     *         name="page",
     *         in="path",
     *         description="Page",
     *         type="string",
     *         default="1"
     *     ),
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="API token",
     *         required=true,
     *         type="string",
     *         default="1xR3RwLmzhn2QvOcasYePwm1ztoTNJQdyJgXSf9AmjSleUOKgpzTpOluTT8j",
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
            'date_begin' => 'date|nullable|before:tomorrow',
            'date_end' => 'date|nullable|before:tomorrow',
            'company_name' => 'string|nullable|max:255',
            'connector_name' => 'string|nullable|max:255',
            'type' => 'required|numeric|max:255',
            'per_page' => 'numeric|nullable|max:50',

        ]);
        if ($validator->fails() || ($request->has('date_end') && $request->has('date_begin') && $request->date_end != "" && $request->date_begin != "" && ((new \DateTime($request->date_begin))->format('U') > (new \DateTime($request->date_end))->format('U')))) {
            $this->error = $validator->errors();
            $this->success = false;
        } else {
            if ($request->type == 2) {
                $this->success = true;
                $result = DB::table('payments')->join('companies', function ($query) use ($request) {
                    $query->on('payments.company_id', '=', 'companies.id');
                    if ($request->has('company_name') && $request->company_name != "") {
                        $query->where('companies.company_name', 'like', "%$request->company_name%");
                    }
                    if (($request->has('date_end') && $request->date_end != "" && $request->date_end != "undefined") && ($request->has('date_begin') && $request->date_begin != "" && $request->date_begin != "undefined")) {
                        $query->where('payments.created_at', '<', (new \DateTime($request->date_end))->modify('+1 day')->format('U'));
                        $query->where('payments.created_at', '>=', (new \DateTime($request->date_begin))->format('U'));
                    }
//                    if ($request->has('date_begin') && $request->date_begin != "" && $request->date_begin != "undefined") {
//                        $query->where('payments.created_at', '>=', (new \DateTime($request->date_begin))->format('U'));
//                    }
                })->select('payments.id', 'payments.created_at', 'companies.company_name', 'payments.content', 'payments.amount', 'payments.status')
                    ->where('type', $request->type)
                    ->paginate(($request->has('per_page') ? $request->per_page : 15));
            } elseif ($request->type == 1) {
                $this->success = true;
                $result = DB::table('payments')->join('connectors', function ($query) use ($request) {
                    $query->on('payments.connector_id', '=', 'connectors.id');
                    if ($request->has('connector_name') && $request->connector_name != "") {
                        $query->where('connectors.username', 'like', "%$request->connector_name%");
                    }
                    if (($request->has('date_end') && $request->date_end != "" && $request->date_end != "undefined") && ($request->has('date_begin') && $request->date_begin != "" && $request->date_begin != "undefined")) {
                        $query->where('payments.created_at', '<', (new \DateTime($request->date_end))->modify('+1 day')->format('U'));
                        $query->where('payments.created_at', '>=', (new \DateTime($request->date_begin))->format('U'));
                    }
//                    if ($request->has('date_end') && $request->date_end != "" && $request->date_end != "undefined") {
//                        $query->where('payments.created_at', '<', (new \DateTime($request->date_end))->modify('+1 day')->format('U'));
//                    }
//                    if ($request->has('date_begin') && $request->date_begin != "" && $request->date_begin != "undefined") {
//                        $query->where('payments.created_at', '>=', (new \DateTime($request->date_begin))->format('U'));
//                    }
                })->select('payments.id', 'payments.created_at', 'connectors.username as connector_name', 'payments.content', 'payments.amount', 'payments.status')
                    ->where('type', $request->type)
                    ->paginate(($request->has('per_page') ? $request->per_page : 15));
            } else {
                $this->success = false;
            }
            $this->data = $result;
        }
        return $this->doResponse($this->success, $this->data, $this->error);
    }

    /**
     * @SWG\Post(
     *   path="/connector/request/all",
     *   summary="Connector request for tranfer money",
     *    tags={"App"},
     *     @SWG\Parameter(
     *         name="connector_id",
     *         in="formData",
     *         description="connector_id Id",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *          name="amount",
     *          in="formData",
     *          required=false,
     *          description="amount",
     *          type="number",
     *          default="3000",
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
    public function requestAll(Request $request)
    {
        try {
            $connector_id = $request->input('connector_id');
            $amount = $request->input('amount');
            if (empty($connector_id) || empty($amount)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $content = 'Request All';
                $this->data = Payment::add($connector_id, null, null, 1, $content, $amount, 0);
                if (!empty($this->data)) {
                    IntroductionStatus::edit_status_by_introduction_id($connector_id);//status = 1 when connector request payment;
                }
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
     *   path="/company/payment/list?company_id={company_id}&page_limit={page_limit}&page_number={page_number}&keyword={keyword}&date_create_start={date_create_start}&date_create_end={date_create_end}&api_token={api_token}",
     *   summary="Get list payment by staff",
     *    tags={"Company"},
     *     @SWG\Parameter(
     *         name="company_id",
     *         in="path",
     *         description="Company id",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="page_limit",
     *         in="path",
     *         description="Limit payment in one page",
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
     *         description="Keyword to search",
     *         required=false,
     *         type="string",
     *         default="",
     *     ),
     *     @SWG\Parameter(
     *         name="date_create_start",
     *         in="path",
     *         description="Date create start to search",
     *         required=false,
     *         type="string",
     *         default="",
     *     ),
     *     @SWG\Parameter(
     *         name="date_create_end",
     *         in="path",
     *         description="Date create end to search",
     *         required=false,
     *         type="string",
     *         default="",
     *     ),
     *     @SWG\Parameter(
     *         name="api_token",
     *         in="path",
     *         description="Api token of Connector",
     *         required=true,
     *         type="string",
     *         default="UnJlBBetMaOEJRpTcZDS1yGnG34MD94CKD3FxDFHPkT808WYbHjAhgKMHJEn",
     *     ),
     *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
    &emsp;  'data': {
    &emsp;          'total_item': ,
    &emsp;          'data': [
    &emsp;              {
    &emsp;                 'id': ,
    &emsp;                 'status': ,
    &emsp;                  'amount': ,
    &emsp;                  'expire': ,
    &emsp;                  'content': ,
    &emsp;                  'connector_username': ,
    &emsp;                  'created_at': ,
    &emsp;               },
    &emsp;               ...
    &emsp;          ]
    &emsp;   }
    &emsp;  'error': null | ...</pre>}
    "),
     *   @SWG\Response( response=404, description="404 page"),
     * )
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPaymentByCompany(Request $request)
    {
        try {
            $company_id = $request->input('company_id');
            $page_limit = $request->input('page_limit');
            $page_number = $request->input('page_number');

            if (empty($company_id) || empty($page_limit) || empty($page_number)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $keyword = $request->input('keyword');
                $date_create_start = $request->input('date_create_start');
                $date_create_end = $request->input('date_create_end');
                if ((!empty($date_create_start) && !strtotime($date_create_start)) || (!empty($date_create_end && !strtotime($date_create_end)))) {
                    $this->error = \Lang::get('common_message.error.DATE_NOT_INCORRECT');
                } else {
                    $this->data = Payment::listPaymentByCompany($company_id, $keyword, $date_create_start, $date_create_end, $page_number, $page_limit);
                    $this->success = true;
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
     *   path="/connector/compensation/history?connector_id={connector_id}&page_number={page_number}&page_limit={page_limit}&api_token={api_token}",
     *   summary="Get list compensation history",
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
     *         name="page_number",
     *         in="path",
     *         description="Current page",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="page_limit",
     *         in="path",
     *         description="Limit payment in one page",
     *         required=true,
     *         type="number",
     *         default="5",
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
                                                        &emsp; 'data':{
                                                        &emsp;      'total_items': ,
                                                        &emsp;      'data': [
                                                        &emsp;          {
                                                        &emsp;              'id': ,
                                                        &emsp;              'status': ,
                                                        &emsp;              'type': ,
                                                        &emsp;              'amount': ,
                                                        &emsp;              'content': ,
                                                        &emsp;              'created_at': ,
                                                        &emsp;              'id_friend': ,
                                                        &emsp;              'username': ,
                                                        &emsp;              'avatar': ,
                                                        &emsp;              'company_name': ,
                                                        &emsp;              'company_id': ,
                                                        &emsp;              'title': ,
                                                        &emsp;              'base_path_connector':
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
    public function getListCompensationHistory(Request $request)
    {
        try {
            $connector_id = $request->input('connector_id');
            $page_limit = $request->input('page_limit');
            $page_number = $request->input('page_number');
            if (empty($connector_id) || empty($page_limit) || empty($page_number)) {
                $error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = IntroductionStatus::getListCompensationOccurrenceHistory($connector_id, $page_number, $page_limit);
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
     *   path="/connector/request/list?connector_id={connector_id}&page_number={page_number}&page_limit={page_limit}&api_token={api_token}",
     *   summary="Get list request",
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
     *         name="page_number",
     *         in="path",
     *         description="Current page",
     *         required=true,
     *         type="number",
     *         default="1",
     *     ),
     *     @SWG\Parameter(
     *         name="page_limit",
     *         in="path",
     *         description="Limit payment in one page",
     *         required=true,
     *         type="number",
     *         default="5",
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
                                                        &emsp; 'data':{
                                                        &emsp;      'total_items': ,
                                                        &emsp;      'data': [
                                                        &emsp;          {
                                                        &emsp;              'id': ,
                                                        &emsp;              'connector_id': ,
                                                        &emsp;              'company_id': ,
                                                        &emsp;              'job_id': ,
                                                        &emsp;              'content': ,
                                                        &emsp;              'amount': ,
                                                        &emsp;              'status': ,
                                                        &emsp;              'status': ,
                                                        &emsp;              'created_at': ,
                                                        &emsp;              'updated_at':
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
    public function getListRequest(Request $request)
    {
        try {
            $connector_id = $request->input('connector_id');
            $page_limit = $request->input('page_limit');
            $page_number = $request->input('page_number');
            if (empty($connector_id) || empty($page_limit) || empty($page_number)) {
                $error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Payment::getListRequestByConnectorId($connector_id, $page_number, $page_limit);
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
     *   path="/company/transfer",
     *   summary="Company transfer money",
     *    tags={"Company"},
     *     @SWG\Parameter(
     *         name="payment_id",
     *         in="formData",
     *         description="ID of company",
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

    /**
     * @SWG\Post(
     *   path="/admin/transfer",
     *   summary="Admin transfer money",
     *    tags={"Admin"},
     *     @SWG\Parameter(
     *         name="payment_id",
     *         in="formData",
     *         description="ID of company",
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
    public function transfer(Request $request)
    {
        try {
            $payment_id = $request->input('payment_id');
            if (empty($payment_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Payment::updateStatus($payment_id, 1);
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
     *   path="/admin/payment/delete/{id}?api_token={api_token}",
     *   summary="Payment Delete",
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
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|exists:payments',
            ]);
            if ($validator->fails()) {
                $this->error = $validator->errors();
                $this->success = false;
            } else {
                Payment::where('id', $id)->delete();
                $this->data = $id;
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
     *   path="/admin/payment/edit/{id}",
     *   summary="Payment Update Status",
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
     *          name="status",
     *          in="formData",
     *          required=true,
     *          description="Status",
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
                'status' => 'required|max:1|min:0|numeric',
            ]);
            if ($validator->fails()) {
                $this->error = $validator->errors();
                $this->success = false;
            } else {
                Payment::where('id', $id)->update([
                    'status' => $request->status
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
}
