{{-- Superadmin only: opens the teacher portal as this teacher in a new tab (ImpersonateTeacherController). --}}
@if (backpack_user()->hasRole('Superadmin') && $entry->hasRole('Teacher') && $entry->getKey() !== backpack_user()->getKey())
    <form method="POST" action="{{ url($crud->route . '/' . $entry->getKey() . '/portal') }}" target="_blank" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-primary" title="{{ trans('localize.open_portal_as_teacher_hint') }}">
            <i class="la la-door-open"></i> {{ trans('localize.open_portal_as_teacher') }}
        </button>
    </form>
@endif
