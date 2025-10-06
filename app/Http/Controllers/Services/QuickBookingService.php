<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;

class QuickBookingService extends Controller
{
    /**
     *  go back with a flash error message.
     *
     *  how this work?
     *  if previous URL is missing, same as current, or external host => fallback route.
     *  if previous route is quick-booking.choose-partner-category => go to quick-booking.choose-category.
     *  if previous route is in quick-booking.* group => go back to that route.
     *  if previous path contains /quick-booking but no named route found => go to quick-booking.choose-category.
     *  otherwise => fallback route.
     *
     * @param string $message
     * @param string $fallbackRoute (default is 'home')
     * @return \Illuminate\Http\RedirectResponse
     */
    public function goBackWithError(string $message, string $fallbackRoute = 'home')
    {
        $previous = url()->previous();
        $current  = url()->current();

        // 1) no previous or previous equals current -> fallback
        if (empty($previous) || $previous === $current) {
            return redirect()->route($fallbackRoute)->with('error', $message);
        }

        // 2) same-origin check to avoid open-redirect
        $prevHost = parse_url($previous, PHP_URL_HOST);
        $currHost = request()->getHost();
        if ($prevHost && $prevHost !== $currHost) {
            return redirect()->route($fallbackRoute)->with('error', $message);
        }

        // 3) get path portion of previous URL
        $prevPath = parse_url($previous, PHP_URL_PATH) ?: '/';

        // 4) try to resolve previous path to a named route (safe match)
        $prevRouteName = null;
        try {
            $requestForMatch = \Illuminate\Http\Request::create($prevPath);
            $matched = app('router')->getRoutes()->match($requestForMatch);
            $prevRouteName = $matched->getName();
        } catch (\Throwable $e) {
            $prevRouteName = null;
        }

        // 5) handle special case mapping
        if ($prevRouteName === 'quick-booking.choose-partner-category') {
            $target = 'quick-booking.choose-category';
            if (\Illuminate\Support\Facades\Route::has($target)) {
                return redirect()->route($target)->with('error', $message);
            }
        }

        // 6) if it's a quick-booking.* route, go back to the start of quick booking flow
        if ($prevRouteName && \Illuminate\Support\Str::startsWith($prevRouteName, 'quick-booking.')) {
            $target = 'quick-booking.choose-category';
            if (\Illuminate\Support\Facades\Route::has($prevRouteName)) {
                return redirect()->route($target)->with('error', $message);
            }
        }

        // 7) If route not named but path contains quick-booking -> send to choose-category (safe fallback inside group)
        if (strpos($prevPath, '/quick-booking') !== false) {
            $target = 'quick-booking.choose-category';
            if (\Illuminate\Support\Facades\Route::has($target)) {
                return redirect()->route($target)->with('error', $message);
            }
        }

        // 8) final fallback
        return redirect()->route($fallbackRoute)->with('error', $message);
    }
}
