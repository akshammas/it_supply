<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->q . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(['status' => 'active'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = new User([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'], // hashed by the model cast
        ]);
        $user->role   = $data['role'];
        $user->status = $request->boolean('status') ? 'active' : 'inactive';
        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $role   = $data['role'];
        $status = $request->boolean('status') ? 'active' : 'inactive';

        // You can't demote or deactivate your own account here
        if ($user->is($request->user())) {
            $role   = $user->role;
            $status = 'active';
        }

        if ($this->wouldRemoveLastSuperAdmin($user, $role, $status)) {
            return back()->withInput()->withErrors([
                'role' => 'There must be at least one active Super Admin.',
            ]);
        }

        $user->name   = $data['name'];
        $user->email  = $data['email'];
        $user->role   = $role;
        $user->status = $status;

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['delete' => 'You cannot delete your own account.']);
        }

        if ($this->wouldRemoveLastSuperAdmin($user, null, null)) {
            return back()->withErrors(['delete' => 'You cannot delete the last active Super Admin.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }

    /**
     * True if this change would leave the system with no active super admin.
     * $newRole / $newStatus = null means the user is being deleted.
     */
    protected function wouldRemoveLastSuperAdmin(User $user, ?string $newRole, ?string $newStatus): bool
    {
        $isActiveSuper = $user->role === 'super_admin' && $user->status === 'active';
        $stillActiveSuper = $newRole === 'super_admin' && $newStatus === 'active';

        if (! $isActiveSuper || $stillActiveSuper) {
            return false;
        }

        return User::where('role', 'super_admin')
            ->where('status', 'active')
            ->where('id', '!=', $user->id)
            ->doesntExist();
    }
}