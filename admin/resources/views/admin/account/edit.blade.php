<x-admin-layout>
    <x-slot name="header">Account</x-slot>

    <div class="max-w-2xl space-y-6">
        {{-- Email & Name --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-slate-800 mb-2">Email & Name</h3>
            <p class="text-sm text-slate-600 mb-4">Update your login email and display name.</p>

            <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('patch')
                <input type="hidden" name="_redirect" value="admin.account.edit">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full mt-1 rounded border-slate-300" autocomplete="name">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full mt-1 rounded border-slate-300" autocomplete="username">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
                @if (session('status') === 'profile-updated')
                    <span class="ml-3 text-sm text-green-600">Saved.</span>
                @endif
            </form>
        </div>

        {{-- Password --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-slate-800 mb-2">Change Password</h3>
            <p class="text-sm text-slate-600 mb-4">Use a strong password to keep your account secure.</p>

            <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('put')
                <div>
                    <label for="current_password" class="block text-sm font-medium text-slate-700">Current Password</label>
                    <input type="password" id="current_password" name="current_password"
                        class="w-full mt-1 rounded border-slate-300" autocomplete="current-password">
                    @error('current_password', 'updatePassword')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">New Password</label>
                    <input type="password" id="password" name="password"
                        class="w-full mt-1 rounded border-slate-300" autocomplete="new-password">
                    @error('password', 'updatePassword')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="w-full mt-1 rounded border-slate-300" autocomplete="new-password">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Change Password</button>
                @if (session('status') === 'password-updated')
                    <span class="ml-3 text-sm text-green-600">Password updated.</span>
                @endif
            </form>
        </div>
    </div>
</x-admin-layout>
