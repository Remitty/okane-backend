@extends('admin.layouts.base')

@section('content')
    <div class="container-fluid px-4 pt-5">
        <h5 class="mt-4 fw-bold pb-0 mb-4">Create Admin <span class="float-end text-success" style="font-size: 14px">Welcome Operator name</span></h5>
        <div class="bg-white boxshadow rounded p-3 p-md-4 mb-4 formview">
            <div class="row">
                <div class="col-12 col-md-9">
                    <form action="{{ route('admin.admin.add') }}" method="post">
                        @csrf
                    <table class="table table-borderless">
                        <tbody>

                            <tr>
                                <th scope="row">Admin Name:</th>
                                <td>
                                    <div class="form-group">
                                        <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" id="name12" aria-describedby="PAN"
                                            placeholder="Name" required>
                                        @error('name')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Admin Email:</th>
                                <td>
                                    <div class="form-group">
                                        <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" id="name12"
                                            aria-describedby="Gmail ID" placeholder="Email" required>
                                        @error('email')
                                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Admin Password:</th>
                                <td>
                                    <div class="form-group">
                                        <input type="password" name="password" value="{{ old('password') }}" class="form-control" id="name12"
                                            aria-describedby="Gmail ID" placeholder="Password" required>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"></th>
                                <td>
                                    <button type="submit" class="btn btn-success">Submit</button>
                                    <a href="{{ route('agent.dashboard') }}" class="btn btn-light" data-toggle="modal" data-target="#exampleModal">Cancel</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
