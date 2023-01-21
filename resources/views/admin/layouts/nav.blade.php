<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <a class="nav-link {{ Request::is('admin/paymentproofs') ? 'active' : '' }}" href="{{route('admin.paymentproofs')}}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-building"></i></div>
                    Payment Proofs
                </a>
                <a class="nav-link {{ Request::is('admin/banks') ? 'active' : '' }}" href="{{route('admin.banks')}}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-bank"></i></div>
                    Banks
                </a>
                @if(auth_is_admin())
                <a class="nav-link {{ Request::is('admin/operators') ? 'active' : '' }}" href="{{route('operators.index')}}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-user"></i></div>
                    Operators
                </a>
                <a class="nav-link {{ Request::is('admin/merchants') ? 'active' : '' }}" href="{{route('admin.merchants')}}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-user"></i></div>
                    Merchants
                </a>
                <a class="nav-link {{ Request::is('admin/callbacks') ? 'active' : '' }}" href="{{route('admin.callbacks')}}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-layer-group"></i></div>
                    Callbacks
                </a>
                <a class="nav-link {{ Request::is('admin/banktypes') ? 'active' : '' }}" href="{{route('banktypes.index')}}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-layer-group"></i></div>
                    Bank Types
                </a>
                <a class="nav-link {{ Request::is('admin/logs') ? 'active' : '' }}" href="{{route('admin.logs')}}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-layer-group"></i></div>
                    Logs
                </a>
                {{-- <a class="nav-link {{ Request::is('admin/payments') ? 'active' : '' }}" href="{{route('admin.payments')}}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-bank"></i></div>
                    Payments
                </a> --}}
                @endif

            </div>
        </div>
    </nav>
</div>
