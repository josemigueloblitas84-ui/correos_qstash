@extends('plantilla.app')

@section('title', 'Mi Perfil')

@section('content')

    <div class="app-content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h2>Mi Perfil</h2>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">

                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img
                                    class="profile-user-img img-fluid img-circle"
                                    src="{{ asset('assets/img/user2-160x160.jpg') }}"
                                    alt="User profile picture">
                            </div>

                            <h3 class="profile-username text-center">{{ auth()->user()->name }}</h3>

                            <p class="text-muted text-center">{{ auth()->user()->email }}</p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Followers</b> <a class="float-end">1,322</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Following</b> <a class="float-end">543</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Friends</b> <a class="float-end">13,287</a>
                                </li>
                            </ul>

                            <a href="#" class="btn btn-primary w-100">
                                <b>Follow</b>
                            </a>
                        </div>
                    </div>

                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">About Me</h3>
                        </div>

                        <div class="card-body">
                            <strong><i class="fas fa-book me-1"></i> Education</strong>

                            <p class="text-muted">
                                B.S. in Computer Science from the University of Tennessee at Knoxville
                            </p>

                            <hr>

                            <strong><i class="fas fa-map-marker-alt me-1"></i> Location</strong>

                            <p class="text-muted">Malibu, California</p>

                            <hr>

                            <strong><i class="fas fa-pencil-alt me-1"></i> Skills</strong>

                            <p class="text-muted">
                                <span class="badge text-bg-danger">UI Design</span>
                                <span class="badge text-bg-success">Coding</span>
                                <span class="badge text-bg-info">Javascript</span>
                                <span class="badge text-bg-warning">PHP</span>
                                <span class="badge text-bg-primary">Node.js</span>
                            </p>

                            <hr>

                            <strong><i class="far fa-file-alt me-1"></i> Notes</strong>

                            <p class="text-muted">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam fermentum enim neque.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#activity" type="button">
                                        Actividad
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#timeline" type="button">
                                        Seguimiento
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#settings" type="button">
                                        Ajustes
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="activity">
                                    <div class="post">
                                        <div class="user-block">
                                            <img class="img-circle img-bordered-sm"
                                                src="{{ asset('assets/img/user1-128x128.jpg') }}"
                                                alt="user image">
                                            <span class="username">
                                                <a href="#">Jonathan Burke Jr.</a>
                                                <a href="#" class="float-end btn-tool">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </span>
                                            <span class="description">Shared publicly - 7:30 PM today</span>
                                        </div>

                                        <p>
                                            Lorem ipsum represents a long-held tradition for designers,
                                            typographers and the like. Some people hate it and argue for
                                            its demise, but others ignore the hate as they create awesome
                                            tools to help create filler text for everyone from bacon lovers
                                            to Charlie Sheen fans.
                                        </p>

                                        <p>
                                            <a href="#" class="link-black text-sm me-2">
                                                <i class="fas fa-share me-1"></i> Share
                                            </a>
                                            <a href="#" class="link-black text-sm">
                                                <i class="far fa-thumbs-up me-1"></i> Like
                                            </a>
                                            <span class="float-end">
                                                <a href="#" class="link-black text-sm">
                                                    <i class="far fa-comments me-1"></i> Comments (5)
                                                </a>
                                            </span>
                                        </p>

                                        <input class="form-control form-control-sm" type="text" placeholder="Type a comment">
                                    </div>

                                    <div class="post clearfix">
                                        <div class="user-block">
                                            <img class="img-circle img-bordered-sm"
                                                src="{{ asset('assets/img/user7-128x128.jpg') }}"
                                                alt="User Image">
                                            <span class="username">
                                                <a href="#">Sarah Ross</a>
                                                <a href="#" class="float-end btn-tool">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </span>
                                            <span class="description">Sent you a message - 3 days ago</span>
                                        </div>

                                        <p>
                                            Lorem ipsum represents a long-held tradition for designers,
                                            typographers and the like. Some people hate it and argue for
                                            its demise, but others ignore the hate as they create awesome
                                            tools to help create filler text for everyone from bacon lovers
                                            to Charlie Sheen fans.
                                        </p>

                                        <form class="form-horizontal">
                                            <div class="input-group input-group-sm mb-0">
                                                <input class="form-control form-control-sm" placeholder="Response">
                                                <button type="submit" class="btn btn-danger">Send</button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="post">
                                        <div class="user-block">
                                            <img class="img-circle img-bordered-sm"
                                                src="{{ asset('assets/img/user6-128x128.jpg') }}"
                                                alt="User Image">
                                            <span class="username">
                                                <a href="#">Adam Jones</a>
                                                <a href="#" class="float-end btn-tool">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </span>
                                            <span class="description">Posted 5 photos - 5 days ago</span>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <img class="img-fluid" src="https://placehold.co/500x300" alt="Photo">
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <img class="img-fluid mb-3" src="https://placehold.co/300x300" alt="Photo">
                                                        <img class="img-fluid" src="https://placehold.co/300x300" alt="Photo">
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <img class="img-fluid mb-3" src="https://placehold.co/300x300" alt="Photo">
                                                        <img class="img-fluid" src="https://placehold.co/300x300" alt="Photo">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <p>
                                            <a href="#" class="link-black text-sm me-2">
                                                <i class="fas fa-share me-1"></i> Share
                                            </a>
                                            <a href="#" class="link-black text-sm">
                                                <i class="far fa-thumbs-up me-1"></i> Like
                                            </a>
                                            <span class="float-end">
                                                <a href="#" class="link-black text-sm">
                                                    <i class="far fa-comments me-1"></i> Comments (5)
                                                </a>
                                            </span>
                                        </p>

                                        <input class="form-control form-control-sm" type="text" placeholder="Type a comment">
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="timeline">
                                    <div class="timeline timeline-inverse">
                                        <div class="time-label">
                                            <span class="bg-danger">
                                                10 Feb. 2014
                                            </span>
                                        </div>

                                        <div>
                                            <i class="fas fa-envelope bg-primary"></i>

                                            <div class="timeline-item">
                                                <span class="time"><i class="far fa-clock"></i> 12:05</span>

                                                <h3 class="timeline-header">
                                                    <a href="#">Support Team</a> sent you an email
                                                </h3>

                                                <div class="timeline-body">
                                                    Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
                                                    weebly ning heekya handango imeem plugg dopplr jibjab, movity
                                                    jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
                                                    quora plaxo ideeli hulu weebly balihoo...
                                                </div>
                                                <div class="timeline-footer">
                                                    <a href="#" class="btn btn-primary btn-sm">Read more</a>
                                                    <a href="#" class="btn btn-danger btn-sm">Delete</a>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <i class="fas fa-user bg-info"></i>

                                            <div class="timeline-item">
                                                <span class="time"><i class="far fa-clock"></i> 5 mins ago</span>

                                                <h3 class="timeline-header border-0">
                                                    <a href="#">Sarah Young</a> accepted your friend request
                                                </h3>
                                            </div>
                                        </div>

                                        <div>
                                            <i class="fas fa-comments bg-warning"></i>

                                            <div class="timeline-item">
                                                <span class="time"><i class="far fa-clock"></i> 27 mins ago</span>

                                                <h3 class="timeline-header">
                                                    <a href="#">Jay White</a> commented on your post
                                                </h3>

                                                <div class="timeline-body">
                                                    Take me to your leader!
                                                    Switzerland is small and neutral!
                                                    We are more like Germany, ambitious and misunderstood!
                                                </div>
                                                <div class="timeline-footer">
                                                    <a href="#" class="btn btn-warning btn-flat btn-sm">View comment</a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="time-label">
                                            <span class="bg-success">
                                                3 Jan. 2014
                                            </span>
                                        </div>

                                        <div>
                                            <i class="fas fa-camera bg-purple"></i>

                                            <div class="timeline-item">
                                                <span class="time"><i class="far fa-clock"></i> 2 days ago</span>

                                                <h3 class="timeline-header">
                                                    <a href="#">Mina Lee</a> uploaded new photos
                                                </h3>

                                                <div class="timeline-body">
                                                    <img src="https://placehold.co/150x100" alt="...">
                                                    <img src="https://placehold.co/150x100" alt="...">
                                                    <img src="https://placehold.co/150x100" alt="...">
                                                    <img src="https://placehold.co/150x100" alt="...">
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <i class="far fa-clock bg-gray"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="settings">
                                    <div class="mb-4">
                                        @include('profile.partials.update-profile-information-form')
                                    </div>

                                    <hr class="my-4">

                                    <div class="mb-4">
                                        @include('profile.partials.update-password-form')
                                    </div>

                                    <hr class="my-4">

                                    <div>
                                        @include('profile.partials.delete-user-form')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('.toggle-password').forEach(function(button) {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
</script>
@endpush