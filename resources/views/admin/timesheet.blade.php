@extends('layouts.app')

@section('css')
    <link href="{{ asset('plugins/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    @php
        use Carbon\Carbon;
    @endphp
    <div class="row mt-4" style="max-width: 96%;">
        <h2>Timesheet</h2>

        <!-- Filters Form -->
        <form id="filterForm" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="user_id" class="form-label">Employee</label><br>
                    <select name="user_id" id="user_id" class="select2 form-select mb-3 custom-select"
                        style="width: 100%; height:36px;">
                        <option value="">All Employees</option>
                        @foreach ($allUsers as $user)
                            <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="shift_id" class="form-label">Shift</label><br>
                    <select name="shift_id" id="shift_id" class="select2 form-select mb-3 custom-select"
                        style="width: 100%; height:36px;">
                        <option value="">All Shifts</option>
                        @foreach ($allShifts as $shift)
                            <option value="{{ $shift->id }}" {{ $selectedShiftId == $shift->id ? 'selected' : '' }}>
                                {{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="month" class="form-label">Month</label><br>
                    <select name="month" id="month" class="select2 form-select">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="year" class="form-label">Year</label><br>
                    <select name="year" id="year" class="select2 form-select">
                        @for ($y = 2020; $y <= Carbon::now()->year; $y++)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                {{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="clearFilters" class="btn btn-sm btn-secondary"><i class="fas fa-times-circle"></i> Clear</button>
                    <a href="#" id="exportButton" class="btn btn-sm btn-primary ms-2"><i class="fas fa-file-export"></i> Export</a>
                </div>
            </div>
        </form>

        <!-- Timesheet Table -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr id="days-header">
                        <th>Employee</th>
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            <th>{{ $day }}<br>{{ \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, $day)->format('D') }}
                            </th>
                        @endfor
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody id="timesheetBody">
                    @include('admin.partials.timesheet_body', [
                        'timesheet' => $timesheet,
                        'daysInMonth' => $daysInMonth,
                        'selectedYear' => $selectedYear,
                        'selectedMonth' => $selectedMonth,
                    ])
                </tbody>
            </table>
            <div id="paginationLinks">
                {{ $filteredUsers->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/moment.js') }}"></script>
    <script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('plugins/select2/select2.min.js') }}"></script>
    <script src="{{ asset('pages/jquery.forms-advanced.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const clearFilters = document.getElementById('clearFilters');
            const exportButton = document.getElementById('exportButton');
            const timesheetBody = document.getElementById('timesheetBody');
            const daysHeader = document.getElementById('days-header');
            const paginationLinks = document.getElementById('paginationLinks');

            exportButton.addEventListener('click', function() {
                const params = new URLSearchParams(new FormData(filterForm)).toString();
                window.location.href = `{{ route('admin.timesheet.export') }}?${params}`;
            });

            const userSelect = $('#user_id');
            const shiftSelect = $('#shift_id');
            const monthSelect = $('#month');
            const yearSelect = $('#year');

            userSelect.select2();
            shiftSelect.select2();
            monthSelect.select2();
            yearSelect.select2();

            userSelect.on('change', fetchTimesheet);
            shiftSelect.on('change', fetchTimesheet);
            monthSelect.on('change', fetchTimesheet);
            yearSelect.on('change', fetchTimesheet);

            clearFilters.addEventListener('click', function() {
                userSelect.val('').trigger('change');
                shiftSelect.val('').trigger('change');
                monthSelect.val({{ Carbon::now()->month }}).trigger('change');
                yearSelect.val({{ Carbon::now()->year }}).trigger('change');
                fetchTimesheet();
            });

            function fetchTimesheet(page = 1) {
                const formData = new FormData(filterForm);
                formData.append('page', page);
                const params = new URLSearchParams(formData).toString();

                fetch(`{{ route('admin.timesheet') }}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        timesheetBody.innerHTML = data.html;
                        daysHeader.innerHTML = data.header;
                        paginationLinks.innerHTML = data.pagination;

                        // Add event listeners to pagination links
                        document.querySelectorAll('#paginationLinks a').forEach(link => {
                            link.addEventListener('click', function(e) {
                                e.preventDefault();
                                const url = new URL(this.href);
                                const page = url.searchParams.get('page');
                                fetchTimesheet(page);
                            });
                        });
                    })
                    .catch(error => console.error('Error fetching timesheet:', error));
            }

            // Trigger initial fetch to load the table with default values
            fetchTimesheet();
        });
    </script>
@endpush
