<?php

namespace Tests\Feature;

use App\Services\PrivateDocumentMigrator;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateDocumentMigratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_dry_run_does_not_write_or_delete_and_apply_moves_after_checksum(): void
    {
        Storage::disk('public')->put('pelanggan/ktp.png', 'private-document-content');
        $migrator = app(PrivateDocumentMigrator::class);

        $dryRun = $migrator->process('pelanggan/ktp.png', false, 'pelanggan');
        $this->assertSame('planned', $dryRun['status']);
        Storage::disk('public')->assertExists('pelanggan/ktp.png');
        Storage::disk('local')->assertMissing('pelanggan/ktp.png');

        $applied = $migrator->process('pelanggan/ktp.png', true, 'pelanggan');
        $this->assertSame('moved', $applied['status']);
        Storage::disk('public')->assertMissing('pelanggan/ktp.png');
        Storage::disk('local')->assertExists('pelanggan/ktp.png');
        $this->assertSame(
            hash('sha256', 'private-document-content'),
            hash('sha256', Storage::disk('local')->get('pelanggan/ktp.png'))
        );
    }

    public function test_conflicting_private_file_is_never_overwritten_or_removed(): void
    {
        Storage::disk('public')->put('bukti-pembayaran/bukti.jpg', 'source-content');
        Storage::disk('local')->put('bukti-pembayaran/bukti.jpg', 'existing-private-content');

        $result = app(PrivateDocumentMigrator::class)->process('bukti-pembayaran/bukti.jpg', true, 'bukti-pembayaran');

        $this->assertSame('conflict', $result['status']);
        $this->assertSame('source-content', Storage::disk('public')->get('bukti-pembayaran/bukti.jpg'));
        $this->assertSame('existing-private-content', Storage::disk('local')->get('bukti-pembayaran/bukti.jpg'));
    }

    public function test_identical_copy_is_only_removed_after_hash_match_and_rerun_is_safe(): void
    {
        Storage::disk('public')->put('pelanggan/ktp.png', 'same-content');
        Storage::disk('local')->put('pelanggan/ktp.png', 'same-content');
        $migrator = app(PrivateDocumentMigrator::class);

        $result = $migrator->process('pelanggan/ktp.png', true, 'pelanggan');
        $this->assertSame('moved', $result['status']);
        Storage::disk('public')->assertMissing('pelanggan/ktp.png');
        Storage::disk('local')->assertExists('pelanggan/ktp.png');

        $rerun = $migrator->process('pelanggan/ktp.png', true, 'pelanggan');
        $this->assertSame('already_private', $rerun['status']);
        Storage::disk('local')->assertExists('pelanggan/ktp.png');
    }

    public function test_missing_or_unsafe_paths_are_reported_without_side_effects(): void
    {
        $migrator = app(PrivateDocumentMigrator::class);

        $missing = $migrator->process('pelanggan/missing.png', true, 'pelanggan');
        $unsafe = $migrator->process('../private/secret.pdf', true, 'pelanggan');

        $this->assertSame('missing', $missing['status']);
        $this->assertSame('failed', $unsafe['status']);
    }
}
