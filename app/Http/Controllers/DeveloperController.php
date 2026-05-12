<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Developer sandbox — isolated workspace where the developer prototypes
 * features (Asset Management, Dialer) before they ship to other roles.
 *
 * Routes are gated by `role:developer` middleware so admin / CISO /
 * resolvers / employees never see this area.
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

    public function dialer(Request $request)
    {
        return view('developer.dialer');
    }
}
