<?php

namespace App\Http\Requests\TSSD;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTssd() === true;
    }

    public function rules(): array
    {
        /** @var User|null $managedUser */
        $managedUser = $this->route('user');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'username' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique(
                    'users',
                    'username'
                )->ignore($managedUser?->id),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(
                    'users',
                    'email'
                )->ignore($managedUser?->id),
            ],

            'role_id' => [
                'required',
                'integer',
                Rule::exists(
                    'roles',
                    'id'
                ),
            ],

            'province_id' => [
                'nullable',
                'integer',
                Rule::exists(
                    'provinces',
                    'id'
                ),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:128',
                'confirmed',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var User|null $managedUser */
                $managedUser = $this->route('user');

                $selectedRole = Role::query()
                    ->find(
                        $this->integer('role_id')
                    );

                if (! $selectedRole) {
                    return;
                }

                if (
                    $selectedRole->name === 'Provincial Office'
                    && ! $this->filled('province_id')
                ) {
                    $validator->errors()->add(
                        'province_id',
                        'A province is required for a Provincial Office account.'
                    );
                }

                if (
                    $managedUser
                    && $managedUser->is($this->user())
                    && (int) $managedUser->role_id
                    !== $this->integer('role_id')
                ) {
                    $validator->errors()->add(
                        'role_id',
                        'You cannot change your own role while logged in.'
                    );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $role = Role::query()
            ->find(
                (int) $this->input('role_id')
            );

        $this->merge([
            'name' => trim(
                (string) $this->input('name')
            ),

            'username' => strtolower(
                trim(
                    (string) $this->input('username')
                )
            ),

            'email' => strtolower(
                trim(
                    (string) $this->input('email')
                )
            ),

            /*
             * Automatically clear province for non-provincial roles.
             */
            'province_id' => $role?->name === 'Provincial Office'
                ? $this->input('province_id')
                : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Enter the user’s full name.',

            'username.required' => 'Enter a username.',

            'username.alpha_dash' => 'The username may contain letters, numbers, dashes, and underscores only.',

            'username.unique' => 'That username is already assigned to another account.',

            'email.required' => 'Enter an email address.',

            'email.email' => 'Enter a valid email address.',

            'email.unique' => 'That email address is already assigned to another account.',

            'role_id.required' => 'Select a user role.',

            'role_id.exists' => 'The selected role does not exist.',

            'province_id.exists' => 'The selected province does not exist.',

            'password.min' => 'The new password must contain at least 8 characters.',

            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
