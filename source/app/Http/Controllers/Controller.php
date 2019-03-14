<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @SWG\Swagger(
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="Document test API Locofull",
 *         description="",
 *         termsOfService="",
 *         @SWG\Contact(
 *             email="tien.nguyen@saver.jp"
 *         ),
 *         @SWG\License(
 *             name="Saver",
 *             url="https://www.saver.jp/"
 *         )
 *     )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function doResponse($success = false, $data = null, $error = null)
    {
        return response()->json([
                'success' => $success,
                'data' => $data,
                'error' => $error
        ]);
    }
}
