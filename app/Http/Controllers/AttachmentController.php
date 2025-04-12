<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttachmentController extends Controller
{
     /**
     * Display a listing of the resource.
     * 
     * @OA\Get(
     *     path="/api/attachments",
     *     summary="Get all attachments",
     *     tags={"Attachments"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function index()
    {
        try {
            $attachments = Attachment::all();
            return $this->successResponse($attachments, 'attachments fetched successfully', 200);

        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @OA\Post(
     *     path="/api/attachments",
     *     summary="Create an attachment",
     *     tags={"Attachments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"attachment_name", "attachment_type"},
     *             @OA\Property(property="attachment_name", type="string", example="Invoice.pdf"),
     *             @OA\Property(property="attachment_type", type="string", example="PDF"),
     *             @OA\Property(property="is_enabled", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, ref="#/components/responses/201"),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'attachment_name' => 'required|string|max:255',
                'attachment_type' => 'required|string|max:255',
                'is_enabled' => 'boolean',
            ]);

            $attachment = Attachment::create($validated);
            return $this->successResponse($attachment, 'Attachment created successfully.', 201);

           
        } catch (ValidationException $e) {
            return $this->responseWithError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * 
     * @OA\Get(
     *     path="/api/attachments/{id}",
     *     summary="Get an attachment by ID",
     *     tags={"Attachments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=404, description="Attachment not found"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function show($id)
    {
        try {
            $attachment = Attachment::findOrFail($id);
            return $this->successResponse($attachment, 'Attachment fetched successfully.', 200);
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * 
     * @OA\Put(
     *     path="/api/attachments/{id}",
     *     summary="Update an attachment",
     *     tags={"Attachments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="attachment_name", type="string", example="Updated Invoice.pdf"),
     *             @OA\Property(property="attachment_type", type="string", example="PDF"),
     *             @OA\Property(property="is_enabled", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=404, description="Attachment not found"),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $attachment = Attachment::findOrFail($id);

            $validated = $request->validate([
                'attachment_name' => 'sometimes|string|max:255',
                'attachment_type' => 'sometimes|string|max:255',
                'is_enabled' => 'sometimes|boolean',
            ]);

            $attachment->update($validated);

            return $this->successResponse($attachment, 'Attachment updated successfully.', 200);
            
        } catch (ValidationException $e) {
           return $this->responseWithError('Validation failed', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @OA\Delete(
     *     path="/api/attachments/{id}",
     *     summary="Delete an attachment (soft delete)",
     *     tags={"Attachments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, ref="#/components/responses/200"),
     *     @OA\Response(response=404, description="Attachment not found"),
     *     @OA\Response(response=500, ref="#/components/responses/500")
     * )
     */
    public function destroy($id)
    {
        try {
            $attachment = Attachment::findOrFail($id);
            $attachment->delete(); // Soft delete

            return $this->successResponse([], 'Attachment deleted successfully.', 200);
            
        }catch (\Exception $e) {
            return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
        }
    }
}
