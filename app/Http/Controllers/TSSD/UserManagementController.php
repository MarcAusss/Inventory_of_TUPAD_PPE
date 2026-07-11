<?php

namespace App\Http\Controllers\TSSD;

use App\Http\Controllers\Controller;
use App\Http\Requests\TSSD\UpdateUserRequest;
use App\Models\Province;
use App\Models\Role;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display all system users.
     */
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->query('search')
        );

        $roleId = $request->integer('role_id');

        $provinceId = $request->integer(
            'province_id'
        );

        $users = User::query()
            ->with([
                'role',
                'province',
            ])
            ->when(
                $search,
                function (
                    Builder $query
                ) use ($search): void {
                    $query->where(
                        function (
                            Builder $query
                        ) use ($search): void {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'username',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'role',
                                    fn (
                                        Builder $roleQuery
                                    ) => $roleQuery->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                )
                                ->orWhereHas(
                                    'province',
                                    fn (
                                        Builder $provinceQuery
                                    ) => $provinceQuery->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                );
                        }
                    );
                }
            )
            ->when(
                $roleId > 0,
                fn (Builder $query): Builder => $query->where(
                    'role_id',
                    $roleId
                )
            )
            ->when(
                $provinceId > 0,
                fn (Builder $query): Builder => $query->where(
                    'province_id',
                    $provinceId
                )
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::query()
            ->orderBy('name')
            ->get();

        $provinces = Province::query()
            ->orderBy('name')
            ->get();

        return view(
            'tssd.users.index',
            compact(
                'users',
                'roles',
                'provinces',
                'search',
                'roleId',
                'provinceId'
            )
        );
    }

    /**
     * Display a single user.
     */
    public function show(User $user): View
    {
        $user->load([
            'role',
            'province',
        ]);

        return view(
            'tssd.users.show',
            compact('user')
        );
    }

    /**
     * Show the user editing form.
     */
    public function edit(User $user): View
    {
        $user->load([
            'role',
            'province',
        ]);

        $roles = Role::query()
            ->orderBy('name')
            ->get();

        $provinces = Province::query()
            ->orderBy('name')
            ->get();

        return view(
            'tssd.users.edit',
            compact(
                'user',
                'roles',
                'provinces'
            )
        );
    }

    /**
     * Update the selected user.
     */
    public function update(
        UpdateUserRequest $request,
        User $user,
        UserManagementService $service
    ): RedirectResponse {
        $updatedUser = $service->update(
            $user,
            $request->validated()
        );

        return redirect()
            ->route(
                'tssd.users.show',
                $updatedUser
            )
            ->with(
                'success',
                'User account updated successfully.'
            );
    }
}
