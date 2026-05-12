<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Developer workspace — the app launcher (CTS / ATS / Dialer) plus the
 * still-incubating ATS prototype. The Dialer module lives in its own
 * controllers under App\Http\Controllers\Developer.
 *
 * Routes are gated by `role:developer` middleware, so admin / CISO /
 * resolvers / employees never see ATS or the Dialer.
 */
class DeveloperController extends Controller
{
    public function home(Request $request)
    {
        return view('developer.home');
    }

    public function assets(Request $request)
    {
        return view('developer.assets');
    }
}
