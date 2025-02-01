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

 
 * @OA\Components(
 *     @OA\Response(
 *         response="403",
 *         description="Unauthorized access",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="status_code", type="integer", example=401),
 *             @OA\Property(property="message", type="string", example="Unauthorized access.")
 *         )
 *     ),
    *  @OA\Response(
    *         response="500",
    *         description="Internal Server Error",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="message", type="string", example="Something went wrong!"),
    *             @OA\Property(property="error", type="string", example="Internal Server Error")
    *         )
    *     ),
     *  @OA\Response(
     *         response="401",
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     ),
     * @OA\Response(
 *         response="200",
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Operation successful"),
 *             @OA\Property(property="data", type="object", example={})
 *         )
 *     ),
 *     @OA\Response(
 *         response="201",
 *         description="Resource created successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Resource created successfully"),
 *             @OA\Property(property="data", type="object", example={})
 *         )
 *     ),
 *  @OA\Response(
     *         response="422",
     *         description="Validation error - invalid input data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *       @OA\Response(
     *         response=404,
     *         description="Record not updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Not Updated!!")
     *         )
     *     ),
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
