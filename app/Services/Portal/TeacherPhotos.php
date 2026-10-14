<?php

namespace App\Services\Portal;

/** Teacher photos: set by the teacher (profile) or the admin (users); shown to their students. */
class TeacherPhotos extends ProfilePhotos
{
    const DIRECTORY = 'teachers/photos';
}
