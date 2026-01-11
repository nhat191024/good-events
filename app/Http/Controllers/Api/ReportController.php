<?php

namespace App\Http\Controllers\Api;

use App\Enum\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\PartnerBill;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * POST /api/report/user
     *
     * Body: reported_user_id, title, description
     * Response: { success: true } or 422 with message if duplicate
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reportUser(Request $request)
    {
        $validated = $request->validate([
            'reported_user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $exists = Report::where('user_id', Auth::id())
            ->where('reported_user_id', $validated['reported_user_id'])
            ->where('status', ReportStatus::PENDING)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'You already reported this user.',
            ], 422);
        }

        Report::create([
            'user_id' => Auth::id(),
            'reported_user_id' => $validated['reported_user_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/report/bill
     *
     * Body: reported_bill_id, title, description
     * Response: { success: true } or 422 with message if duplicate
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reportBill(Request $request)
    {
        $validated = $request->validate([
            'reported_bill_id' => 'required|exists:partner_bills,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $exists = Report::where('user_id', Auth::id())
            ->where('reported_bill_id', $validated['reported_bill_id'])
            ->where('status', ReportStatus::PENDING)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'You already reported this bill.',
            ], 422);
        }

        $bill = PartnerBill::find($validated['reported_bill_id']);
        $partnerId = $bill ? $bill->partner_id : null;

        Report::create([
            'user_id' => Auth::id(),
            'reported_user_id' => $partnerId,
            'reported_bill_id' => $validated['reported_bill_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        return response()->json(['success' => true]);
    }
}
