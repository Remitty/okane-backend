@if (Session::has('error'))
    <div class="modal fade centermodel" id="notifymodal" tabindex="-1" role="dialog"
        aria-labelledby="notifymodalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-radius12">
                <div class="modal-header pb-0">
                    <h5 class="modal-title text-blue" id="notifymodalTitle">Alert!</h5>
                    <button type="button" class="close text-blue" data-dismiss="modal" aria-label="Close"> <span
                            aria-hidden="true">&times;</span> </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        {{-- <button type="button" class="btn btn-sm close" data-dismiss="alert">×</button> --}}
                        {{ Session::get('error') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif


@if (Session::has('success'))
    <div class="modal fade centermodel " id="notifymodal" tabindex="-1" role="dialog"
        >
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-radius12">
                <div class="modal-header pb-0">
                    <h5 class="modal-title text-blue" id="notifymodalTitle">Success!</h5>
                    <button type="button" class="close text-blue" data-dismiss="modal" aria-label="Close"> <span
                            aria-hidden="true">&times;</span> </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success">
                        {{-- <button type="button" class="btn btn-sm close" data-dismiss="alert">×</button> --}}
                        {{ Session::get('success') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
