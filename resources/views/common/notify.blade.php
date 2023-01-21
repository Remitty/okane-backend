@if (count($errors) > 0)
    <div class="alert alert-danger" role="alert">
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        <ul>
            @foreach ($errors->all() as $error)
                <strong>{{ $error }}</strong>
            @endforeach
        </ul>
    </div>
@endif

@if(Session::has('error'))
    <div class="alert alert-danger" role="alert">
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        {{ Session::get('error') }}
    </div>
@endif


@if(Session::has('success'))
    <div class="alert alert-success" role="alert">
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        {{ Session::get('success') }}
    </div>
@endif
