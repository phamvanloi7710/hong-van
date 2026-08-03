<?php

namespace Database\Seeders;

use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class DemoMediaSeeder extends Seeder
{
    public const PATH = 'demo/hong-van-demo-placeholder.png';

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Demo media cannot be seeded in production.');
        }

        $disk = (string) config('media.disk', 'public');
        $contents = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true,
        );

        if (! is_string($contents)) {
            throw new RuntimeException('The generated demo placeholder is invalid.');
        }

        Storage::disk($disk)->put(self::PATH, $contents, 'public');

        Media::query()->updateOrCreate(
            ['disk' => $disk, 'path' => self::PATH],
            [
                'folder_id' => null,
                'original_filename' => 'hong-van-demo-placeholder.png',
                'normalized_filename' => 'hong-van-demo-placeholder.png',
                'extension' => 'png',
                'mime_type' => 'image/png',
                'size_bytes' => strlen($contents),
                'checksum_sha256' => hash('sha256', $contents),
                'width' => 1,
                'height' => 1,
                'status' => 'ready',
                'visibility' => 'public',
                'is_locked' => true,
                'title' => '[DEMO] Ảnh giữ chỗ do hệ thống tạo',
                'alt_text' => '[DEMO] Ảnh giữ chỗ',
                'caption' => 'Dữ liệu mẫu nội bộ, không đại diện cho sản phẩm hoặc năng lực thực tế.',
                'metadata' => ['demo' => true, 'generated_locally' => true],
            ],
        );
    }
}
