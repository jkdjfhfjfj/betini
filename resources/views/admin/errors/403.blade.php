@extends('admin.layouts.app')
@section('title')
    @lang('403')
@endsection


@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3"><i class="icon-check"></i> @lang("You're signed in, but this account doesn't have permission to open this page.")</h4>
                        <p class="mb-3">
                            @lang('For wallet transactions, grant Payment Request & Log view access. To approve or reject manual wallet requests, also grant edit access.')
                        </p>
                        @if(adminAccessRoute(config('role.manage_staff.access.view')))
                            <a class="btn btn-primary" href="{{ route('admin.staff') }}">
                                @lang('Manage admin permissions')
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
