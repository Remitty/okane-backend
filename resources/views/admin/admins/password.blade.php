<div>
    <div id="password-edit{{$id}}">
        <label class="pb-0 mb-0 text-dark">{{ Crypt::decryptString($password_crypt) }}
            <span onclick="clipboard(event)" data-toggle="tooltip" data-placement="top" data-trigger="focus" title="Copied!"><i class="fa fa-clone" ></i></span></label>
        <button onclick="onChange({{$id}})" class="btn btn-outline-success ml-3">Change</button>
    </div>
    <form id="password-form{{$id}}" action="{{ route('admin.admin.change_password', $id) }}" method="post" class="form-inline" style="display:none">
        @csrf
        <div class="form-group mr-2">
            <input
            type="password"
            name="password"
            class="form-control"
            id="exampleInputEmail1"
            aria-describedby="emailHelp"
            placeholder="Enter New Password"
            required
            />
        </div>
        <button type="submit" class="btn btn-outline-success">Submit</button>
    </form>
</div>
