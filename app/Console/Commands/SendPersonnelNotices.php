<?php

namespace App\Console\Commands;

use App\Support\PersonnelNotifier;
use Illuminate\Console\Command;

class SendPersonnelNotices extends Command
{
    protected $signature = 'notices:send {--force : Enviar aunque el resumen de hoy ya haya salido}';

    protected $description = 'Envía los avisos diarios activados en la configuración general.';

    public function handle(): int
    {
        $result = PersonnelNotifier::digest((bool) $this->option('force'));

        $this->info(match ($result) {
            'sent' => 'Se enviaron los avisos.',
            'already' => 'Los avisos de hoy ya se enviaron.',
            default => 'No hay avisos para enviar.',
        });

        return self::SUCCESS;
    }
}
