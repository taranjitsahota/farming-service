<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $attachments = Attachment::all();
        return response()->json($attachments, 200);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'attachment_name' => 'required|string|max:255',
            'attachment_type' => 'required|string|max:255',
            'is_enabled' => 'boolean',
        ]);

        $attachment = Attachment::create($request->all());

        return response()->json([
            'message' => 'Attachment created successfully.',
            'data' => $attachment,
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $attachment)
    {

        $request->validate([
            'attachment_name' => 'required|string|max:255',
            'attachment_type' => 'required|string|max:255',
            'is_enabled' => 'boolean',
        ]);

        $attachment->update($request->all());

        return response()->json([
            'message' => 'Attachment updated successfully.',
            'data' => $attachment,
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($attachment)
    {

        $attachment->delete();

        return response()->json([
            'message' => 'Attachment deleted successfully.',
        ], 200);
        
    }
}
