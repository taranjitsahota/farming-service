<?php

namespace App\Http\Controllers;

use App\Models\InterestedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class WebsiteController extends Controller
{
    public function contactForm(Request $request)
    {

        try {
            $request->validate([
                'name' => 'required',
                'contact_number' => 'required',
                'email' => 'required|email',
                'village_name' => 'required',
                'pincode' => 'required',
                'district' => 'required',
                'area_of_land' => 'required',
                'land_unit' => 'required',
            ]);

            $data = $request->all();


            InterestedUser::create([
                'name' => $data['name'],
                'contact_number' => $data['contact_number'],
                'email' => $data['email'],
                'village_name' => $data['village_name'],
                'pincode' => $data['pincode'],
                'district' => $data['district'],
                'area_of_land' => $data['area_of_land'],
                'land_unit' => $data['land_unit'],
                'type' => 'email',
            ]);

            Mail::send([], [], function ($message) use ($data) {
                $message->to('admin@ezykheti.com')
                    ->subject('New Contact Form Submission')
                    ->html("
                    <h2>New Contact Form Submission</h2>
                    <p><strong>Farmer Name:</strong> {$data['name']}</p>
                    <p><strong>Contact Number:</strong> {$data['contact_number']}</p>
                    <p><strong>Email:</strong> {$data['email']}</p>
                    <p><strong>Village:</strong> {$data['village_name']}</p>
                    <p><strong>Pincode:</strong> {$data['pincode']}</p>
                    <p><strong>District:</strong> {$data['district']}</p>
                    <p><strong>Area of Land:</strong> {$data['area_of_land']} {$data['land_unit']}</p>
                ");
            });

            return $this->responseWithSuccess([], 'Form submitted successfully', 200);
        } catch (ValidationException $e) {
            return $this->responseWithError($e->getMessage(), 422 , 'Form not submitted');
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage(), 422 , 'Form not submitted');
        }
    }
}
