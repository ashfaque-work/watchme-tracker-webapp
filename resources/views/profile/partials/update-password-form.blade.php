<section>
    <header>
        <h2>{{ __('Update Password') }}</h2>
        <p>{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
    </header>

    <form method="post" action="{{ route('profile.updatePassword') }}" class="mt-4">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="current_password" class="form-label">{{ __('Current Password') }}</label>
            <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password">
            @if ($errors->has('current_password'))
                <div class="text-danger mt-2">{{ $errors->first('current_password') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('New Password') }}</label>
            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
            @if ($errors->has('password'))
                <div class="text-danger mt-2">{{ $errors->first('password') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
            @if ($errors->has('password_confirmation'))
                <div class="text-danger mt-2">{{ $errors->first('password_confirmation') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

            @if (session('status') === 'password-updated')
                <p class="text-success mt-2">{{ __('Password updated successfully.') }}</p>
            @endif
        </div>
    </form>
</section>