<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('driver_performance_evaluations')) {
            return;
        }

        $this->dropLegacySingleColumnUniqueIndexes();
        $this->ensureRequestCopyUniqueIndex();
        $this->backfillMissingEvaluationCopies();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible data repair migration.
    }

    private function dropLegacySingleColumnUniqueIndexes(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM driver_performance_evaluations'))
            ->groupBy('Key_name');

        foreach ($indexes as $indexName => $rows) {
            if (strtoupper((string) $indexName) === 'PRIMARY') {
                continue;
            }

            $first = collect($rows)->first();
            $isUnique = (int) ($first->Non_unique ?? 1) === 0;
            if (!$isUnique) {
                continue;
            }

            $columns = collect($rows)
                ->sortBy('Seq_in_index')
                ->pluck('Column_name')
                ->map(function ($value) {
                    return (string) $value;
                })
                ->values()
                ->all();

            if (count($columns) === 1 && $columns[0] === 'transportation_request_form_id') {
                $safeIndexName = str_replace('`', '', (string) $indexName);
                DB::statement('ALTER TABLE `driver_performance_evaluations` DROP INDEX `' . $safeIndexName . '`');
            }
        }
    }

    private function ensureRequestCopyUniqueIndex(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM driver_performance_evaluations'))
            ->groupBy('Key_name');

        $indexRows = $indexes->get('driver_perf_eval_request_copy_unique');

        if ($indexRows) {
            $first = collect($indexRows)->first();
            $isUnique = (int) ($first->Non_unique ?? 1) === 0;
            $columns = collect($indexRows)
                ->sortBy('Seq_in_index')
                ->pluck('Column_name')
                ->map(function ($value) {
                    return (string) $value;
                })
                ->values()
                ->all();

            $isExpectedIndex = $isUnique
                && count($columns) === 2
                && $columns[0] === 'transportation_request_form_id'
                && $columns[1] === 'copy_key';

            if ($isExpectedIndex) {
                return;
            }

            DB::statement('ALTER TABLE `driver_performance_evaluations` DROP INDEX `driver_perf_eval_request_copy_unique`');
        }

        DB::statement('ALTER TABLE `driver_performance_evaluations` ADD UNIQUE `driver_perf_eval_request_copy_unique` (`transportation_request_form_id`, `copy_key`)');
    }

    private function backfillMissingEvaluationCopies(): void
    {
        $requestRows = DB::table('transportation_requests_forms as trf')
            ->join('driver_performance_evaluations as dpe', 'dpe.transportation_request_form_id', '=', 'trf.id')
            ->select('trf.id', 'trf.driver_name', 'trf.status')
            ->distinct()
            ->orderBy('trf.id')
            ->get();

        foreach ($requestRows as $requestRow) {
            $requestId = (int) ($requestRow->id ?? 0);
            if ($requestId <= 0) {
                continue;
            }

            $driverNames = $this->extractDriverNames((string) ($requestRow->driver_name ?? ''));
            if (empty($driverNames)) {
                $driverNames = ['N/A'];
            }

            $existingRows = DB::table('driver_performance_evaluations')
                ->where('transportation_request_form_id', $requestId)
                ->orderBy('id')
                ->get(['id', 'copy_key', 'copy_number', 'driver_name']);

            $existingCopyKeys = collect($existingRows)
                ->pluck('copy_key')
                ->map(function ($value) {
                    return trim((string) $value);
                })
                ->filter(function (string $value) {
                    return $value !== '';
                })
                ->values()
                ->all();

            $insertedRows = 0;

            foreach ($driverNames as $index => $driverName) {
                $copyNumber = $index + 1;
                $normalizedDriverName = trim((string) $driverName) !== '' ? trim((string) $driverName) : 'N/A';
                $copyKey = $this->buildCopyKey($requestId, $normalizedDriverName, $copyNumber);

                if (in_array($copyKey, $existingCopyKeys, true)) {
                    continue;
                }

                $legacyRow = collect($existingRows)->first(function ($row) use ($normalizedDriverName) {
                    $rowCopyKey = trim((string) ($row->copy_key ?? ''));
                    $rowDriverName = strtolower(trim((string) ($row->driver_name ?? '')));

                    return $rowCopyKey === '' && $rowDriverName === strtolower($normalizedDriverName);
                });

                if ($legacyRow) {
                    DB::table('driver_performance_evaluations')
                        ->where('id', (int) $legacyRow->id)
                        ->update([
                            'copy_key' => $copyKey,
                            'copy_number' => $copyNumber,
                            'driver_name' => $normalizedDriverName,
                            'updated_at' => now(),
                        ]);

                    $existingCopyKeys[] = $copyKey;
                    continue;
                }

                DB::table('driver_performance_evaluations')->insert([
                    'transportation_request_form_id' => $requestId,
                    'copy_key' => $copyKey,
                    'copy_number' => $copyNumber,
                    'driver_name' => $normalizedDriverName,
                    'status' => 'Pending',
                    'overall_rating' => null,
                    'timeliness_score' => null,
                    'safety_score' => null,
                    'compliance_score' => null,
                    'evaluator_name' => null,
                    'evaluation_payload' => null,
                    'comments' => null,
                    'evaluated_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $existingCopyKeys[] = $copyKey;
                $insertedRows += 1;
            }

            if ($insertedRows > 0 && strtolower(trim((string) ($requestRow->status ?? ''))) === 'completed') {
                DB::table('transportation_requests_forms')
                    ->where('id', $requestId)
                    ->update([
                        'status' => 'For Evaluation',
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    private function extractDriverNames(string $value): array
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return [];
        }

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            $tokens = $decoded;
        } else {
            $tokens = preg_split('/\s*,\s*|\s*;\s*|\R+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return collect($tokens)
            ->map(function ($token): string {
                if (is_array($token)) {
                    return trim((string) ($token['driver_name'] ?? $token['name'] ?? ''));
                }

                return trim((string) $token);
            })
            ->filter(function (string $name): bool {
                return $name !== '';
            })
            ->unique()
            ->values()
            ->all();
    }

    private function buildCopyKey(int $requestId, string $driverName, int $copyNumber): string
    {
        $seed = $requestId . '|' . strtolower(trim($driverName)) . '|' . $copyNumber;

        return substr(hash('sha256', $seed), 0, 32);
    }
};
