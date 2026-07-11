<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserManagementService extends BaseService
{
    /**
     * Update a managed system user.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function update(
        User $managedUser,
        array $data
    ): User {
        $this->requireTssd();

        return DB::transaction(
            function () use (
                $managedUser,
                $data
            ): User {
                $managedUser = User::query()
                    ->with([
                        'role',
                        'province',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($managedUser->id);

                $selectedRole = Role::query()
                    ->findOrFail(
                        (int) $data['role_id']
                    );

                $this->validateSelfUpdate(
                    $managedUser,
                    $selectedRole
                );

                $provinceId =
                    $selectedRole->name === 'Provincial Office'
                        ? (int) $data['province_id']
                        : null;

                $updateData = [
                    'name' => $data['name'],

                    'username' => $data['username'],

                    'email' => $data['email'],

                    'role_id' => $selectedRole->id,

                    'province_id' => $provinceId,
                ];

                if (
                    isset($data['password'])
                    && filled($data['password'])
                ) {
                    $updateData['password'] =
                        Hash::make(
                            $data['password']
                        );
                }

                $managedUser->update(
                    $updateData
                );

                return $managedUser->fresh([
                    'role',
                    'province',
                ]);
            }
        );
    }

    /**
     * Prevent a TSSD user from removing their own administrative access.
     */
    private function validateSelfUpdate(
        User $managedUser,
        Role $selectedRole
    ): void {
        if (! $managedUser->is($this->user())) {
            return;
        }

        if ($selectedRole->name !== 'TSSD Unit') {
            throw ValidationException::withMessages([
                'role_id' => 'You cannot remove your own TSSD role while logged in.',
            ]);
        }

        if ($managedUser->province_id !== null) {
            throw ValidationException::withMessages([
                'province_id' => 'A TSSD account cannot be assigned to a province.',
            ]);
        }
    }
}
