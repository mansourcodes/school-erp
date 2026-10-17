<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<!-- Groups are open section titles (nav-title), not dropdowns: every page is one click away. -->
@if(!backpack_user()->hasRole('Teacher'))
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i>
        {{ trans('backpack::base.dashboard') }}</a></li>
@endif


<!-- Attendance -->
<li class="nav-title">{{ __('attend.attend_and_d') }}</li>

<li class='nav-item'><a class='nav-link' href='{{ backpack_url('add_attend_by_date') }}'><i
            class='nav-icon la la-check-square'></i> {{ __('attend.add_attend_w_date') }}</a></li>

@hasanyrole('Superadmin')
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('attends') }}'><i
                class='nav-icon la la-list-alt'></i> {{ __('attend.attends') }}</a></li>
@endhasanyrole


@hasanyrole('Superadmin')
    <!-- Accounting -->
    <li class="nav-title">{{ __('account.account') }}</li>

    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('payment') }}'><i
                class='nav-icon la la-file-invoice-dollar'></i> {{ __('account.payments') }}</a></li>


    <!-- Registration -->
    <li class="nav-title">{{ __('base.academicRegistration') }}</li>

    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('studentmarks') }}'><i
                class='nav-icon la la-certificate'></i> {{ __('studentmark.studentsmarks') }}</a></li>

    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('classroom') }}'><i
                class='nav-icon la la-chalkboard-teacher'></i> {{ __('classroom.classrooms') }}</a></li>

    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('student') }}'><i
                class='nav-icon la la-user-graduate'></i> {{ __('student.students') }}</a></li>

    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('message') }}'><i
                class='nav-icon la la-envelope'></i> {{ __('message.messages') }}</a></li>


    <!-- Curricula -->
    <li class="nav-title">{{ __('base.academicManagment') }}</li>

    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('course') }}'><i
                class='nav-icon la la-campground'></i> {{ __('course.courses') }}</a></li>

    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('academicpath') }}'><i
                class='nav-icon la la-road'></i> {{ __('academicpath.academicpaths') }}</a></li>

    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('curriculum') }}'><i
                class='nav-icon la la-book'></i> {{ __('curriculum.curricula') }}</a></li>

    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('curriculumcategory') }}'><i
                class='nav-icon la la-project-diagram'></i> {{ __('curriculumcategory.curriculumcategories') }}</a></li>

    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('examtool') }}'><i
                class='nav-icon la la-tools'></i> {{ __('examtool.examtools') }}</a></li>


    <!-- Users, Roles, Permissions -->
    <li class="nav-title">{{ __('base.Authentication') }}</li>

    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i>
            <span>{{ __('base.Users') }}</span></a></li>
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i
                class="nav-icon la la-id-badge"></i> <span>{{ __('base.Roles') }}</span></a></li>
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i
                class="nav-icon la la-key"></i> <span>{{ __('base.Permissions') }}</span></a></li>


    <!-- Settings -->
    <li class="nav-title">{{ __('base.Settings') }}</li>

    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('elfinder') }}"><i
                class="nav-icon la la-folder-open"></i> <span>{{ trans('backpack::crud.file_manager') }}</span></a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('backup') }}'><i
                class='nav-icon la la-hdd'></i> {{ __('base.Backups') }}</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('setting') }}'><i
                class='nav-icon la la-sliders-h'></i> {{ __('base.Settings') }}</a></li>
    <li class='nav-item'><a class='nav-link' href='{{ backpack_url('log') }}'><i
                class='nav-icon la la-terminal'></i> Logs</a></li>
@endhasanyrole
