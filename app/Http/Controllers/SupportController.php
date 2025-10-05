<?php

namespace App\Http\Controllers;

use App\Models\BusinessTiming;
use App\Models\Faq;
use App\Models\Issue;
use App\Models\IssueType;
use App\Models\SupportContact;
use Exception;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function issueTypes()
    {
        return response()->json(IssueType::all());
    }

    public function issueTypeDetail($id)
    {
        $type = IssueType::find($id);
        if (!$type) {
            return response()->json(['message' => 'Issue type not found'], 404);
        }
        return response()->json($type);
    }

    public function reportIssue(Request $request)
    {
        try{
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'issue_type_id' => 'required|exists:issue_types,id',
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:5120'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('issues', 'public');
        }

        $issue = Issue::create([
            'user_id' => $request->user_id,
            'issue_type_id' => $request->issue_type_id,
            'message' => $request->message,
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Issue submitted successfully',
            'data' => $issue,
        ]);
    } catch (Exception $e){
        return response()->json(['message' => $e->getMessage()], 500);
    }
    }

    public function faqs()
    {
        return response()->json(
            Faq::where('status', 1)->get()
        );
    }

    public function contacts()
    {
        $contact = SupportContact::first();
        return response()->json($contact ?: []);
    }
    public function getSupportHours(){
        try {
        $timing = BusinessTiming::first();

        return $this->responseWithSuccess(
            [
                'start_time' => $timing->start_time,
                'end_time' => $timing->end_time
            ],
            'Business timing fetched successfully',
            200
        );
    } catch (\Exception $e) {
        return $this->responseWithError('Something went wrong!', 500, $e->getMessage());
    }
    }
}
