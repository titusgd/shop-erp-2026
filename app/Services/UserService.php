<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * @param  array{search?: string|null, per_page?: int|null}  $filters
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        return User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{name: string, username: string, email: string, password: string}  $data
     */
    public function create(array $data): User
    {
        return User::query()->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @param  array{name: string, username: string, email: string, password?: string|null}  $data
     */
    public function update(User $user, array $data): User
    {
        $user->name = $data['name'];
        $user->username = $data['username'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return $user->refresh();
    }

    /**
     * @throws ValidationException
     */
    public function delete(User $user, ?User $actor = null): void
    {
        if ($actor && $actor->is($user)) {
            throw ValidationException::withMessages([
                'user' => '無法刪除目前登入的帳號。',
            ]);
        }

        $user->delete();
    }
}
