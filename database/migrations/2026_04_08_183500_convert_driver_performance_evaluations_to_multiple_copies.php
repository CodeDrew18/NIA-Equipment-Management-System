<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('driver_performance_evaluations', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_performance_evaluations', 'copy_key')) {
                $table->string('copy_key', 64)->nullable()->after('transportation_request_form_id');
            }

            if (!Schema::hasColumn('driver_performance_evaluations', 'copy_number')) {
                $table->unsignedSmallInteger('copy_number')->default(1)->after('copy_key');
            }
        });

        // Remove one-to-one uniqueness before inserting additional per-driver copies.
        $this->dropLegacyUniqueIndexes();

        $rows = DB::table('driver_performance_evaluations')
            ->orderBy('id')
            ->get([
                'id',
                'transportation_request_form_id',
                'driver_name',
                'status',
                'created_at',
                'updated_at',
            ]);

        foreach ($rows as $row) {
            $driverNames = $this->extractDriverNames((string) ($row->driver_name ?? ''));
            if (empty($driverNames)) {
                $driverNames = ['N/A'];
            }

            $primaryDriver = $driverNames[0];

            DB::table('driver_performance_evaluations')
                ->where('id', $row->id)
                ->update([
                    'driver_name' => $primaryDriver,
                    'copy_key' => $this->buildCopyKey((int) $row->transportation_request_form_id, $primaryDriver, 1),
                    'copy_number' => 1,
                    'updated_at' => now(),
                ]);

            foreach (array_slice($driverNames, 1) as $index => $driverName) {
                $copyNumber = $index + 2;
                $copyKey = $this->buildCopyKey((int) $row->transportation_request_form_id, $driverName, $copyNumber);

                DB::table('driver_performance_evaluations')->updateOrInsert(
                    [
                        'transportation_request_form_id' => (int) $row->transportation_request_form_id,
                        'copy_key' => $copyKey,
                    ],
                    [
                        'copy_number' => $copyNumber,
                        'driver_name' => $driverName,
                        'status' => 'Pending',
                        'overall_rating' => null,
                        'timeliness_score' => null,
                        'safety_score' => null,
                        'compliance_score' => null,
                        'evaluator_name' => null,
                        'evaluation_payload' => null,
                        'comments' => null,
                        'evaluated_at' => null,
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        try {
            Schema::table('driver_performance_evaluations', function (Blueprint $table) {
                $table->unique([
                    'transportation_request_form_id',
                    'copy_key',
                ], 'driver_perf_eval_request_copy_unique');
            });
        } catch (\Throwable) {
            // Ignore if the index already exists in this environment.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('driver_performance_evaluations', function (Blueprint $table) {
                $table->dropUnique('driver_perf_eval_request_copy_unique');
            });
        } catch (\Throwable) {
            // Ignore missing index.
        }

        $rows = DB::table('driver_performance_evaluations')
            ->orderBy('id')
            ->get([
                'id',
                'transportation_request_form_id',
                'driver_name',
                'status',
                'overall_rating',
                'timeliness_score',
                'safety_score',
                'compliance_score',
                'evaluator_name',
                'evaluation_payload',
                'comments',
                'evaluated_at',
                'created_at',
                'updated_at',
            ])
            ->groupBy('transportation_request_form_id');

        foreach ($rows as $requestId => $groupedRows) {
            /** @var Collection<int, object> $groupedRows */
            $firstRow = $groupedRows->first();
            if (!$firstRow) {
                continue;
            }

            $mergedDriverName = $groupedRows
                ->pluck('driver_name')
                ->map(function ($name) {
                    return trim((string) $name);
                })
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');

            if ($mergedDriverName === '') {
                $mergedDriverName = 'N/A';
            }

            DB::table('driver_performance_evaluations')
                ->where('id', $firstRow->id)
                ->update([
                    'driver_name' => $mergedDriverName,
                    'updated_at' => now(),
                ]);

            $redundantIds = $groupedRows
                ->pluck('id')
                ->filter(function ($id) use ($firstRow) {
                    return (int) $id !== (int) $firstRow->id;
                })
                ->values()
                ->all();

            if (!empty($redundantIds)) {
                DB::table('driver_performance_evaluations')
                    ->whereIn('id', $redundantIds)
                    ->delete();
            }
        }

        Schema::table('driver_performance_evaluations', function (Blueprint $table) {
            if (Schema::hasColumn('driver_performance_evaluations', 'copy_number')) {
                $table->dropColumn('copy_number');
            }

            if (Schema::hasColumn('driver_performance_evaluations', 'copy_key')) {
                $table->dropColumn('copy_key');
            }
        });

        try {
            Schema::table('driver_performance_evaluations', function (Blueprint $table) {
                $table->unique('transportation_request_form_id', 'driver_perf_eval_request_unique');
            });
        } catch (\Throwable) {
            // Ignore if index already exists.
        }
    }

    private function dropLegacyUniqueIndexes(): void
    {
        $legacyIndexes = [
            'driver_perf_eval_request_unique',
            'driver_performance_evaluations_transportation_request_form_id_unique',
        ];

        foreach ($legacyIndexes as $indexName) {
            try {
                Schema::table('driver_performance_evaluations', function (Blueprint $table) use ($indexName) {
                    $table->dropUnique($indexName);
                });
            } catch (\Throwable) {
                // Ignore missing legacy index.
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
            ->map(function ($token) {
                if (is_array($token)) {
                    return trim((string) ($token['driver_name'] ?? $token['name'] ?? ''));
                }

                return trim((string) $token);
            })
            ->filter(function (string $name) {
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
