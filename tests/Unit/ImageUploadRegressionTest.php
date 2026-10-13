<?php

namespace Tests\Unit;

use App\Http\Traits\Upload;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class ImageUploadRegressionTest extends TestCase
{
    private static $application;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $application = require dirname(__DIR__, 2) . '/bootstrap/app.php';
        $application->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        self::$application = $application;
    }

    public function test_upload_writes_to_the_app_root_even_when_the_working_directory_differs(): void
    {
        $source = base_path('assets/uploads/logo/banner.jpg');
        $this->assertFileExists($source);

        $relativeDirectory = 'storage/app/upload-regression-' . bin2hex(random_bytes(6));
        $destination = base_path($relativeDirectory . '/replacement.jpg');
        $originalDirectory = getcwd();

        try {
            chdir(sys_get_temp_dir());

            $uploader = new class {
                use Upload;
            };

            $uploadedFile = new UploadedFile(
                $source,
                'replacement.jpg',
                'image/jpeg',
                null,
                true
            );

            $uploader->uploadImage(
                $uploadedFile,
                $relativeDirectory,
                null,
                null,
                null,
                'replacement.jpg'
            );

            $this->assertFileExists($destination);
            $this->assertNotFalse(getimagesize($destination));
            $this->assertStringContainsString(
                'v=',
                getFile($relativeDirectory . '/replacement.jpg')
            );
        } finally {
            chdir($originalDirectory);
            if (is_file($destination)) {
                unlink($destination);
            }
            $directory = dirname($destination);
            if (is_dir($directory)) {
                rmdir($directory);
            }
        }
    }

    public function test_banner_url_changes_when_same_size_image_contents_change(): void
    {
        $relativeDirectory = 'storage/app/banner-cache-regression-' . bin2hex(random_bytes(6));
        $directory = base_path($relativeDirectory);
        $imagePath = $directory . '/banner.jpg';
        $sameTimestamp = 1700000000;

        mkdir($directory, 0775, true);

        try {
            file_put_contents($imagePath, 'first');
            touch($imagePath, $sameTimestamp);
            $firstUrl = getFile($relativeDirectory . '/banner.jpg');

            file_put_contents($imagePath, 'other');
            touch($imagePath, $sameTimestamp);
            $secondUrl = getFile($relativeDirectory . '/banner.jpg');

            $this->assertNotSame($firstUrl, $secondUrl);
            $this->assertStringContainsString('v=' . hash('sha256', 'first'), $firstUrl);
            $this->assertStringContainsString('v=' . hash('sha256', 'other'), $secondUrl);
        } finally {
            if (is_file($imagePath)) {
                unlink($imagePath);
            }
            if (is_dir($directory)) {
                rmdir($directory);
            }
        }
    }
}
