@extends('layouts.app')

@section('content')
    <div class="row mt-4">
        <div class="col-6">
            <h2 class="mt-0">Manual Entries</h2>
        </div>
        <!-- Search and Month Filter Form -->
        <div class="col-6">
            <form id="filterForm" class="mb-4" method="GET" action="{{ route('admin.manualEntries') }}">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email"
                        value="{{ request('search') }}">
                    <input type="month" name="month" class="form-control" value="{{ $selectedMonth }}">
                    <!-- Preserve sorting parameters when filtering -->
                    <input type="hidden" name="sort_by" value="{{ request('sort_by', 'name') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'asc') }}">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row mt-2">
        <!-- User Data Table -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>
                            <a
                                href="{{ route('admin.manualEntries', array_merge(request()->query(), ['sort_by' => 'total_hours', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}">
                                Total Hours
                                @if (request('sort_by') === 'total_hours')
                                    @if (request('sort_order') === 'asc')
                                        <i class="fas fa-sort-up"></i>
                                    @else
                                        <i class="fas fa-sort-down"></i>
                                    @endif
                                @else
                                    <i class="fas fa-sort"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a
                                href="{{ route('admin.manualEntries', array_merge(request()->query(), ['sort_by' => 'total_manual_hours', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}">
                                Total Manual Hours
                                @if (request('sort_by') === 'total_manual_hours')
                                    @if (request('sort_order') === 'asc')
                                        <i class="fas fa-sort-up"></i>
                                    @else
                                        <i class="fas fa-sort-down"></i>
                                    @endif
                                @else
                                    <i class="fas fa-sort"></i>
                                @endif
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($userData as $data)
                        <tr @if ($data['manual_seconds'] > 216000) style="background-color: coral;" @endif>
                            <td style="position: relative; padding-left: 10px;">
                                @if ($data['manual_seconds'] > 54000)
                                    <div
                                        style="position: absolute; left: 0; top: 0; bottom: 0; border-left: 5px solid red; width: 0;">
                                    </div>
                                @endif
                                {{ $data['user']->name }}
                            </td>
                            <td>{{ $data['user']->email }}</td>
                            <td>{{ $data['total_hours'] }}</td>
                            <td>{{ $data['total_manual_hours'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- Pagination Links -->
    <div class="d-flex justify-content-start">
        {{ $userData->appends(request()->query())->links() }}
    </div>
@endsection

@push('scripts')
    <!-- Script to submit the form when search or month is changed -->
    <script>
        $(document).ready(function() {
            $('input[name="month"]').on('change', function() {
                $('#filterForm').submit();
            });
            $('input[name="search"]').on('keyup', function() {
                $('#filterForm').submit();
            });
        });
    </script>
@endpush
