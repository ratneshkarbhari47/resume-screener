<main>
    <div class="container mt-5">
        <!-- Screening Form -->
        <form id="screen-form" enctype="multipart/form-data" class="row">
            @csrf
            <div class="col-lg-6 col-md-12 col-sm-12">
                <div id="screen-form-section">
                    <!-- Job Description -->
                    <div class="mb-3">
                        <label for="job-description" class="form-label">Job Description</label>
                        <textarea name="job_description" id="job-description" class="form-control" rows="24" placeholder="Enter the job description..."></textarea>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-sm-12">
                <div class="mb-3">
                    <label class="form-label">Upload Resumes (Max 10)</label>
                    <br><br>
                    <input type="file" name="resumes[]" id="resumeFiles" multiple>
                </div>
                <div class="mt-3">
                    <h3 class="text-3xl font-extrabold text-left">How does it work?</h3>
                    <ol class="text-left" style="padding-left: 1em;">
                        <li>Enter your job description <span class="hidden md:inline">on the left</span><span class="md:hidden">below</span></li>
                        <li>Drop your resumes <span class="hidden md:inline">on the right</span><span class="md:hidden">above</span></li>
                        <li>Get a sorted list of applicants in seconds</li>
                    </ol>

                    <div id="resultsBox" class="accordion mt-4">
                        <!-- Results will be injected here -->
                    </div>

                    <div class="container text-left mt-3 mt-4">
                        <button id="submit-all" type="submit" class="btn btn-success">Submit All</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<!-- Modal for Login -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login to Resume Screener</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="login-form">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" placeholder="Enter email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <hr class="my-4">

                <button class="btn btn-outline-danger w-100">
                    Continue with Google
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Setup AJAX for CSRF protection
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle form submission
    $('#screen-form').submit(function(e) {
        e.preventDefault();

        // Create a FormData object for file upload
        var formData = new FormData(this);

        $.ajax({
            url: "{{ url('screen-resume-service') }}",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Clear previous results
                $('#resultsBox').empty();

                // Ensure response is parsed as JSON if it's a string
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (error) {
                        $('#resultsBox').html('<p class="text-danger">Invalid response from server.</p>');
                        return;
                    }
                }

                // Check if response.candidates is valid
                if (!response.candidates || !Array.isArray(response.candidates)) {
                    $('#resultsBox').html('<p class="text-danger">No candidates found or invalid response structure.</p>');
                    return;
                }

                // Create accordions for each candidate
                response.candidates.forEach((candidate, index) => {
                    // Ensure strong_points and weak_points are arrays
                    const strongPoints = Array.isArray(candidate.strong_points) ? candidate.strong_points : [];
                    const weakPoints = Array.isArray(candidate.weak_points) ? candidate.weak_points : [];

                    let candidateHTML = `
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading${index}">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${index}" aria-expanded="true" aria-controls="collapse${index}">
                                    ${candidate.name} - Score: ${candidate.score}
                                </button>
                            </h2>
                            <div id="collapse${index}" class="accordion-collapse collapse" aria-labelledby="heading${index}" data-bs-parent="#resultsBox">
                                <div class="accordion-body">
                                    <strong>Strong Points:</strong>
                                    <ul>
                                        ${strongPoints.length > 0 ? strongPoints.map(point => `<li>${point}</li>`).join('') : '<li>No strong points available.</li>'}
                                    </ul>
                                    <strong>Weak Points:</strong>
                                    <ul>
                                        ${weakPoints.length > 0 ? weakPoints.map(point => `<li>${point}</li>`).join('') : '<li>No weak points available.</li>'}
                                    </ul>
                                    <strong>Explanation:</strong>
                                    <p>${candidate.explanation || 'No explanation provided.'}</p>
                                </div>
                            </div>
                        </div>`;
                    $('#resultsBox').append(candidateHTML);
                });

                // Initialize Bootstrap accordion
                var myAccordion = new bootstrap.Accordion(document.getElementById('resultsBox'));
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert('Something went wrong. Please try again.');
            }
        });
    });
});
</script>
