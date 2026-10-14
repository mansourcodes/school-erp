<?php

namespace Tests\Feature\Api\V1;

use App\Models\Message;
use App\Models\User;
use App\Services\Portal\TeacherPhotos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\V1\Concerns\BuildsEnrollments;
use Tests\TestCase;

/** Teachers set their own photo (portal profile, or the admin's users form); students see it by their name. */
class TeacherPhotoTest extends TestCase
{
    use RefreshDatabase, BuildsEnrollments;

    protected $teacher;

    protected $classroom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnrollments();
        Storage::fake('public');

        Role::findOrCreate('Teacher', 'web');
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');
        $this->classroom = $this->enroll([], [
            'teachers' => [['curriculum_id' => (string) $this->curriculum->id, 'teacher_name' => 'أ. علي', 'user_id' => (string) $this->teacher->id]],
        ]);
    }

    protected function jpeg($width, $height, $payload = '')
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 40, 120, 200));
        ob_start();
        imagejpeg($image, null, 90);

        return UploadedFile::fake()->createWithContent('photo.jpg', ob_get_clean() . $payload);
    }

    /** Sets the photo without the API (see MessagesTest::stored on acting as a second identity). */
    protected function givePhoto()
    {
        app(TeacherPhotos::class)->store($this->teacher, $this->jpeg(400, 400));

        return Storage::disk('public')->url($this->teacher->fresh()->photo_path);
    }

    public function test_a_teacher_uploads_their_photo_re_encoded_and_shown_in_me()
    {
        $response = $this->actingAs($this->teacher, 'web')
            ->post('/api/v1/teacher/photo', ['photo' => $this->jpeg(1600, 1200, '<?php echo "x"; ?>')], ['Accept' => 'application/json'])
            ->assertOk();

        $path = $this->teacher->fresh()->photo_path;
        $this->assertStringStartsWith("teachers/photos/{$this->teacher->id}/", $path);
        $bytes = Storage::disk('public')->get($path);
        $this->assertStringNotContainsString('<?php', $bytes);
        $this->assertSame([512, 384], array_slice(getimagesizefromstring($bytes), 0, 2));
        $url = Storage::disk('public')->url($path);
        $response->assertJsonPath('data.photo_url', $url);
        $this->actingAs($this->teacher, 'web')->getJson('/api/v1/me')->assertJsonPath('data.photo_url', $url);
    }

    public function test_replacing_and_deleting_remove_the_old_file()
    {
        $this->actingAs($this->teacher, 'web')->post('/api/v1/teacher/photo', ['photo' => $this->jpeg(400, 400)], ['Accept' => 'application/json']);
        $first = $this->teacher->fresh()->photo_path;
        $this->actingAs($this->teacher, 'web')->post('/api/v1/teacher/photo', ['photo' => $this->jpeg(500, 500)], ['Accept' => 'application/json'])->assertOk();
        Storage::disk('public')->assertMissing($first);
        $second = $this->teacher->fresh()->photo_path;

        $this->actingAs($this->teacher, 'web')->deleteJson('/api/v1/teacher/photo')->assertNoContent();

        Storage::disk('public')->assertMissing($second);
        $this->assertNull($this->teacher->fresh()->photo_path);
    }

    public function test_small_or_non_image_files_are_refused()
    {
        $this->actingAs($this->teacher, 'web')->post('/api/v1/teacher/photo', ['photo' => $this->jpeg(200, 200)], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors('photo');
        $this->actingAs($this->teacher, 'web')
            ->post('/api/v1/teacher/photo', ['photo' => UploadedFile::fake()->createWithContent('photo.jpg', 'not an image')], ['Accept' => 'application/json'])
            ->assertStatus(422);
        $this->assertNull($this->teacher->fresh()->photo_path);
    }

    public function test_students_cannot_upload_a_teacher_photo()
    {
        $this->actingAs($this->student, 'student')->post('/api/v1/teacher/photo', ['photo' => $this->jpeg(400, 400)], ['Accept' => 'application/json'])
            ->assertStatus(403);
    }

    public function test_students_see_the_photo_by_the_subject_teacher()
    {
        $url = $this->givePhoto();

        $this->actingAs($this->student, 'student')->getJson('/api/v1/student/courses/current')
            ->assertJsonPath('data.curricula.0.teacher', 'أ. علي')
            ->assertJsonPath('data.curricula.0.teacher_photo_url', $url);
    }

    public function test_subjects_without_a_linked_teacher_user_have_no_photo()
    {
        $this->givePhoto();
        $this->classroom->update(['teachers' => [['curriculum_id' => (string) $this->curriculum->id, 'teacher_name' => 'أ. علي']]]);

        $this->actingAs($this->student, 'student')->getJson('/api/v1/student/courses/current')
            ->assertJsonPath('data.curricula.0.teacher_photo_url', null);
    }

    public function test_students_see_the_photo_in_the_inbox()
    {
        $url = $this->givePhoto();
        $message = Message::create(['sender_user_id' => $this->teacher->id, 'class_room_id' => $this->classroom->id, 'body' => 'رسالة']);
        $message->recipients()->create(['student_id' => $this->student->id]);

        $this->actingAs($this->student, 'student')->getJson('/api/v1/student/messages')
            ->assertJsonPath('data.0.teacher_photo_url', $url);
    }

    public function test_the_admin_sets_and_clears_a_users_photo()
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('Superadmin', 'web'));
        $form = ['name' => $this->teacher->name, 'email' => $this->teacher->email, 'id' => $this->teacher->id];

        $this->actingAs($admin, 'backpack')
            ->put("/admin/user/{$this->teacher->id}", $form + ['photo_path' => $this->jpeg(400, 400)])
            ->assertSessionHasNoErrors()->assertRedirect();
        $path = $this->teacher->fresh()->photo_path;
        $this->assertStringStartsWith("teachers/photos/{$this->teacher->id}/", $path);
        Storage::disk('public')->assertExists($path);

        // Saving without touching the field keeps the photo.
        $this->actingAs($admin, 'backpack')->put("/admin/user/{$this->teacher->id}", $form)->assertSessionHasNoErrors();
        $this->assertSame($path, $this->teacher->fresh()->photo_path);

        // The upload field's clear button posts an empty value.
        $this->actingAs($admin, 'backpack')->put("/admin/user/{$this->teacher->id}", $form + ['photo_path' => ''])->assertSessionHasNoErrors();
        $this->assertNull($this->teacher->fresh()->photo_path);
        Storage::disk('public')->assertMissing($path);
    }
}
