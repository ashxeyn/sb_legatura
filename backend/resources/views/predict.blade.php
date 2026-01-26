@extends('layouts.app')

@section('content')
<div class="container py-4">

    <!-- Page Header -->
    <div class="text-center mb-4">
        <h2 class="fw-bold">Project Delay Prediction</h2>
        <p class="text-muted">
            Enter project and weather-related details below.  
            The system will analyze the data and predict whether the project is likely to be delayed.
        </p>
    </div>

    <!-- Prediction Form -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('predict.delay') }}">
                @csrf

                <div class="row g-4">

                    <!-- Weather Conditions -->
                    <div class="col-md-6">
                        <h5 class="fw-semibold mb-3">🌦 Weather Conditions</h5>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Average Temperature (°C)</label>
                            <input type="number" step="0.1" name="avg_temp" class="form-control" required>
                            <small class="text-muted">
                                Typical daily temperature during the project period.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Average Humidity (%)</label>
                            <input type="number" step="0.1" name="avg_humidity" class="form-control" required>
                            <small class="text-muted">
                                Higher humidity can affect work efficiency and material handling.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Average Wind Speed (km/h)</label>
                            <input type="number" step="0.1" name="avg_wind" class="form-control" required>
                            <small class="text-muted">
                                Strong winds may slow down outdoor or elevated work.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Total Rainfall (mm)</label>
                            <input type="number" step="0.1" name="total_rain" class="form-control" required>
                            <small class="text-muted">
                                Accumulated rainfall throughout the project duration.
                            </small>
                        </div>
                    </div>

                    <!-- Project & Risk Factors -->
                    <div class="col-md-6">
                        <h5 class="fw-semibold mb-3">🏗 Project & Risk Factors</h5>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Project Duration (days)</label>
                            <input type="number" name="duration_days" class="form-control" required>
                            <small class="text-muted">
                                Total planned length of the project.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contractor Experience (years)</label>
                            <input type="number" name="contractor_experience_years" class="form-control" required>
                            <small class="text-muted">
                                Years of experience of the main contractor or team lead.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Rain Events</label>
                            <input type="number" name="rain_events" class="form-control" required>
                            <small class="text-muted">
                                Number of rainy days during the project.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Flooding Occurred?</label>
                            <select name="flooding" class="form-select" required>
                                <option value="" selected disabled>— Select an option —</option>
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                            <small class="text-muted">
                                Indicates whether flooding affected the work area.
                            </small>
                        </div>


                        <div class="mb-3">
                            <label class="form-label fw-semibold">Work Suspension Hours</label>
                            <input type="number" name="work_suspension_hours" class="form-control" required>
                            <small class="text-muted">
                                Total hours work was stopped due to weather or safety issues.
                            </small>
                        </div>

                        <<div class="mb-3">
                            <label class="form-label fw-semibold">Low Visibility Conditions?</label>
                            <select name="low_visibility" class="form-select" required>
                                <option value="" selected disabled>— Select an option —</option>
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                            <small class="text-muted">
                                Fog, heavy rain, or dust that reduced visibility on-site.
                            </small>
                        </div>


                        <div class="mb-3">
                            <label class="form-label fw-semibold">Storm Warning Issued?</label>
                            <select name="storm_warning" class="form-select" required>
                                <option value="" selected disabled>— Select an option —</option>
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                            <small class="text-muted">
                                Official storm or severe weather warning during the project.
                            </small>
                        </div>

                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        Predict Project Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Prediction Result -->
    @if(session('result'))
        <div class="card mt-4 shadow-sm border-0">
            <div class="card-body">
                <h4 class="fw-bold mb-3">📈 Prediction Result</h4>

                <p><strong>Status:</strong> {{ session('result')['prediction'] }}</p>
                <p><strong>Delay Probability:</strong> {{ session('result')['probability'] }}</p>
                <p><strong>Computed Weather Severity:</strong> {{ session('result')['weather_severity'] }}</p>

                @if(isset(session('result')['severity_label']))
                    <p>
                        <strong>Severity Level:</strong>
                        <span class="badge bg-info">{{ session('result')['severity_label'] }}</span>
                    </p>
                @endif

                @if(session('result')['prediction'] === 'Error')
                    <div class="alert alert-danger mt-3">
                        <h6 class="fw-bold">⚠ AI Error Details</h6>

                        @if(isset(session('result')['json_error']))
                            <p><strong>JSON Error:</strong> {{ session('result')['json_error'] }}</p>
                        @endif

                        @if(isset(session('result')['raw_output']))
                            <p><strong>Raw Output:</strong></p>
                            <pre>{{ session('result')['raw_output'] }}</pre>
                        @endif

                        @if(isset(session('result')['stderr']))
                            <p><strong>Python STDERR:</strong></p>
                            <pre>{{ session('result')['stderr'] }}</pre>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>
@endsection
