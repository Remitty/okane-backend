<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\DataTables;

class UserDataTable
{
    public function index()
    {
        $data = User::orderBy('id', 'desc')->get();
        $datatable = DataTables::of($data)
        ->editColumn('created_at', function ($user) {
            return substr($user->created_at, 0, 10);
        })
        ->editColumn('bank_id', function ($user) {
            /**
             * @var \App\Models\Bank
             */
            $bank = $user->bank;
            return isset($bank) ? $bank->toString() : '';
        })
        ->addColumn('action', function ($user) {
            $assignUrl = route('admin.user.assign.bank', $user->id);
            return '<a href="'.$assignUrl.'" class="btn btn-xs btn-success">Assign Bank</a>';
        })
        ->editColumn('password', function($user) {
            return  Crypt::decryptString($user->password_crypt);
        })
        ->rawColumns(['action'])
        ->removeColumn(['email_verified_at', 'password_crypt', 'updated_at']);

        return $datatable->make(true);
    }
}
