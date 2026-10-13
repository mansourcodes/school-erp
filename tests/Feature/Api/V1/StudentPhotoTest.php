<?php

namespace Tests\Feature\Api\V1;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\V1\Concerns\BuildsEnrollments;
use Tests\TestCase;

/** PLAN.md §5.8 / §8: teachers set student photos; the server re-encodes them. */
class StudentPhotoTest extends TestCase
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
            'teachers' => [['curriculum_id' => (string) $this->curriculum->id, 'teacher_name' => 'x', 'user_id' => (string) $this->teacher->id]],
        ]);
    }

    protected function jpeg($width, $height, $payload = '')
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 120, 40));
        ob_start();
        imagejpeg($image, null, 90);

        return UploadedFile::fake()->createWithContent('photo.jpg', ob_get_clean() . $payload);
    }

    protected function url($student = null)
    {
        return "/api/v1/teacher/classrooms/{$this->classroom->id}/students/" . ($student ?: $this->student)->id . '/photo';
    }

    public function test_a_teacher_uploads_a_photo_that_is_re_encoded_and_scaled()
    {
        // Bytes appended after the JPEG (a polyglot payload) must not survive.
        $response = $this->actingAs($this->teacher, 'web')
            ->post($this->url(), ['photo' => $this->jpeg(1200, 1600, '<?php echo "x"; ?>')], ['Accept' => 'application/json'])
            ->assertOk();

        $path = $this->student->fresh()->photo_path;
        $this->assertStringStartsWith("students/photos/{$this->student->id}/", $path);
        $this->assertStringEndsWith('.webp', $path);
        $bytes = Storage::disk('public')->get($path);
        $this->assertStringNotContainsString('<?php', $bytes);
        $size = getimagesizefromstring($bytes);
        $this->assertSame([384, 512], [$size[0], $size[1]]);
        $response->assertJsonPath('data.photo_url', Storage::disk('public')->url($path));
    }

    public function test_replacing_a_photo_deletes_the_previous_file()
    {
        $this->actingAs($this->teacher, 'web')->post($this->url(), ['photo' => $this->jpeg(400, 400)], ['Accept' => 'application/json']);
        $first = $this->student->fresh()->photo_path;

        $this->actingAs($this->teacher, 'web')->post($this->url(), ['photo' => $this->jpeg(500, 500)], ['Accept' => 'application/json'])->assertOk();

        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($this->student->fresh()->photo_path);
    }

    public function test_the_photo_shows_in_the_students_me_and_profile()
    {
        $this->actingAs($this->teacher, 'web')->post($this->url(), ['photo' => $this->jpeg(400, 400)], ['Accept' => 'application/json']);
        $url = Storage::disk('public')->url($this->student->fresh()->photo_path);

        $this->assertSame($url, (new \App\Http\Resources\V1\MeResource($this->student->fresh()))->toArray(request())['photo_url']);
        $this->assertSame($url, (new \App\Http\Resources\V1\ProfileResource($this->student->fresh()))->toArray(request())['photo_url']);
    }

    public function test_deleting_removes_the_file()
    {
        $this->actingAs($this->teacher, 'web')->post($this->url(), ['photo' => $this->jpeg(400, 400)], ['Accept' => 'application/json']);
        $path = $this->student->fresh()->photo_path;

        $this->actingAs($this->teacher, 'web')->deleteJson($this->url())->assertNoContent();

        Storage::disk('public')->assertMissing($path);
        $this->assertNull($this->student->fresh()->photo_path);
    }

    public function test_small_or_non_image_files_are_refused()
    {
        $this->actingAs($this->teacher, 'web')->post($this->url(), ['photo' => $this->jpeg(200, 200)], ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors('photo');
        $this->actingAs($this->teacher, 'web')
            ->post($this->url(), ['photo' => UploadedFile::fake()->createWithContent('photo.jpg', 'not an image')], ['Accept' => 'application/json'])
            ->assertStatus(422);
        $this->assertNull($this->student->fresh()->photo_path);
    }

    public function test_only_students_of_my_classrooms()
    {
        $stranger = Student::factory()->create();

        $this->actingAs($this->teacher, 'web')
            ->post($this->url($stranger), ['photo' => $this->jpeg(400, 400)], ['Accept' => 'application/json'])
            ->assertStatus(404);
        $this->assertNull($stranger->fresh()->photo_path);
    }

    public function test_uploads_are_throttled_per_student()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($this->teacher, 'web')->post($this->url(), ['photo' => $this->jpeg(400, 400)], ['Accept' => 'application/json'])->assertOk();
        }

        $this->actingAs($this->teacher, 'web')->post($this->url(), ['photo' => $this->jpeg(400, 400)], ['Accept' => 'application/json'])
            ->assertStatus(429);
    }
}
