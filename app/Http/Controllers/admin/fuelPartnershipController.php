<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\FuelIssuancePartnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class fuelPartnershipController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $searchDate = preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $search) === 1 ? $search : null;

        $partnerships = FuelIssuancePartnership::query()
            ->when($search !== '', function ($query) use ($search, $searchDate) {
                $query->where(function ($nested) use ($search, $searchDate) {
                    $nested->where('partnership_name', 'like', '%' . $search . '%')
                        ->when($searchDate !== null, function ($dateNested) use ($searchDate) {
                            $dateNested->orWhereDate('valid_from', $searchDate)
                                ->orWhereDate('valid_until', $searchDate);
                        });
                });
            })
            ->orderByDesc('is_active')
            ->orderByDesc('valid_until')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.fuel_partnerships.fuel_partnerships', [
            'partnerships' => $partnerships,
            'search' => $search,
            'totalPartnerships' => FuelIssuancePartnership::query()->count(),
            'activePartnerships' => FuelIssuancePartnership::query()->where('is_active', true)->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $isActive = $request->boolean('is_active');

        DB::transaction(function () use ($validated, $isActive) {
            if ($isActive) {
                FuelIssuancePartnership::query()->update(['is_active' => false]);
            }

            FuelIssuancePartnership::query()->create([
                'partnership_name' => $validated['partnership_name'],
                'valid_from' => $validated['valid_from'],
                'valid_until' => $validated['valid_until'],
                'gasoline_price_per_liter' => round((float) $validated['gasoline_price_per_liter'], 2),
                'diesel_price_per_liter' => round((float) $validated['diesel_price_per_liter'], 2),
                'fuel_save_price_per_liter' => round((float) $validated['fuel_save_price_per_liter'], 2),
                'v_power_price_per_liter' => round((float) $validated['v_power_price_per_liter'], 2),
                'is_active' => $isActive,
            ]);

            $this->ensureAtLeastOneActivePartnership();
        });

        return redirect()
            ->route('admin.fuel_partnerships', $request->only('search', 'page'))
            ->with('fuel_partnership_success', 'Fuel partnership created successfully.');
    }

    public function update(Request $request, FuelIssuancePartnership $fuelPartnership)
    {
        $validated = $this->validatePayload($request);
        $isActive = $request->boolean('is_active');

        DB::transaction(function () use ($fuelPartnership, $validated, $isActive) {
            if ($isActive) {
                FuelIssuancePartnership::query()
                    ->whereKeyNot($fuelPartnership->id)
                    ->update(['is_active' => false]);
            }

            $fuelPartnership->update([
                'partnership_name' => $validated['partnership_name'],
                'valid_from' => $validated['valid_from'],
                'valid_until' => $validated['valid_until'],
                'gasoline_price_per_liter' => round((float) $validated['gasoline_price_per_liter'], 2),
                'diesel_price_per_liter' => round((float) $validated['diesel_price_per_liter'], 2),
                'fuel_save_price_per_liter' => round((float) $validated['fuel_save_price_per_liter'], 2),
                'v_power_price_per_liter' => round((float) $validated['v_power_price_per_liter'], 2),
                'is_active' => $isActive,
            ]);

            $this->ensureAtLeastOneActivePartnership();
        });

        return redirect()
            ->route('admin.fuel_partnerships', $request->only('search', 'page'))
            ->with('fuel_partnership_success', 'Fuel partnership updated successfully.');
    }

    public function activate(Request $request, FuelIssuancePartnership $fuelPartnership)
    {
        DB::transaction(function () use ($fuelPartnership) {
            FuelIssuancePartnership::query()
                ->whereKeyNot($fuelPartnership->id)
                ->update(['is_active' => false]);

            if (!$fuelPartnership->is_active) {
                $fuelPartnership->update(['is_active' => true]);
            }
        });

        return redirect()
            ->route('admin.fuel_partnerships', $request->only('search', 'page'))
            ->with('fuel_partnership_success', 'Fuel partnership set as active.');
    }

    public function destroy(Request $request, FuelIssuancePartnership $fuelPartnership)
    {
        DB::transaction(function () use ($fuelPartnership) {
            $fuelPartnership->delete();

            $this->ensureAtLeastOneActivePartnership();
        });

        return redirect()
            ->route('admin.fuel_partnerships', $request->only('search', 'page'))
            ->with('fuel_partnership_success', 'Fuel partnership deleted successfully.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'partnership_name' => ['required', 'string', 'max:255'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:valid_from'],
            'gasoline_price_per_liter' => ['required', 'numeric', 'min:0'],
            'diesel_price_per_liter' => ['required', 'numeric', 'min:0'],
            'fuel_save_price_per_liter' => ['required', 'numeric', 'min:0'],
            'v_power_price_per_liter' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function ensureAtLeastOneActivePartnership(): void
    {
        if (FuelIssuancePartnership::query()->where('is_active', true)->exists()) {
            return;
        }

        $fallback = FuelIssuancePartnership::query()
            ->orderByDesc('valid_until')
            ->orderByDesc('id')
            ->first();

        if ($fallback) {
            $fallback->update(['is_active' => true]);
        }
    }
}
