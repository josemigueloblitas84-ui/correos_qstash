<ul class="page-toc">
    <li class="toc-title">En esta pagina</li>
    @foreach ($sections as $section)
        <li>
            <a href="{{ $section['href'] }}">{{ $section['title'] }}</a>
            @if (! empty($section['children']))
                <ul>
                    @foreach ($section['children'] as $child)
                        <li>
                            <a href="{{ $child['href'] }}">{{ $child['title'] }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </li>
    @endforeach
</ul>
