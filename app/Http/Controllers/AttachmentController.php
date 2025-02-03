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
            return response()->json($attachments, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
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

            return response()->json([
                'message' => 'Attachment created successfully.',
                'data' => $attachment,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
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
            return response()->json($attachment, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
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

            return response()->json([
                'message' => 'Attachment updated successfully.',
                'data' => $attachment,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
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

            return response()->json([
                'message' => 'Attachment deleted successfully.',
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
