@extends('larecipe::default')

@section('content')
@php($page = app(\App\Services\DocsPresentationService::class)->preparePage($content))
<div>
    @include('larecipe::partials.sidebar')

    <div class="documentation is-{{ config('larecipe.ui.code_theme') }}" :class="{'expanded': ! sidebar}">
        {!! $page['toc'] !!}
        {!! $page['content'] !!}
        @include('larecipe::plugins.forum')
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const headings = [...document.querySelectorAll('.documentation h2[id], .documentation h3[id]')];
    const tocLinks = [...document.querySelectorAll('.page-toc a[href^="#"]')];

    function updateActiveToc() {
        let current = null;
        const offset = 140;

        for (const heading of headings) {
            const top = heading.getBoundingClientRect().top;
            if (top <= offset) {
                current = heading;
            }
        }

        tocLinks.forEach(link => link.classList.remove('is-current'));

        if (!current) return;

        const activeLink = tocLinks.find(
            link => link.getAttribute('href') === `#${current.id}`
        );

        if (activeLink) {
            activeLink.classList.add('is-current');
        }
    }

    window.addEventListener('scroll', updateActiveToc, { passive: true });
    window.addEventListener('load', updateActiveToc);
    updateActiveToc();
});
</script>

@endsection
