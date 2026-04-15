<?php

namespace App\Support;

use App\Models\AssignatoryPersonnel;
use Illuminate\Support\Facades\Schema;

class AssignatoryPersonnelResolver
{
    private const DEFAULT_NAME = 'ENGR. EMILIO M. DOMAGAS JR.';
    private const DEFAULT_POSITION = 'Division Manager A, EOD.';

    public static function resolve(): array
    {
        try {
            if (!Schema::hasTable('assignatory_personnel')) {
                return self::defaults();
            }

            $hasActiveColumn = Schema::hasColumn('assignatory_personnel', 'is_active');

            $assignatory = null;

            if ($hasActiveColumn) {
                $assignatory = AssignatoryPersonnel::query()
                    ->where('is_active', true)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->first();
            }

            if (!$assignatory) {
                $assignatory = AssignatoryPersonnel::query()
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->first();
            }
        } catch (\Throwable) {
            return self::defaults();
        }

        $name = trim((string) ($assignatory?->name ?? ''));
        $position = trim((string) ($assignatory?->position ?? ''));

        return [
            'name' => $name !== '' ? $name : self::DEFAULT_NAME,
            'position' => $position !== '' ? $position : self::DEFAULT_POSITION,
        ];
    }

    public static function defaults(): array
    {
        return [
            'name' => self::DEFAULT_NAME,
            'position' => self::DEFAULT_POSITION,
        ];
    }
}
