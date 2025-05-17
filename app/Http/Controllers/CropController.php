<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use Illuminate\Http\Request;

class CropController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $crop = Crop::all();
            return $this->responseWithSuccess($crop, 'crop fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 'crop not found', 422);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'is_enabled' => 'required',
            ]);

            $crop = Crop::create($request->all());
            return $this->responseWithSuccess($crop, 'crop created successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 'crop not found', 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $crop = Crop::findOrFail($id);
            return $this->responseWithSuccess($crop, 'crop fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 'crop not found', 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {

        try{
            $request->validate([
                'name' => 'required',
                'is_enabled' => 'required',
            ]);

            $crop = Crop::findOrFail($id);
            $crop->update($request->all());
            return $this->responseWithSuccess($crop, 'crop updated successfully', 200);
        }catch(\Exception $e){
            return $this->responseWithError($e->getMessage(), 'crop not found', 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $crop = Crop::findOrFail($id);
            $crop->delete();
            return $this->responseWithSuccess($crop, 'crop deleted successfully', 200);
        }catch(\Exception $e){
            return $this->responseWithError($e->getMessage(), 'crop not found', 422);
        }
    }
}
