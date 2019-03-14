<?php
namespace App\Http\Controllers;

use App\Chat;
use App\ChatHistory;
use App\WorkConnection;
use App\CompanyFile;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private $success = false;
    private $data = null;
    private $error = null;

    /**
        * @SWG\Get(
        *   path="/connector/chat/list?connector_id={connector_id}&keyword={keyword}&page_limit={page_limit}&page_number={page_number}&api_token={api_token}",
        *   summary="Get info Connector",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="connector_id",
        *         in="path",
        *         description="Id of Connector",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="page_limit",
        *         in="path",
        *         description="Limit in one page",
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
        *         description="Keyword",
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
        *         default="N6stipDS6KkBLGq3WAEK4uUL1ZwlEdQMm6BQT11fnhUfQOsy4BAamkWUDQbD",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': [
                                                        &emsp;&emsp;&emsp;{'id': 11,
                                                        &emsp;&emsp;&emsp;'staff_id': '1',
                                                        &emsp;&emsp;&emsp;'connector_id': '1',
                                                        &emsp;&emsp;&emsp;'job_id': '1',
                                                        &emsp;&emsp;&emsp;'del': '0',
                                                        &emsp;&emsp;&emsp;'created_at': 1544768121,
                                                        &emsp;&emsp;&emsp;'updated_at': 1544768121},
                                                        &emsp;&emsp;&emsp;}
                                                        &emsp;&emsp;&emsp;{},...] | null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function getListChatByConnectorId(Request $request)
    {
        try {
            $page_limit = $request->input('page_limit');
            $page_number = $request->input('page_number');
            $connector_id = $request->input('connector_id');
            $keyword = $request->input('keyword');
            if (empty($connector_id) || empty($page_limit) || empty($page_number)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Chat::getListChatByConnectorId($page_limit, $page_number, $connector_id, $keyword);
                foreach ($this->data ['data'] as $key => $value) {
                    if (!$this->data ['data'][$key]['avatar']) {
                        $this->data ['data'][$key]['avatar'] = "avatar_default.png";
                    }
                    $logo = CompanyFile::getMainImage($this->data ['data'][$key]['company_id']);
                    if (!$logo) {
                        $this->data ['data'][$key]['company_logo'] = "dummy-company.png";
                    } else {
                        $this->data ['data'][$key]['company_logo'] = $logo;
                    }
                }
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
        *   path="/company/chat/list?company_id={company_id}&api_token={api_token}",
        *   summary="Get info Connector",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="company_id",
        *         in="path",
        *         description="Id of Company",
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
        *         default="N6stipDS6KkBLGq3WAEK4uUL1ZwlEdQMm6BQT11fnhUfQOsy4BAamkWUDQbD",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp; 'data':{
                                                        &emsp;&emsp;'total_items': 4
                                                        &emsp;&emsp;'data': [{'id': 11,
                                                        &emsp;&emsp;&emsp;'connector_id': ,
                                                        &emsp;&emsp;&emsp;'username': ,
                                                        &emsp;&emsp;&emsp;'avatar': ,
                                                        &emsp;&emsp;&emsp;'job_title': ,
                                                        &emsp;&emsp;&emsp;'chat_history_id': ,
                                                        &emsp;&emsp;&emsp;'message_lastest': ,
                                                        &emsp;&emsp;&emsp;},
                                                        &emsp;&emsp;&emsp;{},...],
                                                        }
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function getListChatByCompanyId(Request $request)
    {
        try {
            $company_id = $request->input('company_id');
            if (empty($company_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Chat::getListChatByCompanyId($company_id);
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
        *   path="/company/chat/total-not-seen?company_id={company_id}&api_token={api_token}",
        *   summary="Get info Connector",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="company_id",
        *         in="path",
        *         description="Id of Company",
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
        *         default="N6stipDS6KkBLGq3WAEK4uUL1ZwlEdQMm6BQT11fnhUfQOsy4BAamkWUDQbD",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp; 'data':{
                                                        &emsp;&emsp;'total_items': 4
                                                        &emsp;&emsp;'data': [{'id': 11,
                                                        &emsp;&emsp;&emsp;'connector_id': ,
                                                        &emsp;&emsp;&emsp;'username': ,
                                                        &emsp;&emsp;&emsp;'avatar': ,
                                                        &emsp;&emsp;&emsp;'job_title': ,
                                                        &emsp;&emsp;&emsp;'chat_history_id': ,
                                                        &emsp;&emsp;&emsp;'message_lastest': ,
                                                        &emsp;&emsp;&emsp;},
                                                        &emsp;&emsp;&emsp;{},...],
                                                        }
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function getTotalNotSeen(Request $request)
    {
        try {
            $company_id = $request->input('company_id');
            if (empty($company_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Chat::getTotalNotSeen($company_id);
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
        *   path="/company/job/chat/detail?connector_id={connector_id}&job_id={job_id}&api_token={api_token}",
        *   summary="Get info Connector",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="connector_id",
        *         in="path",
        *         description="Id of Connector",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="job_id",
        *         in="path",
        *         description="Id of Job",
        *         required=true,
        *         type="number",
        *         default="2",
        *     ),
        *     @SWG\Parameter(
        *         name="api_token",
        *         in="path",
        *         description="Api token of Company",
        *         required=true,
        *         type="string",
        *         default="N6stipDS6KkBLGq3WAEK4uUL1ZwlEdQMm6BQT11fnhUfQOsy4BAamkWUDQbD",
        *     ),
         *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;'data': [
                                                        &emsp;&emsp;&emsp;{'id': 11,
                                                        &emsp;&emsp;&emsp;'chat_id': '1',
                                                        &emsp;&emsp;&emsp;'type': '1',
                                                        &emsp;&emsp;&emsp;'message': '....',
                                                        &emsp;&emsp;&emsp;'time': '1544066356',
                                                        &emsp;&emsp;&emsp;'order_no': '1544066356',
                                                        &emsp;&emsp;&emsp;'created_at': 1544768121,
                                                        &emsp;&emsp;&emsp;'updated_at': 1544768121},
                                                        &emsp;&emsp;&emsp;}
                                                        &emsp;&emsp;&emsp;{},...] | null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function getChatDetailByCompany(Request $request)
    {
        try {
            $connector_id = $request->input('connector_id');
            $job_id = $request->input('job_id');
            if (empty($connector_id) || empty($job_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $chat_id = Chat::getListChatIdByConnectorIdAndJobId($connector_id, $job_id);
                if ($chat_id != null) {
                    $this->data = ChatHistory::getListChatDetailByChatId($chat_id);
                }
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
        *   path="/company/chat/message-detail/{id}?api_token={api_token}",
        *   summary="Get chat detail message for company",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="id",
        *         in="path",
        *         description="chat id",
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
        *         default="N6stipDS6KkBLGq3WAEK4uUL1ZwlEdQMm6BQT11fnhUfQOsy4BAamkWUDQbD",
        *     ),
         *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;'data': [
                                                        &emsp;&emsp;&emsp;{'id': 11,
                                                        &emsp;&emsp;&emsp;'chat_id': '1',
                                                        &emsp;&emsp;&emsp;'type': '1',
                                                        &emsp;&emsp;&emsp;'message': '....',
                                                        &emsp;&emsp;&emsp;'time': '1544066356',
                                                        &emsp;&emsp;&emsp;'order_no': '1544066356',
                                                        &emsp;&emsp;&emsp;'created_at': 1544768121,
                                                        &emsp;&emsp;&emsp;'updated_at': 1544768121},
                                                        &emsp;&emsp;&emsp;}
                                                        &emsp;&emsp;&emsp;{},...] | null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function getChatMessageDetailById($chat_id)
    {
        try {
            if (empty($chat_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = ChatHistory::getListChatDetailByChatId($chat_id);
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
        *   path="/connector/chat/message-detail/{id}?page_limit={page_limit}&page_number={page_number}&api_token={api_token}",
        *   summary="Get chat detail  message for app",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="id",
        *         in="path",
        *         description="chat id",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
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
        *         description="Api token of Connector",
        *         required=true,
        *         type="string",
        *         default="N6stipDS6KkBLGq3WAEK4uUL1ZwlEdQMm6BQT11fnhUfQOsy4BAamkWUDQbD",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                       &emsp;'data': [
                                                       &emsp;&emsp;&emsp;{'id': 11,
                                                       &emsp;&emsp;&emsp;'chat_id': '1',
                                                       &emsp;&emsp;&emsp;'type': '1',
                                                       &emsp;&emsp;&emsp;'message': '....',
                                                       &emsp;&emsp;&emsp;'time': '1544066356',
                                                       &emsp;&emsp;&emsp;}
                                                       &emsp;&emsp;&emsp;{},...] | null,
                                                       &emsp;  'error': null | ...</pre>}
                                                       "),
       *   @SWG\Response( response=404, description="404 page"),
       * )
       *
       * Display a listing of the resource.
       *
       * @return \Illuminate\Http\Response
    */
    public function getChatMessageDetailByIdForApp($chat_id, Request $request)
    {
        $page_limit = $request->input('page_limit');
        $page_number = $request->input('page_number');
        try {
            if (empty($chat_id) || empty($page_limit) || empty($page_number)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = ChatHistory::getListChatDetailByChatIdForApp($page_limit, $page_number, $chat_id);
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
        *   path="/company/chat/detail/{id}?api_token={api_token}",
        *   summary="Get chat detail for company",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="id",
        *         in="path",
        *         description="chat id",
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
                                                        &emsp;'data': [
                                                        &emsp;&emsp;&emsp;{'id': 11,
                                                        &emsp;&emsp;&emsp;'chat_id': '1',
                                                        &emsp;&emsp;&emsp;'type': '1',
                                                        &emsp;&emsp;&emsp;'message': '....',
                                                        &emsp;&emsp;&emsp;'time': '1544066356',
                                                        &emsp;&emsp;&emsp;'order_no': '1544066356',
                                                        &emsp;&emsp;&emsp;'created_at': 1544768121,
                                                        &emsp;&emsp;&emsp;'updated_at': 1544768121},
                                                        &emsp;&emsp;&emsp;}
                                                        &emsp;&emsp;&emsp;{},...] | null,
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function getChatDetailById($chat_id)
    {
        try {
            if (empty($chat_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Chat::getDetailById($chat_id);
                if (!$this->data['avatar']) {
                    $this->data['avatar'] = "/assets/img/avatar_default.png";
                } else {
                    $this->data['avatar'] = Chat::getBasePath() . "/connector" . "/" . $this->data['avatar'];
                }
                $logo = CompanyFile::getMainImage($this->data['company_id']);
                if (!$logo) {
                    $this->data['company_logo'] = "/assets/img/dummy-company.png";
                } else {
                    $this->data['company_logo'] = Chat::getBasePath() . "/company" . "/" . $logo;
                }
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
        *   path="/company/job/chat/delete/{id}",
        *   summary="Delete chat",
        *    tags={"Company"},
        *     @SWG\Parameter(
        *         name="id",
        *         in="path",
        *         description="Id of Chat",
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
        *   path="/connector/chat/delete/{id}",
        *   summary="Delete chat",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="id",
        *         in="path",
        *         description="Id of Chat",
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
            if (empty($id)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Chat::deleteById($id);
                if ($this->data) {
                    $this->success = true;
                    $this->error = null;
                } else {
                    $this->success = false;
                    $this->error = "Delete chat fail!";
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
        *   path="/company/chat/update-message-status/{chat_id}",
        *   summary="Update message status",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="chat_id",
        *         in="path",
        *         description="Id of Chat",
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
    public function updateMessageStatus($chat_id)
    {
        try {
            if (empty($chat_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = ChatHistory::updateMessageStatus($chat_id);
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
        *   path="/company/chat/update-note",
        *   summary="Update note",
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
        *         name="job_id",
        *         in="formData",
        *         description="Id of Job",
        *         required=true,
        *         type="number",
        *         default="1",
        *     ),
        *     @SWG\Parameter(
        *         name="note",
        *         in="formData",
        *         description="Note content",
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
    public function updateNote(Request $request)
    {
        $connector_id = $request->input('connector_id');
        $job_id = $request->input('job_id');
        $note = $request->input('note');
        try {
            if (empty($connector_id) || empty($job_id)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $id_work_connection = WorkConnection::getIdByConnectorIdAndJobId($connector_id, $job_id);
                if ($id_work_connection) {
                    $this->data = WorkConnection::edit($id_work_connection, null, null, null, null, null, $note, null);
                }
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
        *   path="/connector/chat/infor-extend/{id}?api_token={api_token}",
        *   summary="Get info extend Chat",
        *    tags={"App"},
        *     @SWG\Parameter(
        *         name="id",
        *         in="path",
        *         description="Id of Chat",
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
                                                        &emsp;  'data':
                                                        &emsp;&emsp;'id':
                                                        &emsp;&emsp;&emsp;'connector_id': ,
                                                        &emsp;&emsp;&emsp;'company_id': ,
                                                        &emsp;&emsp;&emsp;'company_name': ,
                                                        &emsp;&emsp;&emsp;'company_phone_number': ,
                                                        &emsp;&emsp;&emsp;'job_id': ,
                                                        &emsp;&emsp;&emsp;'username': ,
                                                        &emsp;&emsp;&emsp;'avatar': ,
                                                         &emsp;&emsp;&emsp;'company_logo': ,
                                                        &emsp;&emsp;&emsp;},
                                                        &emsp;  'error': null | ...</pre>}
                                                        "),
        *   @SWG\Response( response=404, description="404 page"),
        * )
        *
        * Display a listing of the resource.
        *
        * @return \Illuminate\Http\Response
    */
    public function getChatInforExtendById($id)
    {
        try {
            if (empty($id)) {
                $this->success = false;
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Chat::getChatInforExtendById($id);
                if (!$this->data['avatar']) {
                    $this->data['avatar'] = Chat::getBasePath() . "/connector" . "/" . "avatar_default.png";
                } else {
                    $this->data['avatar'] = Chat::getBasePath() . "/connector" . "/" . $this->data['avatar'];
                }
                $logo = CompanyFile::getMainImage($this->data['company_id']);
                if (!$logo) {
                    $this->data['company_logo'] = Chat::getBasePath() . "/company" . "/" . "dummy-company.png";
                } else {
                    $this->data['company_logo'] = Chat::getBasePath() . "/company" . "/" . $logo;
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
}
