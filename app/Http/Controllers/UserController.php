<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the users page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('admin.users.index');
    }

    /**
     * @param UserDataTable $datatable
     *
     * @return Yajra\DataTables\DataTables
     */
    public function list(UserDataTable $datatable)
    {
        return $datatable->index();
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.users.add');
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required'],
            'name' => ['required'],
        ]);

        $data = $request->all();

        $data['password'] = Hash::make($request->password);
        $data['password_crypt'] = Crypt::encryptString($request->password);
        $user = User::create($data);
        return back()->with('success', 'Added an user successfully');
    }
}
