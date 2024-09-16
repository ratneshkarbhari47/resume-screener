<main>
    <div class="container mt-5">
        <!-- Screening Form -->
        <form id="screen-form" method="POST" action="{{ url('screen-resume-service') }}" enctype="multipart/form-data" class="row">
            @csrf
            <div class="col-lg-6 col-md-12 col-sm-12">
                <div id="screen-form-section">
                    <!-- Job Description -->
                    <div class="mb-3">
                        <label for="job-description" class="form-label">Job Description</label>
                        <textarea name="job-description" id="job-description" class="form-control" rows="24" placeholder="Enter the job description..."></textarea>
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
                    <div id="resultsBox">
                        <img src="{{ url('assets/images/ui-preview.png') }}" alt="Resume Screening AI demo" class="relative transition-all rounded-lg fade-out-bottom w-100">
                    </div>

                    <div class="container text-left mt-3">
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
                <!-- Email and Password Login Form -->
                <form id="login-form">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" placeholder="Enter email">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" placeholder="Password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <hr class="my-4">

                <!-- Google Login Button -->
                <button class="btn btn-outline-danger w-100">
                    Continue with Google
                </button>
            </div>
        </div>
    </div>
</div>