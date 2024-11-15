<!-- resources/views/admin/user-list.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Team Members</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Shift</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($users->isNotEmpty())
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->shift->name ?? 'No shift assigned' }}</td>
                                <td>{{ $user->email }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" class="text-center">No users available</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div class="float-end">
                @if (isset($users) && count($users) > 0)
                    {{ $users->links('pagination::bootstrap-4') }}
                @endif
            </div>
        </div>
    </div>
@endsection
