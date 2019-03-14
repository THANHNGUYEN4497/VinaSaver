<?php
namespace App\Http\Controllers;

use App\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    private $success = false;
    private $data = null;
    private $error = null;

    /**
        * @SWG\Get(
        *   path="/connector/notification/list?connector_id={connector_id}&page_limit={page_limit}&page_number={page_number}&api_token={api_token}",
        *   summary="Get lis notification by connector",
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
        *          name="api_token",
        *          in="path",
        *          required=true,
        *          description="API token of Admin",
        *          type="string",
        *          default="YccJm3KSKJcRA7gP3Uh3haNiVLm9ei1EyYeRkUXkojlpkwoC5Wi8KmOzsXQl",
        *     ),
        *   @SWG\Response(response=200, description="{<pre>&emsp;  'success': true | false,
                                                        &emsp;  'data': {
                                                        &emsp;      'total_items': ,
                                                        &emsp;      'data': [
                                                        &emsp;          {
                                                        &emsp;               'id': ,
                                                        &emsp;               'connector_id': ,
                                                        &emsp;               'content': ,
                                                        &emsp;               'type': ,
                                                        &emsp;               'work_connection_id': ,
                                                        &emsp;               'company_id': ,
                                                        &emsp;               'company_name': ,
                                                        &emsp;               'created_at': ,
                                                        &emsp;               'image_company': ,
                                                        &emsp;               'base_path_company':
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
    public function getByConnector(Request $request)
    {
        try {
            $connector_id = $request->input('connector_id');
            $page_number = $request->input('page_number');
            $page_limit = $request->input('page_limit');
            if (empty($connector_id) || empty($page_number) || empty($page_limit)) {
                $this->error = \Lang::get('common_message.error.MISS_PARAM');
            } else {
                $this->data = Notification::getByConnector($connector_id, $page_number, $page_limit);
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
