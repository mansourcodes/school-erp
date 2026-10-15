{{-- Superadmin only: opens the portal as this student / teacher in a new tab (PortalImpersonationController). --}}
@php
    $canOpenPortal = backpack_user()->hasRole('Superadmin') && ($entry instanceof \App\Models\Student
        || ($entry->hasRole('Teacher') && $entry->getKey() !== backpack_user()->getKey()));
@endphp
@if ($canOpenPortal)
    <form method="POST" action="{{ url($crud->route . '/' . $entry->getKey() . '/portal') }}" target="_blank" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-primary" title="{{ trans('localize.open_portal_hint') }}">
            <i class="la la-door-open"></i> {{ trans('localize.open_portal') }}
        </button>
    </form>
@endif
