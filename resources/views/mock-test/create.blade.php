<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Request Mock Test Session</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4cc9f0;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-color);
            padding: 20px 0;
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-bottom: none;
            padding: 25px 30px;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .card-header h4 {
            font-weight: 700;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .user-info i {
            font-size: 1.1rem;
        }

        .card-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .info-box {
            background-color: rgba(67, 97, 238, 0.08);
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 25px;
        }

        .info-box p {
            margin-bottom: 0;
            font-size: 0.9rem;
            color: #495057;
        }

        .info-box i {
            color: var(--primary-color);
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            .card-header {
                padding: 20px;
            }

            .card-body {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
            }
        }

        .floating-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.1;
            z-index: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Request Mock Test Session</h4>
                        <div class="user-info">
                            <i class="fas fa-user-circle"></i>
                            <span>{{ auth()->user()->name }}</span>
                        </div>
                        <i class="fas fa-video floating-icon"></i>
                    </div>

                    <div class="card-body">
                        <div class="info-box">
                            <p><i class="fas fa-info-circle"></i> Please fill out all the required information to
                                request a mock test session. Your teacher will review and respond to your request.</p>
                        </div>

                        <form action="{{ route('mock-test.store') }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="teacher_id" class="form-label">
                                    <i class="fas fa-chalkboard-teacher"></i> Select Teacher
                                </label>
                                <select name="teacher_id" id="teacher_id" class="form-select" required>
                                    <option value="">Choose a teacher...</option>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="title" class="form-label">
                                    <i class="fas fa-heading"></i> Session Title
                                </label>
                                <input type="text" name="title" id="title" class="form-control"
                                    placeholder="e.g., IELTS Speaking Practice Test" required>
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left"></i> Description
                                </label>
                                <textarea name="description" id="description" class="form-control" rows="4"
                                    placeholder="Briefly describe what you'd like to focus on during this session..."></textarea>
                            </div>

                            <div class="form-group">
                                <label for="proposed_time" class="form-label">
                                    <i class="fas fa-calendar-alt"></i> Proposed Time
                                </label>
                                <input type="datetime-local" name="proposed_time" id="proposed_time"
                                    class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="duration_minutes" class="form-label">
                                    <i class="fas fa-clock"></i> Duration
                                </label>
                                <select name="duration_minutes" id="duration_minutes" class="form-select" required>
                                    <option value="30">30 minutes</option>
                                    <option value="45">45 minutes</option>
                                    <option value="60" selected>60 minutes</option>
                                    <option value="90">90 minutes</option>
                                    <option value="120">120 minutes</option>
                                </select>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i> Submit Request
                                </button>
                                <a href="{{ route('mock-test.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Set minimum datetime to current time
        document.addEventListener('DOMContentLoaded', function() {
            const proposedTimeInput = document.getElementById('proposed_time');
            if (proposedTimeInput) {
                const now = new Date();

                // Convert ke waktu lokal Indonesia (UTC+7)
                const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
                const jakartaTime = new Date(utc + (3600000 * 7)); // UTC+7

                // Tambah 3 menit
                jakartaTime.setMinutes(jakartaTime.getMinutes() + 3);

                // Format ke YYYY-MM-DDTHH:MM
                const year = jakartaTime.getFullYear();
                const month = String(jakartaTime.getMonth() + 1).padStart(2, '0');
                const day = String(jakartaTime.getDate()).padStart(2, '0');
                const hours = String(jakartaTime.getHours()).padStart(2, '0');
                const minutes = String(jakartaTime.getMinutes()).padStart(2, '0');

                const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                proposedTimeInput.value = minDateTime;
                proposedTimeInput.min = minDateTime;
            }
        });
    </script>
</body>

</html>
