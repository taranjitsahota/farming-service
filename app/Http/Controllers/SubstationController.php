<?php

namespace App\Http\Controllers;

use App\Models\Substation;
use Illuminate\Http\Request;

class SubstationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $substation = Substation::all();
            return $this->responseWithSuccess($substation, 'substation fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'substation not found');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:substations,name',
                'is_enabled' => 'required',
            ]);

            $substation = Substation::create($request->all());
            return $this->responseWithSuccess($substation, 'substation created successfully', 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->responseWithError($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'substation not created');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $substation = Substation::find($id);
            return $this->responseWithSuccess($substation, 'substation fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500, 'substation not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'name'       => ['sometimes', 'required', 'string', 'max:25'],
                'is_enabled' => ['sometimes', 'required', 'boolean'],
            ]);

            $exists = Substation::where('name', $request->name)->where('id', '!=', $id)->exists();
            if ($exists) {
                return $this->responseWithError('substation name already exists', 422);
            }
            
            $substation = Substation::find($id);
            $substation->update($request->all());
            return $this->responseWithSuccess($substation, 'substation updated successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500,  'substation not updated');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $substation = Substation::find($id);
            $substation->delete();
            return $this->responseWithSuccess(null, 'substation deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 500,  'substation not deleted');
        }
    }
}
