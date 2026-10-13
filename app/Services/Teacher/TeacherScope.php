<?php

namespace App\Services\Teacher;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * What a teacher may see and edit (PLAN.md §8 "Access rules"):
 * classrooms whose `teachers` JSON names them (user_id), in the current course only
 * (active, latest start date), and within a classroom the subjects assigned to them.
 */
class TeacherScope
{
    protected $teacher;

    protected $classrooms;

    public function __construct(User $teacher)
    {
        $this->teacher = $teacher;
    }

    /** @return Collection|ClassRoom[] the teacher's classrooms of the current course */
    public function classrooms()
    {
        if ($this->classrooms !== null) {
            return $this->classrooms;
        }

        $id = $this->teacher->id;

        // LIKE pre-filter (user_id is stored as "5" by Backpack, sometimes as 5), then an exact check.
        $candidates = ClassRoom::whereHas('course', function ($q) {
            $q->where('is_active', 1);
        })
            ->where(function ($q) use ($id) {
                $q->where('teachers', 'like', '%"user_id":"' . $id . '"%')
                    ->orWhere('teachers', 'like', '%"user_id":' . $id . ',%')
                    ->orWhere('teachers', 'like', '%"user_id":' . $id . '}%');
            })
            ->with('course')
            ->get()
            ->filter(function (ClassRoom $classroom) {
                return count($this->curriculumIds($classroom)) > 0;
            });

        // Current course = the latest active one among them (one course only).
        $latest = $candidates->sortByDesc(function (ClassRoom $classroom) {
            return optional($classroom->course->start_date)->toDateString() . sprintf('%010d', $classroom->course_id);
        })->first();

        return $this->classrooms = $latest
            ? $candidates->where('course_id', $latest->course_id)->sortBy('class_room_number')->values()
            : collect();
    }

    /** One of the teacher's current classrooms, or null. */
    public function find($classroomId)
    {
        return $this->classrooms()->first(function (ClassRoom $classroom) use ($classroomId) {
            return (int) $classroom->id === (int) $classroomId;
        });
    }

    /** @return int[] curricula assigned to this teacher in the classroom */
    public function curriculumIds(ClassRoom $classroom)
    {
        $ids = [];
        foreach ($classroom->teachers ?: [] as $entry) {
            if (isset($entry['user_id'], $entry['curriculum_id']) && (string) $entry['user_id'] === (string) $this->teacher->id) {
                $ids[] = (int) $entry['curriculum_id'];
            }
        }

        return array_values(array_unique($ids));
    }
}
