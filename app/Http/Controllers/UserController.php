<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Support\StoredUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();

        $users = User::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($request->string('role')->value(), fn ($query, $role) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => UserRole::options(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'user' => new User(['role' => UserRole::Admin]),
            'roles' => UserRole::options(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $this->attributes($request, new User);

        User::query()->create($data);

        return redirect()->route('users.index')->with('success', 'Usuario creado.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => UserRole::options(),
            'canDelete' => $this->canDelete($user),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        if ($this->isLastSuperAdmin($user) && $request->string('role')->toString() !== UserRole::SuperAdmin->value) {
            return back()
                ->withInput()
                ->with('error', 'Debe quedar al menos un super administrador.');
        }

        $user->update($this->attributes($request, $user));

        return redirect()->route('users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'No puede eliminar su propia cuenta.');
        }

        if ($this->isLastSuperAdmin($user)) {
            return back()->with('error', 'Debe quedar al menos un super administrador.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(UserRequest $request, User $user): array
    {
        $data = $request->safe()->except(['avatar', 'remove_avatar', 'password_confirmation']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return StoredUpload::apply($request, $data, 'avatar', 'avatar_path', 'avatars', $user->avatar_path);
    }

    private function canDelete(User $user): bool
    {
        return ! $user->is(auth()->user()) && ! $this->isLastSuperAdmin($user);
    }

    private function isLastSuperAdmin(User $user): bool
    {
        return $user->isSuperAdmin()
            && User::query()->where('role', UserRole::SuperAdmin)->count() === 1;
    }
}
