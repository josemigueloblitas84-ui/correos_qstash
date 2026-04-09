{{-- resources/views/mensajes.blade.php --}}

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show auto-dismiss-alert position-relative overflow-hidden" role="alert">
        <strong>Éxito:</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-timer-bar bg-success"></div>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show auto-dismiss-alert position-relative overflow-hidden" role="alert">
        <strong>Error:</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-timer-bar bg-danger"></div>
    </div>
@endif

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const alerts = document.querySelectorAll('.auto-dismiss-alert');
                const duration = 10000; //lo dej en 10s

                alerts.forEach(function (alertEl) {
                    const bar = alertEl.querySelector('.alert-timer-bar');

                    if (bar) {
                        bar.style.transition = `width ${duration}ms linear`;
                        setTimeout(() => {
                            bar.style.width = '0%';
                        }, 50);
                    }

                    setTimeout(() => {
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                        bsAlert.close();
                    }, duration);
                });
            });
        </script>
    @endpush

    <style>
        .alert-timer-bar {
            position: absolute;
            left: 0;
            bottom: 0;
            height: 4px;
            width: 100%;
            opacity: 0.8;
        }
    </style>
@endonce