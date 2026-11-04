<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class PrivateDocumentMigrator
{
    /**
     * Move one relative document path without ever overwriting a private file.
     * Outcomes: planned, moved, already_private, identical_copy, conflict, missing, failed.
     */
    public function process(string $path, bool $apply, string $expectedDirectory): array
    {
        if (! $this->isSafePath($path, $expectedDirectory)) {
            return ['status' => 'failed', 'message' => 'Path database tidak valid atau di luar direktori dokumen yang diizinkan.'];
        }

        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $sourceExists = $public->exists($path);
        $targetExists = $private->exists($path);

        if (($sourceExists && ! $this->isWithinDiskRoot('public', $path))
            || ($targetExists && ! $this->isWithinDiskRoot('local', $path))) {
            return ['status' => 'failed', 'message' => 'Path file melewati root disk yang diizinkan. Tidak ada file yang diubah.'];
        }

        if (! $sourceExists && ! $targetExists) {
            return ['status' => 'missing', 'message' => 'File sumber dan file privat tidak ditemukan.'];
        }

        if (! $sourceExists && $targetExists) {
            return ['status' => 'already_private', 'message' => 'File privat tersedia; sumber publik sudah tidak ada.'];
        }

        $sourceHash = $this->hash($public->path($path));
        if ($sourceHash === null) {
            return ['status' => 'failed', 'message' => 'File sumber tidak dapat dibaca untuk checksum SHA-256.'];
        }

        if ($targetExists) {
            $targetHash = $this->hash($private->path($path));
            if ($targetHash === null || ! hash_equals($sourceHash, $targetHash)) {
                return ['status' => 'conflict', 'message' => 'Konflik checksum SHA-256. Sumber: '.($sourceHash ?: 'tidak terbaca').'; tujuan: '.($targetHash ?: 'tidak terbaca').'. Sumber dan tujuan dipertahankan.'];
            }

            if (! $apply) {
                return ['status' => 'identical_copy', 'message' => 'Sumber dan tujuan identik (SHA-256 cocok); dry-run tidak menghapus sumber.'];
            }

            $sourceHashBeforeDelete = $this->hash($public->path($path));
            $targetHashBeforeDelete = $this->hash($private->path($path));
            if ($sourceHashBeforeDelete === null || $targetHashBeforeDelete === null
                || ! hash_equals($sourceHash, $sourceHashBeforeDelete)
                || ! hash_equals($sourceHashBeforeDelete, $targetHashBeforeDelete)) {
                return ['status' => 'conflict', 'message' => 'Isi file berubah setelah pemeriksaan checksum; kedua file dipertahankan.'];
            }

            if (! $public->delete($path)) {
                return ['status' => 'failed', 'message' => 'Checksum cocok, tetapi salinan publik gagal dihapus. Kedua file dipertahankan.'];
            }

            return ['status' => 'moved', 'message' => 'Salinan publik identik dihapus setelah checksum diverifikasi.'];
        }

        if (! $apply) {
            return ['status' => 'planned', 'message' => 'File sumber tersedia dan tujuan privat belum ada.'];
        }

        $createdTarget = false;
        try {
            $private->makeDirectory(dirname($path));
            if (! $this->isWithinDiskRoot('local', $path)) {
                throw new RuntimeException('Folder tujuan privat melewati root storage yang diizinkan.');
            }
            $sourceStream = fopen($public->path($path), 'rb');
            if ($sourceStream === false) {
                throw new RuntimeException('File sumber tidak dapat dibuka.');
            }

            // Exclusive creation ('x') prevents a race from overwriting a file
            // that appeared after the existence check above.
            $targetStream = @fopen($private->path($path), 'x+b');
            if ($targetStream === false) {
                fclose($sourceStream);
                if ($private->exists($path)) {
                    $newTargetHash = $this->hash($private->path($path));
                    return $newTargetHash !== null && hash_equals($sourceHash, $newTargetHash)
                        ? ['status' => 'conflict', 'message' => 'File tujuan muncul saat pemindahan; tidak ditimpa. Jalankan ulang untuk pemeriksaan checksum.']
                        : ['status' => 'conflict', 'message' => 'File tujuan muncul saat pemindahan; tidak ditimpa. Sumber dipertahankan.'];
                }
                throw new RuntimeException('File tujuan privat tidak dapat dibuat secara eksklusif.');
            }
            $createdTarget = true;

            try {
                $copied = stream_copy_to_stream($sourceStream, $targetStream);
                fflush($targetStream);
            } finally {
                if (is_resource($sourceStream)) fclose($sourceStream);
                if (is_resource($targetStream)) fclose($targetStream);
            }

            if ($copied === false) {
                throw new RuntimeException('Penyalinan stream tidak selesai.');
            }

            $verifiedSourceHash = $this->hash($public->path($path));
            $targetHash = $this->hash($private->path($path));
            if ($verifiedSourceHash === null || $targetHash === null
                || ! hash_equals($sourceHash, $verifiedSourceHash)
                || ! hash_equals($verifiedSourceHash, $targetHash)) {
                $private->delete($path); // Only our exclusively-created partial target.
                throw new RuntimeException('Verifikasi SHA-256 gagal; file sumber dipertahankan.');
            }

            if (! $public->delete($path)) {
                return ['status' => 'failed', 'message' => 'File privat terverifikasi, tetapi sumber publik gagal dihapus; kedua salinan dipertahankan.'];
            }

            return ['status' => 'moved', 'message' => 'Disalin secara eksklusif, checksum SHA-256 cocok, lalu sumber dihapus.'];
        } catch (Throwable $exception) {
            if ($createdTarget && $private->exists($path)) {
                $private->delete($path);
            }
            return ['status' => 'failed', 'message' => $exception->getMessage().' File sumber tetap dipertahankan.'];
        }
    }

    private function isSafePath(string $path, string $expectedDirectory): bool
    {
        if ($path === '' || str_contains($path, "\0") || str_contains($path, '\\') || str_starts_with($path, '/')) {
            return false;
        }

        $segments = explode('/', $path);
        return $segments[0] === $expectedDirectory
            && count($segments) === 2
            && preg_match('/^[A-Za-z0-9._-]+$/D', $segments[1]) === 1
            && ! in_array('.', $segments, true)
            && ! in_array('..', $segments, true);
    }

    private function isWithinDiskRoot(string $diskName, string $relativePath): bool
    {
        $disk = Storage::disk($diskName);
        $absolutePath = $disk->path($relativePath);
        if (is_link($absolutePath)) {
            return false;
        }
        $root = realpath($disk->path(''));
        $parent = realpath(dirname($absolutePath));
        if ($root === false || $parent === false) {
            return false;
        }

        $root = rtrim(str_replace('\\', '/', $root), '/');
        $parent = str_replace('\\', '/', $parent);
        return $parent === $root || str_starts_with($parent, $root.'/');
    }

    private function hash(string $absolutePath): ?string
    {
        if (! is_file($absolutePath)) {
            return null;
        }

        $hash = @hash_file('sha256', $absolutePath);
        return $hash === false ? null : $hash;
    }
}
