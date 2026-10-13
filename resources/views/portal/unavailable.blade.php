@extends('frontend-student.layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body py-5">
                        <h2 class="h4">{{ __('localize.portal_' . $mode . '_title') }}</h2>
                        <p class="text-muted mb-0">{{ __('localize.portal_' . $mode . '_body') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
