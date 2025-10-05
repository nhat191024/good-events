<?php

namespace App\Http\Controllers\Client;

use App\Enum\PartnerBillStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Services\QuickBookingService;
use App\Http\Requests\Client\BookingRequest;
use App\Models\Event;
use App\Models\Location;
use App\Models\PartnerBill;
use App\Models\PartnerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 *  @property PartnerBillStatus $status
 */
class ChatController extends Controller
{
    // private $chatService = null;
    //? error messages
    public function index(Request $request)
    {
        return Inertia::render('chat/ChatPage');
    }

}
