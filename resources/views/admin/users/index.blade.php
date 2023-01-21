@extends('admin.layouts.base')

@section('content')
<div class="container-fluid px-4 pt-3" id="user-list">
    <h5 class="mt-4 fw-bold pb-0 mb-4">User Management <a href="{{ route('admin.user.create') }}" class="btn btn-outline-success">Add New</a> </h5>
    <div class="card mb-4 boxshadow">
        <div class="card-header py-3 fw-bold">
            <i class="fa fa-table me-1"></i>
            User List
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table dataTable" id="usersTable">
                    <thead>
                        <tr>
                            <th class="table_header">ID</th>
                            <th class="table_header">Name</th>
                            <th class="table_header">Email</th>
                            <th class="table_header">Password</th>
                            <th class="table_header">Assigned Bank</th>
                            <th class="table_header">Group No</th>
                            <th class="table_header">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script type="text/javascript">
var table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.users.list') }}"
                },
                order: [
                    [0, 'desc']
                ],
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'password', name: 'password', sortable: false, searchable: false},
                    {data: 'bank_id', name: 'bank_id', sortable: false, searchable: false},
                    {data: 'group_no', name: 'group_no'},
                    {data: 'action', name: 'action', sortable: false, searchable: false},
                ]
            });

</script>
@endsection
