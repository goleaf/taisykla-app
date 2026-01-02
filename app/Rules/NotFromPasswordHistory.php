<?php

namespace App\Rules;

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class NotFromPasswordHistory implements Rule
{
    private ?User $user;
    private int $limit;

    public function __construct(?User $user, int $limit = 10)
    {
        $this->user = $user;
        $this->limit = $limit;
    }

    public function passes($attribute, $value): bool
    {
        if (! $this->user) {
            return true;
        }

        if (Hash::check((string) $value, $this->user->password)) {
            return false;
        }

        $history = PasswordHistory::where('user_id', $this->user->id)
            ->latest('id')
            ->take($this->limit)
            ->get();

        foreach ($history as $entry) {
            if (Hash::check((string) $value, $entry->password_hash)) {
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return 'You cannot reuse a previous password.';
    }
}
