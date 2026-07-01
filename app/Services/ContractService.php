<?php

namespace App\Services;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class ContractService
{
    public function __construct(
        private HrSettingsService $hrSettings,
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Contract::query()->with('user');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['expiring'])) {
            $days = $this->hrSettings->getSettings()['contract_expiry_warning_days'] ?? 30;
            $query->where('status', ContractStatus::Active)
                ->whereNotNull('end_date')
                ->whereBetween('end_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
        }

        return $query->orderByDesc('start_date')->paginate();
    }

    public function create(array $data): Contract
    {
        Contract::query()
            ->where('user_id', $data['user_id'])
            ->where('status', ContractStatus::Active)
            ->update(['status' => ContractStatus::Terminated]);

        return Contract::create([
            ...$data,
            'status' => ContractStatus::Active,
        ]);
    }

    public function update(Contract $contract, array $data): Contract
    {
        $contract->update($data);

        return $contract->fresh()->load('user');
    }

    public function renew(Contract $contract, array $data): Contract
    {
        if ($contract->status !== ContractStatus::Active) {
            throw ValidationException::withMessages([
                'contract' => [__('errors.contract_not_active')],
            ]);
        }

        $contract->update(['status' => ContractStatus::Renewed]);

        return $this->create([
            ...$data,
            'user_id' => $contract->user_id,
            'renewed_from_id' => $contract->id,
        ]);
    }

    /** @return Collection<int, Contract> */
    public function expiring(): Collection
    {
        $days = $this->hrSettings->getSettings()['contract_expiry_warning_days'] ?? 30;

        return Contract::query()
            ->where('status', ContractStatus::Active)
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays($days)->toDateString()])
            ->with('user')
            ->orderBy('end_date')
            ->get();
    }

    public function expiryReminderCount(): int
    {
        return $this->expiring()->count();
    }
}
