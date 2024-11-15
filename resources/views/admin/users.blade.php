@extends('layouts.app')

@section('css')
    <!-- DataTables -->
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/datatables/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2>Users</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">Create User</button>
    </div>

    {{-- User table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="datatable" class="table table-bordered dt-responsive nowrap"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Email</th>
                                <th>Shift</th>
                                <th>Assigned Manager</th>
                                <th name="buttons">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($users))
                                @foreach ($users as $user)
                                    <tr>
                                        <td class="fw-bold">{{ $loop->iteration }}.</td>
                                        <td>{{ $user->name }}</td>
                                        <td>
                                            {{ $user->roles->isEmpty()? 'None': $user->roles->pluck('name')->map(function ($role) {return ucwords($role);})->join(', ') }}
                                        </td>
                                        <td>{{ ucwords($user->status) }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ ucwords($user->shift->name) }}</td>
                                        <td>{{ $user->manager ? $user->manager->name : 'None' }}</td>
                                        <td name="buttons">
                                            <button class="btn btn-sm btn-soft-primary btn-circle me-2"
                                                data-bs-toggle="modal" data-bs-target="#updateShiftModal"
                                                data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}"
                                                data-shift-name="{{ $user->shift->name }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Update User Shift">
                                                <i class="fas fa-user-clock"></i>
                                            </button>
                                            <button class="btn btn-sm btn-soft-purple me-2 btn-circle"
                                                data-bs-toggle="modal" data-bs-target="#assignManagerModal"
                                                data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}"
                                                data-manager-id="{{ $user->manager ? $user->manager->id : '' }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Assign Manager to this User">
                                                <i class="fas fa-users"></i>
                                            </button>
                                            <button class="btn btn-sm btn-soft-info btn-circle me-2" data-bs-toggle="modal"
                                                data-bs-target="#assignRoleModal" data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-role-id="{{ $user->roles->pluck('id')->join(', ') }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Assign a role to this User">
                                                <i class="dripicons-user"></i>
                                            </button>
                                            <button class="btn btn-sm btn-soft-warning btn-circle me-2"
                                                data-bs-toggle="modal" data-bs-target="#updateStatusModal"
                                                data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}"
                                                data-user-status="{{ $user->status }}" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Update User Status">
                                                <i class="fas fa-toggle-on"></i>
                                            </button>
                                            <button class="btn btn-sm btn-soft-danger btn-circle me-2"
                                                data-bs-toggle="modal" data-bs-target="#updatePasswordModal"
                                                data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Update User Password">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Create User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createUserForm" action="{{ route('admin.create-user') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="role_id" class="form-label">Role</label>
                                <select class="form-control" id="role_id" name="role_id">
                                    <option value="">No Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ ucwords($role->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shift_id" class="form-label">Shift</label>
                                <select class="form-control" id="shift_id" name="shift_id" required>
                                    @foreach ($shiftNames as $shiftName)
                                        <option value="{{ $shiftName }}">{{ ucwords($shiftName) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="manager_id" class="form-label">Manager</label>
                                <select class="form-control" id="manager_id" name="manager_id">
                                    <option value="">No Manager</option>
                                    @foreach ($managers as $manager)
                                        <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="send_email" name="send_email">
                                    <label class="form-check-label" for="send_email">Send email to user</label>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-primary">Create User</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Manager Modal -->
    <div class="modal fade" id="assignManagerModal" tabindex="-1" aria-labelledby="assignManagerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignManagerModalLabel">Assign Manager</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="assignManagerForm" action="{{ route('admin.assign-manager') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="user_id" id="assignManagerUserId">
                        <div class="form-group">
                            <label for="manager_id">Manager</label>
                            <select name="manager_id" id="manager_id" class="form-control">
                                <option value="">No Manager</option>
                                @foreach ($managers as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Assign Manager</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Shift Modal -->
    <div class="modal fade" id="updateShiftModal" tabindex="-1" aria-labelledby="updateShiftModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateShiftModalLabel">Update Shift</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateShiftForm" method="POST">
                        @csrf
                        @method('post')
                        <div class="mb-3">
                            <label class="form-label" for="username">User Name</label>
                            <input type="text" class="form-control" name="username" id="shiftUserName" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="shift_name">Shift Name</label>
                            <select class="shift form-control custom-select" name="shift_name" id="shiftName">
                                @foreach ($shiftNames as $shiftName)
                                    <option value="{{ $shiftName }}">{{ ucwords($shiftName) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="userId" id="shiftUserId">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Role Modal -->
    <div class="modal fade" id="assignRoleModal" tabindex="-1" aria-labelledby="assignRoleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignRoleModalLabel">Assign Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="assignRoleForm" action="{{ route('admin.assign-role') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="user_id" id="assignRoleUserId">
                        <div class="form-group">
                            <label for="role_id">Role</label>
                            <select name="role_id" id="role_id" class="form-control">
                                <option value="">No Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ ucwords($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Assign Role</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel">Update User Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="updateStatusForm" method="POST" action="{{ route('admin.updateUserStatus') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="updateStatusUserId">
                        <div class="mb-3">
                            <label for="updateStatusUserName" class="form-label">User Name</label>
                            <input type="text" class="form-control" id="updateStatusUserName" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="updateStatus" class="form-label">Status</label>
                            <select class="form-select" id="updateStatus" name="status">
                                <option value="regular">Regular</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Password Modal -->
    <div class="modal fade" id="updatePasswordModal" tabindex="-1" aria-labelledby="updatePasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updatePasswordModalLabel">Update Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updatePasswordForm" action="{{ route('admin.updateUserPassword') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="user_id" id="updatePasswordUserId">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="new_password_confirmation"
                                name="new_password_confirmation" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <!-- DataTables -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('pages/jquery.datatable.init.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var assignManagerModal = document.getElementById('assignManagerModal');
            assignManagerModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var userId = button.getAttribute('data-user-id');
                var userName = button.getAttribute('data-user-name');
                var managerId = button.getAttribute('data-manager-id');

                var modalTitle = assignManagerModal.querySelector('.modal-title');
                var form = assignManagerModal.querySelector('form');
                var userIdInput = assignManagerModal.querySelector('#assignManagerUserId');
                var managerSelect = assignManagerModal.querySelector('#manager_id');

                modalTitle.textContent = 'Assign Manager to ' + userName;
                userIdInput.value = userId;
                form.action = "{{ route('admin.assign-manager') }}";

                // Set the manager dropdown to the current manager
                managerSelect.value = managerId;
            });

            var updateShiftModal = document.getElementById('updateShiftModal');
            updateShiftModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var userId = button.getAttribute('data-user-id');
                var userName = button.getAttribute('data-user-name');
                var shiftName = button.getAttribute('data-shift-name');

                var modalTitle = updateShiftModal.querySelector('.modal-title');
                var form = updateShiftModal.querySelector('form');
                var userNameInput = updateShiftModal.querySelector('#shiftUserName');
                var shiftNameSelect = updateShiftModal.querySelector('#shiftName');
                var userIdInput = updateShiftModal.querySelector('#shiftUserId');

                modalTitle.textContent = 'Update Shift for ' + userName;
                userNameInput.value = userName;
                shiftNameSelect.value = shiftName;
                userIdInput.value = userId;
                form.action = "{{ route('shift.updateUserShift') }}";
            });

            var assignRoleModal = document.getElementById('assignRoleModal');
            assignRoleModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var userId = button.getAttribute('data-user-id');
                var userName = button.getAttribute('data-user-name');
                var roleId = button.getAttribute('data-role-id');

                var modalTitle = assignRoleModal.querySelector('.modal-title');
                var form = assignRoleModal.querySelector('form');
                var userIdInput = assignRoleModal.querySelector('#assignRoleUserId');
                var roleSelect = assignRoleModal.querySelector('#role_id');

                modalTitle.textContent = 'Assign Role to ' + userName;
                userIdInput.value = userId;
                form.action = "{{ route('admin.assign-role') }}";

                // Set the role dropdown to the current role
                roleSelect.value = roleId;
            });

            var updateStatusModal = document.getElementById('updateStatusModal');
            updateStatusModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget; // Button that triggered the modal
                var userId = button.getAttribute('data-user-id');
                var userName = button.getAttribute('data-user-name');
                var userStatus = button.getAttribute('data-user-status');

                var modalTitle = updateStatusModal.querySelector('.modal-title');
                var userNameInput = updateStatusModal.querySelector('#updateStatusUserName');
                var userIdInput = updateStatusModal.querySelector('#updateStatusUserId');
                var statusSelect = updateStatusModal.querySelector('#updateStatus');

                userIdInput.value = userId;
                userNameInput.value = userName;
                statusSelect.value = userStatus;
            });

            var updatePasswordModal = document.getElementById('updatePasswordModal');
            updatePasswordModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var userId = button.getAttribute('data-user-id');
                var userName = button.getAttribute('data-user-name');

                var modalTitle = updatePasswordModal.querySelector('.modal-title');
                var userIdInput = updatePasswordModal.querySelector('#updatePasswordUserId');

                modalTitle.textContent = 'Update Password for ' + userName;
                userIdInput.value = userId;
            });
        });
    </script>
@endpush
