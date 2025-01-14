<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Farming Service API",
 *     version="1.0.0",
 *     description="API documentation for Farming Service"
 * )
 *
 * @OA\PathItem(
 *     path="/api/v1/example",
 *     @OA\Get(
 *         summary="Get example data",
 *         description="An example endpoint",
 *         operationId="getExample",
 *         tags={"Example"},
 *         @OA\Response(
 *             response=200,
 *             description="Successful response"
 *         )
 *     )
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
