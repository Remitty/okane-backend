@extends('admin.layouts.base')

@section('content')
<div class="container-fluid px-4 pt-3" id="user-list">
    <h5 class="mt-4 fw-bold pb-0 mb-4">Admin Management <a href="{{ route('admin.admin.add.form') }}" class="btn btn-outline-success">Add New</a> <span class="float-end text-success" style="font-size: 14px">Welcome {{ Auth::guard('admin')->user()->roleLabel() }}</span></h5>
    <user-table
        title="Admins"
        url="{{ route('admin.admins.list') }}"
    ></user-table>
</div>
@endsection
@section('js')
<script>
    function onChange(id) {
        $('#password-form'+id).show();
        $('#password-edit'+id).hide();
    }
    function clipboard(e) {
        e = e || window.event;
        var target = e.target || e.srcElement;
        navigator.clipboard.writeText(target.closest('label').innerText);
        // $('[data-toggle="tooltip"]').tooltip();
    }
</script>
@endsection
