<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Handle index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        redirect()->route('admin.dashboard');
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function dashboard()
    {
        /**
         * @var Admin $admin
         */
        return view('admin.dashboard');
    }

    public function changePassword(Request $request, $id)
    {
        $admin = Admin::find($id);
        $admin->update(['password_crypt' => Crypt::encryptString($request->password)]);
        return back();
    }
}
