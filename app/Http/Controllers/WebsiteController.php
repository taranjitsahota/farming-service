<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class WebsiteController extends Controller
{
   public function contactForm(Request $request) {
    $data = $request->all();

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
}

}
