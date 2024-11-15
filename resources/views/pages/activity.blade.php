@extends('layouts.app')

@section('css')
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="{{ asset('plugins/select2/select2.min.css') }}" type="text/css">
    <link href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('plugins/animate/animate.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="{{ asset('css/activity.css') }}">
@endsection

@section('content')
    <div class="row mt-4">
        <div class="col-lg-8 mb-2">
            <h2>Tracker Logs</h2>
        </div>
        <div class="col-lg-4 mb-2">
            <div class="input-group">
                <input type="text" class="form-control daterange">
                <span class="input-group-text"><i class="ti ti-calendar font-16"></i></span>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-10 text-center tracked-sections-container" id="activity-container">
            @foreach ($logsArray as $date => $logs)
                <section class="tracked-section ">
                    <header class="tracked-header d-flex align-items-center justify-content-between px-4">
                        <h4 class="date-heading m-0 text-primary-emphasis">{{ $date }}</h4>
                        <div>
                            <p class="total-hours text-secondary mb-0" title="Total Hours" data-log="">
                                {{ $logs['elapsedTime'] }}</p>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-lg btn-toggle toggle-icon-btn text-secondary collapsed"
                                data-bs-toggle="collapse" data-bs-target="#collapseDetails_{{ $logs['logId'] }}"
                                aria-expanded="false" aria-controls="collapseDetails_{{ $logs['logId'] }}"
                                data-bs-placement="top" title="Expand Activities">
                                <i class="mdi mdi-chevron-down-circle mdi-24px"></i>
                            </button>
                        </div>
                    </header>
                    <div class="collapse" id="collapseDetails_{{ $logs['logId'] }}">
                        <ol class="tracked-list list-unstyled px-4">
                            <!-- Time logs will be added here -->
                            @foreach ($logs['log'] as $log)
                                <li class="tracked-item">
                                    <div
                                        class="tracked-details  {{ $log['type'] === 'untracked' ? 'temp-entry' : ($log['type'] === 'entry' ? 'manual-entry' : ($log['type'] === 'break' ? 'splitted-break' : '')) }} d-flex align-items-center justify-content-between">
                                        <div class="time-details col d-flex justify-content-start">
                                            <p class="time-span mb-0"><span class="time">{{ $log['fstartTime'] }}
                                                    - {{ $log['fendTime'] }}</span></p>
                                        </div>
                                        <div class="duration-details col-auto">
                                            <p class="duration mb-0"><span class="duration">{{ $log['duration'] }}</span>
                                            </p>
                                        </div>
                                        <div class="screenshot-details screenshot-trigger col d-flex justify-content-end">
                                            @if ($log['type'] === 'break')
                                                <button type="button" class="btn btn-lg text-secondary p-1 px-2"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="'Already Splitted'">
                                                    <i class="mdi mdi-cookie-clock mdi-18px"></i>
                                                </button>
                                            @endif

                                            @if ($log['type'] === 'untracked' && ($log['isGreaterThanAnHour'] ?? false))
                                                <button class="btn btn-lg text-secondary add-break-interval p-1 px-2"
                                                    data-bs-toggle="modal" data-bs-target="#addTimeModal"
                                                    data-timelogid="{{ $log['timeLogId'] }}"
                                                    data-log-id="{{ $logs['logId'] }}" data-from="{{ $log['startTime'] }}"
                                                    data-duration="{{ $log['duration'] }}" data-to="{{ $log['endTime'] }}"
                                                    title="Add break interval">
                                                    <i class="mdi mdi-timer-edit mdi-18px"></i>
                                                </button>
                                            @endif
                                            @if ($log['type'] === 'untracked')
                                                <button class="btn btn-lg text-secondary add-interval p-1 px-2"
                                                    data-bs-toggle="modal" data-bs-target="#addTimeModal"
                                                    data-timelogid="{{ $log['timeLogId'] }}"
                                                    data-log-id="{{ $logs['logId'] }}" data-from="{{ $log['startTime'] }}"
                                                    data-duration="{{ $log['duration'] }}" data-to="{{ $log['endTime'] }}"
                                                    title="Add interval">
                                                    <i class="mdi mdi-timer-plus mdi-18px"></i>
                                                </button>
                                            @endif
                                            @if ($log['type'] === 'entry')
                                                <button type="button" class="btn btn-lg p-1 px-2" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="{{ $log['description'] }}">
                                                    <i class="mdi mdi-information-outline mdi-18px"></i>
                                                </button>
                                            @endif
                                            {{-- {{dd($log)}} --}}
                                            <button class="btn btn-secondary-outline btn-lg p-1 px-2" data-bs-toggle="modal"
                                                data-date="{{ $logs['dateTime'] }}" data-bs-target="#screenshotModal"
                                                data-log-id="{{ $logs['logId'] }}"
                                                data-screenshots='{{ json_encode($log['screenshot']) }}'
                                                {{ count($log['screenshot']) === 0 ? 'disabled' : '' }}>
                                                <i class="mdi mdi-image-multiple mdi-18px"></i>
                                                <span
                                                    class="badge badge-dark text-secondary ps-0">{{ count($log['screenshot']) }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    <!--  Manual Time Entry form modal -->
    <div class="modal fade" id="addTimeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Tracked Time</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="put" id="addTimeForm">
                        @csrf
                        @method('put')
                        <div class="form-floating mb-3">
                            <textarea class="form-control" placeholder="Add description" id="addTimeDescription" name="description"
                                style="height: 100px" required></textarea>
                            <label for="addTimeDescription">Add Description</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="datetime-local" class="form-control" name="from_datetime" id="from_datetime"
                                value="" readonly required>
                            <label for="from_datetime">From</label>
                        </div>
                        <div class="form-floating mb-1">
                            <input type="datetime-local" class="form-control" name="to_datetime" id="to_datetime"
                                value="" readonly required>
                            <label for="to_datetime">To</label>
                        </div>
                        <div class="form-floating text-danger" id="durationDisplay"></div>
                        <input type="hidden" id="logIdInput" name="logId">
                        <input type="hidden" id="userIdInput" name="userId">
                        <input type="hidden" id="timeLogId" name="timeLogId">
                        <input type="hidden" id="duration" name="duration">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="updateButton">Update</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!------ Screenshots Modal ------>
    <div class="modal fade" id="screenshotModal" tabindex="-1" aria-labelledby="screenshotModalTitle"
        style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <p class="modal-title m-0" id="screenshotModalTitle">Screenshots</p>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div><!--end modal-header-->
                <div class="modal-body">
                    <!-------- Carouser ---------------->
                    <div id="screenshotCarousel" class="carousel slide pointer-event" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <!-- images -->
                        </div>
                        <div id="" class="carousel-indicators" style="margin:0 0 -20px 0">
                            <!-- Thumbnails -->
                        </div>
                        <a class="carousel-control-prev" href="#screenshotCarousel" role="button" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#screenshotCarousel" role="button" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </a>
                    </div>
                    <!-------- Carouser End ---------------->
                </div><!--end modal-body-->
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div><!--end modal-footer-->
            </div><!--end modal-content-->
        </div><!--end modal-dialog-->
    </div>
@endsection

@push('scripts')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/select2.min.js') }}"></script>
    <!-- Daterangepicker -->
    <script src="{{ asset('js/moment.js') }}"></script>
    <script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
    <!-- Repeater -->
    <script src="{{ asset('plugins/repeater/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('pages/jquery.form-repeater.js') }}"></script>
    <script>
        $(document).ready(function() {
            var urlParams = new URLSearchParams(window.location.search);
            var user = urlParams.get('user');

            $(function() {
                $('[data-bs-toggle="tooltip"]').tooltip();
            });

            $('.teamMembers').select2({
                placeholder: "Select a Member",
                allowClear: true
            });

            if (user) {
                $('.teamMembers').val(user).trigger('change');
            } else {
                $('.teamMembers').val($('.teamMembers option:first').val()).trigger('change');
            }

            $('.teamMembers').on('select2:select');

            $('.daterange').daterangepicker();

            $('.tracked-sections-container').on('click', '.add-break-interval', function() {
                let logId = $(this).data('log-id');
                let date = $(this).data('date');
                let timeLogId = $(this).data('timelogid');
                let duration = $(this).data('duration');
                let userId = $('.teamMembers').val();
                let from = $(this).data('from').split(':').slice(0, 2).join(':');
                let to = $(this).data('to').split(':').slice(0, 2).join(':');
                
                // let userId = $('.teamMembers').val();

                $("#to_datetime").attr("type", "datetime-local");
                $("#from_datetime").attr("type", "datetime-local");

                $('#durationDisplay').empty();

                $("#addTimeForm").attr('action', '{{ route('updateBreakEnteries', '') }}');
                $('#addTimeModal').modal('show');

                $("#logIdInput").val(logId);
                $("#userIdInput").val(userId);
                $("#timeLogId").val(timeLogId);
                $("#duration").val(duration);
                $("#id").val(0);
                // $("#userIdInput").val(userId);
                $("#from_datetime").val(from);
                $("#to_datetime").val(to);

                $("#from_datetime").prop("readonly", false);
                $("#to_datetime").prop("readonly", false);
            });

            $('.tracked-sections-container').on('click', '.add-interval', function() {
                let logId = $(this).data('log-id');
                let timeLogId = $(this).data('timelogid');
                let from = $(this).data('from');
                let to = $(this).data('to');
                let duration = $(this).data('duration');
                let userId = $('.teamMembers').val();

                $('#durationDisplay').empty();

                $("#addTimeForm").attr('action', '{{ route('updateEntries', '') }}');
                $('#addTimeModal').modal('show');

                $("#to_datetime").attr("type", "text");
                $("#from_datetime").attr("type", "text");

                $("#logIdInput").val(logId);
                $("#userIdInput").val(userId);
                $("#from_datetime").val(from);
                $("#to_datetime").val(to);
                $("#timeLogId").val(timeLogId);
                $("#duration").val(duration);


                $("#from_datetime").prop("readonly", true);
                $("#to_datetime").prop("readonly", true);
            });

            $('#addTimeForm').submit(function(e) {
                e.preventDefault();

                let form = $(this);
                let logId = form.find('#logIdInput').val();
                let userId = form.find('#userIdInput').val();
                let id = form.find('#id').val();
                let from = form.find('#from_datetime').val();
                let to = form.find('#to_datetime').val();
                $.ajax({
                    type: 'PUT',
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(response) {
                        $('#addTimeModal').modal('hide');

                        let Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 800,
                            timerProgressBar: true,
                            onOpen: function(toast) {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal
                                    .resumeTimer)
                            }
                        });

                        Toast.fire({
                            icon: 'success',
                            title: 'Tracked hours updated'
                        }).then(function() {
                            location.reload();
                        })
                    },
                    error: function(error) {
                        if (error.status === 400) {
                            Swal.fire({
                                title: 'Warning',
                                text: error.responseJSON.message,
                                icon: 'warning'
                            });
                        } else if (error.status === 422) {
                            Swal.fire({
                                title: 'Warning',
                                text: 'Please check with your input!',
                                icon: 'warning'
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'Some Error Occured!',
                                icon: 'error'
                            });
                        }
                        // location.reload();
                    }
                });
            });

            $('#screenshotModal').on('shown.bs.modal', function(e) {
                e.preventDefault();
                let button = $(e.relatedTarget);
                let logId = button.data('log-id');
                let date = button.data('date');
                let screenshots = button.data('screenshots');

                {{-- let id = $('.teamMembers').val() ?? '{{ $selectedUserId }}'; --}}
                $('#screenshotCarousel .carousel-inner').empty();
                $('#screenshotCarousel .carousel-indicators').empty();

                if (screenshots.length > 0) {
                    screenshots.forEach(function(screenshot, index) {
                        const isActive = index === 0 ? 'active' : '';
                        const carouselItem = `<div class="carousel-item ${isActive}">
                                <img class="d-block w-100" src="https://drive.google.com/thumbnail?id=${screenshot['image']}&sz=w1000" alt="Screenshot ${index + 1}">
                                <div class="carousel-caption d-none d-md-block">
                                    <p>Captured at ${screenshot['capture_time']}</p>
                                </div>
                                </div>`;
                        const thumbnailButton =
                            `<button
                                type="button" data-bs-target="#screenshotCarousel" data-bs-slide-to="${index}" class="${isActive}"
                                aria-label="Slide ${index + 1}" style="width: 100px;">
                                <img class="d-block w-100" src="https://drive.google.com/thumbnail?id=${screenshot['image']}" class="img-fluid" alt="Thumbnail ${index + 1}"></button>`;

                        $('#screenshotCarousel .carousel-inner').append(
                            carouselItem);
                        $('#screenshotCarousel .carousel-indicators').append(
                            thumbnailButton);
                    });
                } else {
                    $('#screenshotCarousel .carousel-inner').append(
                        '<div class="carousel-item active">' +
                        '<p>No screenshots available</p>' +
                        '</div>'
                    );
                    return;
                }
            });

            $('.daterange').on('apply.daterangepicker', function(event, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = picker.endDate.format('YYYY-MM-DD');

                var url = `{{ route('activity') }}?start_date=${startDate}&end_date=${endDate}`;

                window.location.href = url;
            });
        });
    </script>
@endpush
