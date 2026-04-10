<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class TransportationRequestFormModel extends Model
{
    use HasFactory;

    protected $table = 'transportation_requests_forms';

    protected $fillable = [
        'form_id',
        'form_creator_id',
        'request_date',
        'requested_by',
        'destination',
        'date_time_from',
        'date_time_to',
        'purpose',
        'vehicle_type',
        'vehicle_quantity',
        'business_passengers',
        'division_personnel',
        'vehicle_id',
        'driver_name',
        'attachments',
        'status',
        'rejection_reason',
        'generated_filename',
    ];

    protected $casts = [
        'request_date' => 'date',
        'date_time_from' => 'datetime',
        'date_time_to' => 'datetime',
        'business_passengers' => 'array',
        'division_personnel' => 'array',
        'attachments' => 'array',
    ];

    public function normalizeAttachments(mixed $attachments = null): array
    {
        $source = $attachments ?? $this->attachments;

        if (is_string($source) && trim($source) !== '') {
            $decoded = json_decode($source, true);
            if (is_array($decoded)) {
                $source = $decoded;
            }
        }

        if (!is_array($source)) {
            return [];
        }

        return collect($source)
            ->map(function ($attachment) {
                if (is_string($attachment)) {
                    $filePath = trim($attachment);

                    return $filePath !== ''
                        ? [
                            'file_name' => basename($filePath),
                            'file_path' => $filePath,
                        ]
                        : null;
                }

                if (!is_array($attachment)) {
                    return null;
                }

                $filePath = trim((string) ($attachment['file_path'] ?? ''));
                if ($filePath === '') {
                    return null;
                }

                $fileName = trim((string) ($attachment['file_name'] ?? basename($filePath)));

                return array_filter([
                    'file_name' => $fileName !== '' ? $fileName : basename($filePath),
                    'file_path' => $filePath,
                    'process' => trim((string) ($attachment['process'] ?? '')),
                    'process_key' => trim((string) ($attachment['process_key'] ?? '')),
                    'source' => trim((string) ($attachment['source'] ?? '')),
                    'copy_key' => trim((string) ($attachment['copy_key'] ?? '')),
                    'stored_at' => trim((string) ($attachment['stored_at'] ?? '')),
                ], function ($value, $key) {
                    if (in_array($key, ['file_name', 'file_path'], true)) {
                        return true;
                    }

                    return $value !== '';
                }, ARRAY_FILTER_USE_BOTH);
            })
            ->filter(function ($attachment) {
                return is_array($attachment) && !empty($attachment['file_path']);
            })
            ->values()
            ->all();
    }

    public function upsertAttachment(array $attachment, bool $deletePreviousFile = true): array
    {
        $filePath = trim((string) ($attachment['file_path'] ?? ''));
        if ($filePath === '') {
            return $this->normalizeAttachments();
        }

        $fileName = trim((string) ($attachment['file_name'] ?? basename($filePath)));
        $normalizedAttachment = [
            'file_name' => $fileName !== '' ? $fileName : basename($filePath),
            'file_path' => $filePath,
            'process' => trim((string) ($attachment['process'] ?? '')),
            'process_key' => trim((string) ($attachment['process_key'] ?? '')),
            'source' => trim((string) ($attachment['source'] ?? '')),
            'copy_key' => trim((string) ($attachment['copy_key'] ?? '')),
            'stored_at' => trim((string) ($attachment['stored_at'] ?? now()->toDateTimeString())),
        ];

        $normalizedAttachment = array_filter($normalizedAttachment, function ($value, $key) {
            if (in_array($key, ['file_name', 'file_path'], true)) {
                return true;
            }

            return $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        $attachments = $this->normalizeAttachments();

        $processKey = trim((string) ($normalizedAttachment['process_key'] ?? ''));
        $existingIndex = collect($attachments)->search(function (array $existingAttachment) use ($processKey, $normalizedAttachment) {
            if ($processKey !== '') {
                return trim((string) ($existingAttachment['process_key'] ?? '')) === $processKey;
            }

            return trim((string) ($existingAttachment['file_path'] ?? '')) === (string) ($normalizedAttachment['file_path'] ?? '');
        });

        if ($existingIndex !== false) {
            $existingAttachment = $attachments[$existingIndex] ?? [];
            $previousPath = trim((string) ($existingAttachment['file_path'] ?? ''));
            $nextPath = trim((string) ($normalizedAttachment['file_path'] ?? ''));

            if (
                $deletePreviousFile
                && $previousPath !== ''
                && $nextPath !== ''
                && $previousPath !== $nextPath
                && Storage::disk('public')->exists($previousPath)
            ) {
                Storage::disk('public')->delete($previousPath);
            }

            $attachments[$existingIndex] = array_merge($existingAttachment, $normalizedAttachment);
        } else {
            $attachments[] = $normalizedAttachment;
        }

        $this->forceFill([
            'attachments' => array_values($attachments),
        ])->save();

        return $attachments;
    }

    public function getRequestorNameAttribute(): string
    {
        $personnel = $this->division_personnel;

        if (is_array($personnel) && isset($personnel[0]['name']) && $personnel[0]['name'] !== '') {
            return (string) $personnel[0]['name'];
        }

        return (string) $this->requested_by;
    }

    public function getRequestorPositionAttribute(): string
    {
        $personnel = $this->division_personnel;

        if (is_array($personnel) && isset($personnel[0]['position']) && $personnel[0]['position'] !== '') {
            return (string) $personnel[0]['position'];
        }

        return 'N/A';
    }

    public function dailyDriversTripTicket(): HasOne
    {
        return $this->hasOne(DailyDriversTripTicket::class, 'transportation_request_form_id');
    }

    public function fuelIssuanceRecords(): HasMany
    {
        return $this->hasMany(FuelIssuance::class, 'transportation_request_form_id');
    }

    public function driverPerformanceEvaluations(): HasMany
    {
        return $this->hasMany(DriverPerformanceEvaluation::class, 'transportation_request_form_id');
    }

    public function driverPerformanceEvaluation(): HasOne
    {
        return $this->hasOne(DriverPerformanceEvaluation::class, 'transportation_request_form_id')->latestOfMany('id');
    }
}
