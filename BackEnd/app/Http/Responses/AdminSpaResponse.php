<?php

namespace App\Http\Responses;

use LogicException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class AdminSpaResponse
{
    /**
     * @var list<string>
     */
    private const ASSET_EXTENSIONS = [
        'css',
        'eot',
        'gif',
        'ico',
        'jpeg',
        'jpg',
        'js',
        'json',
        'png',
        'svg',
        'ttf',
        'txt',
        'webmanifest',
        'webp',
        'woff',
        'woff2',
        'xml',
    ];

    public function forPath(?string $path): BinaryFileResponse
    {
        $buildDirectory = $this->buildDirectory();
        $indexPath = $buildDirectory.DIRECTORY_SEPARATOR.'index.html';

        abort_unless(is_file($indexPath), 503, 'Admin application has not been built.');

        if ($path !== null && $path !== '' && ltrim($path, '/') !== 'index.html') {
            $assetPath = $this->resolveAssetPath($buildDirectory, $path);

            if ($assetPath !== null) {
                return response()->file($assetPath, $this->assetHeaders($assetPath));
            }

            abort_if($this->looksLikeAssetRequest($path), 404);
        }

        return $this->indexResponse($indexPath);
    }

    private function indexResponse(string $indexPath): BinaryFileResponse
    {
        $response = response()->file($indexPath, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Expires' => '0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        $response->setPrivate();
        $response->setMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->headers->addCacheControlDirective('no-cache');
        $response->headers->addCacheControlDirective('no-store');

        return $response;
    }

    private function buildDirectory(): string
    {
        $buildDirectory = config('admin.spa_path');

        if (! is_string($buildDirectory) || $buildDirectory === '') {
            throw new LogicException('Admin SPA build path is not configured.');
        }

        return $buildDirectory;
    }

    private function resolveAssetPath(string $buildDirectory, string $path): ?string
    {
        if (str_contains($path, "\0") || str_contains($path, '\\')) {
            return null;
        }

        $resolvedBuildDirectory = realpath($buildDirectory);

        if ($resolvedBuildDirectory === false) {
            return null;
        }

        $candidatePath = realpath(
            $resolvedBuildDirectory.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/')),
        );

        if ($candidatePath === false || ! is_file($candidatePath)) {
            return null;
        }

        $normalizedRoot = $this->normalizePath($resolvedBuildDirectory).'/';
        $normalizedCandidate = $this->normalizePath($candidatePath);

        if (! str_starts_with($normalizedCandidate, $normalizedRoot)) {
            return null;
        }

        return $candidatePath;
    }

    private function normalizePath(string $path): string
    {
        $normalizedPath = str_replace('\\', '/', $path);

        return PHP_OS_FAMILY === 'Windows' ? strtolower($normalizedPath) : $normalizedPath;
    }

    /**
     * @return array<string, string>
     */
    private function assetHeaders(string $assetPath): array
    {
        $cacheControl = preg_match('/-[a-z0-9]{8,}\.[^.]+$/i', basename($assetPath)) === 1
            ? 'public, max-age=31536000, immutable'
            : 'public, max-age=3600';

        return [
            'Cache-Control' => $cacheControl,
            'Content-Type' => $this->assetContentType($assetPath),
            'X-Content-Type-Options' => 'nosniff',
        ];
    }

    private function assetContentType(string $assetPath): string
    {
        return match (strtolower(pathinfo($assetPath, PATHINFO_EXTENSION))) {
            'css' => 'text/css; charset=UTF-8',
            'eot' => 'application/vnd.ms-fontobject',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'jpeg', 'jpg' => 'image/jpeg',
            'js' => 'text/javascript; charset=UTF-8',
            'json', 'webmanifest' => 'application/json; charset=UTF-8',
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            'ttf' => 'font/ttf',
            'txt' => 'text/plain; charset=UTF-8',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'xml' => 'application/xml; charset=UTF-8',
            default => 'application/octet-stream',
        };
    }

    private function looksLikeAssetRequest(string $path): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, self::ASSET_EXTENSIONS, true);
    }
}
