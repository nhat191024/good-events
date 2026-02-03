<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Enum\ReportStatus;

class ReportController extends Controller
{
    const ERROR_DUPLICATE_REPORT = 'Bạn đã báo cáo nội dung này rồi, vui lòng chờ xử lý.';
    const SUCCESS_REPORT_USER = 'Báo cáo người dùng thành công.';
    const SUCCESS_REPORT_BILL = 'Báo cáo đơn hàng thành công.';

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
            return back()->with('error', self::ERROR_DUPLICATE_REPORT);
        }

        Report::create([
            'user_id' => Auth::id(),
            'reported_user_id' => $validated['reported_user_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        return back()->with('success', self::SUCCESS_REPORT_USER);
    }

    public function reportBill(Request $request)
    {
        // warning: do not submit reported_user_id while reporting bill because this can be easily changed by the client
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
            return back()->with('error', self::ERROR_DUPLICATE_REPORT);
        }

        $bill = \App\Models\PartnerBill::find($validated['reported_bill_id']);
        $partnerId = $bill ? $bill->partner_id : null;

        Report::create([
            'user_id' => Auth::id(),
            'reported_user_id' => $partnerId,
            'reported_bill_id' => $validated['reported_bill_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
        ]);

        return back()->with('success', self::SUCCESS_REPORT_BILL);
    }
}
