<div class="dropdown-multilevel">
    <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
        {{ $test }}
        <span class="caret"></span></button>
    <ul class="dropdown-menu">
        <li class="dropdown-item"><a tabindex="-1" href="#">HTML</a></li>
        <li class="dropdown-item"><a tabindex="-1" href="#">CSS</a></li>
        <li class="dropdown-item dropdown-submenu">
            <a class="test" tabindex="-1" href="#">
                اسم
                <i class="las la-angle-left"></i>
            </a>
            <ul class="dropdown-menu">
                <li class="dropdown-item"><a tabindex="-1" href="#">2nd level dropdown</a></li>
                <li class="dropdown-item"><a tabindex="-1" href="#">2nd level dropdown</a></li>
                <li class=" dropdown-item dropdown-submenu">
                    <a class="test" href="#">Another dropdown <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="#">3rd level dropdown</a></li>
                        <li><a href="#">3rd level dropdown</a></li>
                    </ul>
                </li>
            </ul>
        </li>
    </ul>
</div>
