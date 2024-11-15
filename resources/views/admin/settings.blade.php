@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4">{{ __('Settings') }}</h2>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">{{ __('Edit User Log Permission') }}</div>
                    <div class="card-body">
                        <section>
                            <header>
                                <h2>{{ __('HR Edit User Log Permission') }}</h2>
                                <p>{{ __('Update HR Edit User Log permission.') }}</p>
                            </header>
                            <form method="post" action="{{ route('admin.updateHrPermission') }}" class="mt-4">
                                @csrf
                                @method('patch')

                                <div class="mb-3">
                                    <label for="edit_user_log" class="form-label">{{ __('Edit User Log') }}</label>
                                    <select id="edit_user_log" name="edit_user_log" class="form-select" required>
                                        <option value="enable" {{ old('edit_user_log', $hasEditUserLogPermission) == true ? 'selected' : '' }}>
                                            {{ __('Enable') }}</option>
                                        <option value="disable" {{ old('edit_user_log', $hasEditUserLogPermission) == false ? 'selected' : '' }}>
                                            {{ __('Disable') }}</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
