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
        try{
            $substation = Substation::all();
            return $this->responseWithSuccess($substation, 'substation fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 'substation not found', 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $request->validate([
                'name' => 'required|unique:substations,name',
                'is_enabled' => 'required',
            ]);

            $substation = Substation::create($request->all());
            return $this->responseWithSuccess($substation, 'substation created successfully', 200);
        } catch (\Illuminate\Validation\ValidationException $e){
            return $this->responseWithError($e->getMessage(), 422);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 'substation not created', 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $substation = Substation::find($id);
            return $this->responseWithSuccess($substation, 'substation fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 'substation not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            $request->validate([
                // 'name' => 'required',
                'is_enabled' => 'required',
            ]);

            $substation = Substation::find($id);
            $substation->update($request->all());
            return $this->responseWithSuccess($substation, 'substation updated successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 'substation not updated', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $substation = Substation::find($id);
            $substation->delete();
            return $this->responseWithSuccess(null, 'substation deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 'substation not deleted', 404);
        }
    }
}
