@extends('admin.layouts.auth')

@section('content')
    <section class="pt-5 pb-5">

        <div class="container">
            <!--------stake--------->
            <div class="row justify-content-center">
                <div class="col-12 col-md-6 contentbg">

                    <div class="bg-white border-radius p-3 p-md-5 box-shadow mb-4 text-left">

                        <h4 class="text-center font-weight-bold mb-4">LOGIN</h4>
                        <form method="POST" action="{{ route('admin.login') }}">
                            @csrf
                            <div class="form-group text-left pb-2">
                                <label for="loginemail" class="font14 text-blue">Email</label>
                                <input type="email" class="form-control border-radius6 @error('email') is-invalid @enderror" value="{{ old('email') }}" id="loginemail" name="email"
                                    aria-describedby="email" placeholder="Enter an Email" required>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

                            </div>
                            <div class="form-group text-left pb-2">
                                <label for="password" class="font14 text-blue">Password</label>
                                <input type="password" class="form-control border-radius6 @error('password') is-invalid @enderror" id="password" name="password"
                                    aria-describedby="password" placeholder="Enter Password" required>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100">Login</button>
                        </form>

                    </div>

                </div>

            </div>
            <!--------stake--------->

        </div>
    </section>
@endsection
