<?php

namespace App\Console\Commands;

use App\Models\Pelanggan;
use App\Models\Pembayaran;
use Illuminate\Console\Command;
use App\Services\PrivateDocumentMigrator;

class SecureLegacyDocuments extends Command
{
    protected $signature = 'documents:secure-legacy {--apply : Copy legacy public documents to private storage, verify, then remove public copies}';
    protected $description = 'Safely move existing KTP and payment proof files from public to private storage';

    public function handle(PrivateDocumentMigrator $migrator): int
    {
        $apply = (bool) $this->option('apply');
        $counts = array_fill_keys(['planned', 'moved', 'already_private', 'identical_copy', 'conflict', 'missing', 'failed'], 0);

        foreach ([
            ['model' => Pelanggan::class, 'column' => 'foto_ktp', 'label' => 'KTP', 'directory' => 'pelanggan'],
            ['model' => Pembayaran::class, 'column' => 'bukti_pembayaran', 'label' => 'Bukti pembayaran', 'directory' => 'bukti-pembayaran'],
        ] as $definition) {
            $model = $definition['model'];
            $column = $definition['column'];

            $model::query()->whereNotNull($column)->orderBy($model::make()->getKeyName())
                ->chunk(100, function ($records) use ($apply, &$counts, $column, $definition, $migrator): void {
                    foreach ($records as $record) {
                        $path = $record->{$column};
                        $result = $migrator->process((string) $path, $apply, $definition['directory']);
                        $counts[$result['status']]++;
                        $line = "{$definition['label']} {$record->getKey()} ({$path}): {$result['message']}";
                        if (in_array($result['status'], ['conflict', 'missing', 'failed'], true)) $this->error($line);
                        elseif ($result['status'] === 'planned') $this->line('Rencana: '.$line);
                        else $this->comment($line);
                    }
                });
        }

        $this->info(($apply ? 'Selesai' : 'Dry-run selesai').sprintf(
            ': %d direncanakan, %d dipindahkan, %d sudah privat, %d salinan identik, %d konflik, %d hilang, %d gagal.',
            $counts['planned'], $counts['moved'], $counts['already_private'], $counts['identical_copy'],
            $counts['conflict'], $counts['missing'], $counts['failed']
        ));
        if (! $apply) {
            $this->comment('Buat backup di luar document root, periksa daftar di atas, lalu jalankan kembali dengan --apply.');
        }

        return ($counts['conflict'] + $counts['missing'] + $counts['failed']) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
