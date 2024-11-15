@extends('layouts.app')

@section('content')
    <div class="row mt-4">
        <!-- Today's Total Working Hours -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-calendar-day text-primary fs-3 me-3"></i>
                    <div>
                        <h5 class="card-title mb-2">Today's Total Working Hours</h5>
                        <p class="card-text">{{ $todayTotalFormatted }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Previous Week Total Hours -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-calendar-week text-primary fs-3 me-3"></i>
                    <div>
                        <h5 class="card-title mb-2">Previous Week Total Hours</h5>
                        <p class="card-text">{{ $previousWeekTotalFormatted }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Week Total Hours -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-calendar-week text-primary fs-3 me-3"></i>
                    <div>
                        <h5 class="card-title mb-2">Current Week Total Hours</h5>
                        <p class="card-text">{{ $currentWeekTotalFormatted }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Month Total Hours -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-calendar text-primary fs-3 me-3"></i>
                    <div>
                        <h5 class="card-title mb-2">Current Month Total Hours</h5>
                        <p class="card-text">{{ $currentMonthTotalFormatted }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Last Month Total Hours -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-calendar text-primary fs-3 me-3"></i>
                    <div>
                        <h5 class="card-title mb-2">Last Month Total Hours</h5>
                        <p class="card-text">{{ $lastMonthTotalFormatted }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
