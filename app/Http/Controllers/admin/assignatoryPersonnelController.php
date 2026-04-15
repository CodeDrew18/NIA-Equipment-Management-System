<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\AssignatoryPersonnel;
use App\Support\AssignatoryPersonnelResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class assignatoryPersonnelController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $hasActiveColumn = $this->hasActiveColumn();

        $assignatories = AssignatoryPersonnel::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%' . $search . '%')
                        ->orWhere('position', 'like', '%' . $search . '%');
                });
            })
            ->when($hasActiveColumn, function ($query) {
                $query->orderByDesc('is_active');
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $currentRecord = null;

        if ($hasActiveColumn) {
            $currentRecord = AssignatoryPersonnel::query()
                ->where('is_active', true)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();
        }

        if (!$currentRecord) {
            $currentRecord = AssignatoryPersonnel::query()
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();
        }

        return view('admin.assignatories.assignatories', [
            'assignatories' => $assignatories,
            'search' => $search,
            'currentAssignatory' => AssignatoryPersonnelResolver::resolve(),
            'currentAssignatoryId' => $currentRecord?->id,
            'supportsActiveFlag' => $hasActiveColumn,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $shouldActivate = $request->boolean('is_active');
        $hasActiveColumn = $this->hasActiveColumn();

        DB::transaction(function () use ($validated, $shouldActivate, $hasActiveColumn) {
            $payload = $validated;

            if ($hasActiveColumn) {
                if ($shouldActivate) {
                    AssignatoryPersonnel::query()->update(['is_active' => false]);
                }

                $payload['is_active'] = $shouldActivate;
            }

            AssignatoryPersonnel::query()->create($payload);

            if ($hasActiveColumn) {
                $this->ensureAtLeastOneActiveAssignatory();
            }
        });

        return redirect()
            ->route('admin.assignatories', $request->only('search', 'page'))
            ->with('assignatory_success', 'Assignatory saved successfully.');
    }

    public function update(Request $request, AssignatoryPersonnel $assignatoryPersonnel)
    {
        $validated = $this->validatePayload($request);
        $shouldActivate = $request->boolean('is_active');
        $hasActiveColumn = $this->hasActiveColumn();

        DB::transaction(function () use ($assignatoryPersonnel, $validated, $shouldActivate, $hasActiveColumn) {
            if ($hasActiveColumn && $shouldActivate) {
                AssignatoryPersonnel::query()
                    ->whereKeyNot($assignatoryPersonnel->id)
                    ->update(['is_active' => false]);
            }

            $payload = $validated;
            if ($hasActiveColumn) {
                $payload['is_active'] = $shouldActivate;
            }

            $assignatoryPersonnel->update($payload);

            if ($hasActiveColumn) {
                $this->ensureAtLeastOneActiveAssignatory();
            }
        });

        return redirect()
            ->route('admin.assignatories', $request->only('search', 'page'))
            ->with('assignatory_success', 'Assignatory updated successfully.');
    }

    public function activate(Request $request, AssignatoryPersonnel $assignatoryPersonnel)
    {
        if (!$this->hasActiveColumn()) {
            return redirect()
                ->route('admin.assignatories', $request->only('search', 'page'))
                ->with('assignatory_success', 'Activate action is unavailable until active flag migration is applied.');
        }

        DB::transaction(function () use ($assignatoryPersonnel) {
            AssignatoryPersonnel::query()
                ->whereKeyNot($assignatoryPersonnel->id)
                ->update(['is_active' => false]);

            if (!$assignatoryPersonnel->is_active) {
                $assignatoryPersonnel->update(['is_active' => true]);
            }
        });

        return redirect()
            ->route('admin.assignatories', $request->only('search', 'page'))
            ->with('assignatory_success', 'Assignatory set as active.');
    }

    public function destroy(Request $request, AssignatoryPersonnel $assignatoryPersonnel)
    {
        $hasActiveColumn = $this->hasActiveColumn();
        $deletedWasActive = $hasActiveColumn ? (bool) $assignatoryPersonnel->is_active : false;

        $assignatoryPersonnel->delete();

        if ($hasActiveColumn && $deletedWasActive) {
            $this->ensureAtLeastOneActiveAssignatory();
        }

        return redirect()
            ->route('admin.assignatories', $request->only('search', 'page'))
            ->with('assignatory_success', 'Assignatory deleted successfully.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
        ]);
    }

    private function hasActiveColumn(): bool
    {
        return Schema::hasTable('assignatory_personnel')
            && Schema::hasColumn('assignatory_personnel', 'is_active');
    }

    private function ensureAtLeastOneActiveAssignatory(): void
    {
        if (!$this->hasActiveColumn()) {
            return;
        }

        if (AssignatoryPersonnel::query()->where('is_active', true)->exists()) {
            return;
        }

        $fallback = AssignatoryPersonnel::query()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if ($fallback) {
            $fallback->update(['is_active' => true]);
        }
    }
}
