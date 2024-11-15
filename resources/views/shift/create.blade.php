@extends('layouts.app')

@section('content')
    <div class="row justify-content-center mt-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Create Shift</h4>
                    <p class="text-muted mb-0">You can create shifts here</p>
                </div><!--end card-header-->
                <div class="card-body">
                    <form method="post" action="{{ route('shift.create') }}">
                        @csrf
                        @method('post')
                        <div class="mb-3">
                            <label class="form-label" for="name">Shift Name</label>
                            <input type="text" class="form-control" name="name" placeholder="afternoon">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="type">Type</label>
                            <input type="text" class="form-control" name="type" placeholder="on-site">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="start_time">Start Time</label>
                            <input type="time" class="form-control" name="start_time" placeholder="09:00:00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="end_time">End Time</label>
                            <input type="time" class="form-control" name="end_time" placeholder="18:00:00">
                        </div>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </form>
                </div><!--end card-body-->
            </div><!--end card-->
        </div>
    </div>
@endsection
