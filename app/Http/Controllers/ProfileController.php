<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Support\StoredUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except(['avatar', 'remove_avatar']);
        $data = StoredUpload::apply($request, $data, 'avatar', 'avatar_path', 'avatars', $user->avatar_path);

        $user->update($data);

        return redirect()->route('profile.edit')->with('success', 'Ficha actualizada.');
    }

    public function password(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->string('password')->toString(),
        ]);

        $request->session()->regenerate();

        return redirect()->route('profile.edit')->with('success', 'Contraseña actualizada.');
    }
}
