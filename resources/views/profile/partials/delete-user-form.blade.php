<section>
    <header>
        <h2>{{ __('Delete Account') }}</h2>
        <p>{{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}</p>
    </header>

    <form method="post" action="{{ route('profile.destroy') }}" class="mt-4">
        @csrf
        @method('delete')

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input type="password" id="password" name="password" class="form-control" autocomplete="current-password">
            @if ($errors->userDeletion->has('password'))
                <div class="text-danger mt-2">{{ $errors->userDeletion->first('password') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-danger">{{ __('Delete Account') }}</button>
        </div>
    </form>
</section>
