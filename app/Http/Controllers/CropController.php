<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            return $this->responseWithError($e->getMessage(), 422, 'crop not found');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:crops,name',
            ]);

            $crop = Crop::create($request->all());
            return $this->responseWithSuccess($crop, 'crop created successfully', 200);
        } catch(ValidationException $e){
            $firstError = $e->validator->errors()->first();
            return $this->responseWithError($firstError, 422, $e->validator->errors());
        }
            catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 422, 'crop not found');
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
            return $this->responseWithError($e->getMessage(), 422, 'crop not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        try {
            $request->validate([
                'name' => 'sometimes|required',
            ]);

            $crop = Crop::findOrFail($id);
            $existingCrop = Crop::where('name', $request->name)->where('id', '!=', $id)->first();

            if ($existingCrop) {
                return $this->responseWithError('Crop name already exists', 422, 'Crop name already exists');
            }
            $crop->update($request->all());
            return $this->responseWithSuccess($crop, 'crop updated successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 422, 'crop not found');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $crop = Crop::findOrFail($id);
            $crop->delete();
            return $this->responseWithSuccess($crop, 'crop deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 422, 'crop not found');
        }
    }
}
