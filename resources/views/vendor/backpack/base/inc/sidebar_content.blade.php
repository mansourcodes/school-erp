<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>




<li class="nav-title">{{__('base.academicManagment')}}</li>


<!-- Settings -->
<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-school"></i> {{__('academicpath.academicpaths')}}</a>
    <ul class="nav-dropdown-items">


        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('classroom') }}'><i class='nav-icon la la-chalkboard-teacher'></i> {{ __('classroom.classrooms')}}</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('studentmarks') }}'><i class='nav-icon la la-certificate'></i> {{__('studentmark.studentsmarks')}}</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('student') }}'><i class='nav-icon la la-user-graduate'></i> {{__('student.students')}}</a></li>

        <li class="nav-title">
            <hr>
        </li>

        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('course') }}'><i class='nav-icon la la-campground'></i> {{__('course.courses')}}</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('academicpath') }}'><i class='nav-icon la la-road'></i> {{__('academicpath.academicpaths')}}</a></li>

        <li class="nav-title">
            <hr>
        </li>

        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('curriculum') }}'><i class='nav-icon la la-book'></i> {{__('curriculum.curricula')}}</a></li>

        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('curriculumcategory') }}'><i class='nav-icon la la-project-diagram'></i> {{__('curriculumcategory.curriculumcategories')}}</a></li>



    </ul>
</li>


<li class="nav-title">{{__('base.systemManagment')}}</li>



<!-- Users, Roles, Permissions -->
<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-users"></i> {{__('base.Authentication')}}</a>
    <ul class="nav-dropdown-items">
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>{{__('base.Users')}} </span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-id-badge"></i> <span>{{__('base.Roles')}} </span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>{{__('base.Permissions')}} </span></a></li>
    </ul>
</li>





<!-- News , Pages -->
<li class="nav-item nav-dropdown" style="display: none;">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-newspaper-o"></i>{{ __('base.News')}}</a>
    <ul class="nav-dropdown-items">
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('page') }}'><i class='nav-icon la la-file-o'></i> <span>{{ __('base.Pages')}}</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('article') }}"><i class="nav-icon la la-newspaper-o"></i> {{ __('base.Articles')}}</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('category') }}"><i class="nav-icon la la-list"></i> {{ __('base.Categories')}}</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('tag') }}"><i class="nav-icon la la-tag"></i> {{ __('base.Tags')}}</a></li>
    </ul>
</li>




<!-- Settings -->
<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-cog"></i> {{ __('base.Settings') }}</a>
    <ul class="nav-dropdown-items">
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('elfinder') }}\"><i class="nav-icon la la-files-o"></i> <span>{{ trans('backpack::crud.file_manager') }}</span></a></li>

        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('backup') }}'><i class='nav-icon la la-hdd-o'></i> {{ __('base.Backups') }}</a></li>
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('setting') }}'><i class='nav-icon la la-cog'></i> {{ __('base.Settings') }} </a></li>

        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('log') }}'><i class='nav-icon la la-terminal'></i> Logs</a></li>
    </ul>
</li>
