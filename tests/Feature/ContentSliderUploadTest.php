<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ContentController;
use App\Models\Content;
use App\Models\ContentDetails;
use App\Models\ContentMedia;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContentSliderUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->string('short_name')->nullable();
            $table->timestamps();
        });
        DB::table('languages')->insert([
            'id' => 1,
            'name' => 'English',
            'code' => 'en',
            'short_name' => 'en',
        ]);

        Schema::create('content_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_id');
            $table->unsignedBigInteger('language_id');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('content_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_id');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function test_nested_slider_image_upload_replaces_the_saved_media_and_public_url()
    {
        $source = base_path('assets/uploads/logo/banner.jpg');
        $this->assertFileExists($source);

        $content = Content::create(['name' => 'slider']);
        $languageId = 1;
        $oldFilename = 'slider-old-' . bin2hex(random_bytes(5)) . '.jpg';
        $newFilename = null;
        $mediaDirectory = rtrim(base_path(config('location.content.path')), '/\\');
        if (!is_dir($mediaDirectory)) {
            mkdir($mediaDirectory, 0755, true);
        }

        $oldImagePath = $mediaDirectory . DIRECTORY_SEPARATOR . $oldFilename;
        copy($source, $oldImagePath);

        $media = new ContentMedia();
        $media->content_id = $content->id;
        $media->description = [
            'image' => $oldFilename,
            'button_link' => 'https://example.test/old',
        ];
        $media->save();

        $upload = new UploadedFile($source, 'new-promotion.jpg', 'image/jpeg', null, true);
        $request = Request::create(
            '/admin/content-update/' . $content->id . '/' . $languageId,
            'PUT',
            [
                'name' => [$languageId => 'New promotion'],
                'short_description' => [$languageId => 'Updated homepage promotion'],
                'button_name' => [$languageId => 'Explore'],
                'button_link' => [$languageId => 'https://example.test/new'],
                'secondary_button_name' => [$languageId => ''],
                'secondary_button_link' => [$languageId => ''],
            ],
            [],
            ['image' => [$languageId => $upload]]
        );

        try {
            (new ContentController())->update($request, $content, $languageId);

            $savedMedia = ContentMedia::where('content_id', $content->id)->firstOrFail();
            $newFilename = $savedMedia->description->image;

            $this->assertNotSame($oldFilename, $newFilename);
            $this->assertFileExists($mediaDirectory . DIRECTORY_SEPARATOR . $newFilename);
            $this->assertFileDoesNotExist($oldImagePath);
            $this->assertSame('https://example.test/new', $savedMedia->description->button_link);
            $this->assertStringContainsString(
                rawurlencode($newFilename),
                getFile(config('location.content.path') . $newFilename)
            );
            $this->assertDatabaseHas('content_details', [
                'content_id' => $content->id,
                'language_id' => $languageId,
            ]);
        } finally {
            if ($newFilename && is_file($mediaDirectory . DIRECTORY_SEPARATOR . $newFilename)) {
                unlink($mediaDirectory . DIRECTORY_SEPARATOR . $newFilename);
            }
            if (is_file($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
    }
}
