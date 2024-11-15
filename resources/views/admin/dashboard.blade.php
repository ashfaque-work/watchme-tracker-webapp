@extends('layouts.app')

@section('content')
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5>User Status</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-5">
                        <div>
                            <h3>{{ $totalUsers }}</h3>
                            <p>Users</p>
                        </div>
                        <div>
                            <h3>{{ $activeUsers }}</h3>
                            <p>Active Users</p>
                        </div>
                        <div>
                            <h3>{{ $inactiveUsers }}</h3>
                            <p>Inactive Users</p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-primary">Active Users</span>
                        <span class="text-danger">Inactive Users</span>
                    </div>
                    <div class="progress mt-3" style="height:15px;">
                        <div class="progress-bar bg-primary" role="progressbar"
                            style="width: {{ $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0 }}%"
                            aria-valuenow="{{ $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0 }}"
                            aria-valuemin="0" aria-valuemax="100">
                            {{ $activeUsers }}
                        </div>
                        <div class="progress-bar bg-danger" role="progressbar"
                            style="width: {{ $totalUsers > 0 ? ($inactiveUsers / $totalUsers) * 100 : 0 }}%"
                            aria-valuenow="{{ $totalUsers > 0 ? ($inactiveUsers / $totalUsers) * 100 : 0 }}"
                            aria-valuemin="0" aria-valuemax="100">{{ $inactiveUsers }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Top 5 Members: working hours</h5>
                    <span class="badge bg-secondary">Weekly</span>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach ($topMembersWeekly as $member)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $member->user->name }}
                                <span class="badge bg-primary">{{ $member->total_time }} hrs</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Top 5 Members: working hours with manual entries</h5>
                    <div>
                        <a href="{{ route('dashboard', ['month' => 'this']) }}"
                            class="btn btn-sm {{ $reportMonth == 'this' ? 'btn-primary' : 'btn-outline-primary' }}">This
                            Month</a>
                        <a href="{{ route('dashboard', ['month' => 'prev']) }}"
                            class="btn btn-sm {{ $reportMonth == 'prev' ? 'btn-primary' : 'btn-outline-primary' }}">Prev
                            Month</a>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach ($usersMonthly as $member)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $member->user->name }}
                                <div>
                                    <span class="badge bg-primary">Total: {{ $member->total_time }} hrs</span>
                                    <span class="badge bg-secondary">Manual: {{ $member->manual_time }} hrs</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
