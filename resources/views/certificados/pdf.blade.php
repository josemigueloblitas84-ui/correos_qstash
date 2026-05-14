@php
    $pageSize = ($page['size'] ?? 'a4') === 'carta' ? 'letter' : 'a4';
    $pageOrientation = ($page['orientation'] ?? 'horizontal') === 'vertical' ? 'portrait' : 'landscape';
    $canvasWidth = (int) ($page['canvas_width'] ?? 1123);
    $canvasHeight = (int) ($page['canvas_height'] ?? 794);

    $paperDimensions = match ($pageSize . '-' . $pageOrientation) {
        'a4-landscape' => ['width' => 841.89, 'height' => 595.28],
        'a4-portrait' => ['width' => 595.28, 'height' => 841.89],
        'letter-landscape' => ['width' => 792.00, 'height' => 612.00],
        'letter-portrait' => ['width' => 612.00, 'height' => 792.00],
        default => ['width' => 841.89, 'height' => 595.28],
    };

    $pageWidthPt = $paperDimensions['width'];
    $pageHeightPt = $paperDimensions['height'];
    $scaleX = $canvasWidth > 0 ? $pageWidthPt / $canvasWidth : 1;
    $scaleY = $canvasHeight > 0 ? $pageHeightPt / $canvasHeight : 1;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Certificado</title>
    <style>
        @page {
            margin: 0;
            size: {{ $pageSize }} {{ $pageOrientation }};
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
        }

        .page {
            position: relative;
            width: {{ $pageWidthPt }}pt;
            height: {{ $pageHeightPt }}pt;
            overflow: hidden;
            background: #ffffff;
        }

        .element {
            position: absolute;
            box-sizing: border-box;
            overflow: hidden;
        }

        .element-text {
            white-space: pre-wrap;
        }

        .element-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .element-qr-box {
            border: 1.5pt solid #111827;
            border-radius: 10pt;
            background: transparent;
        }
    </style>
</head>
<body>
    <div class="page">
        @foreach ($elements as $element)
            @php
                $style = $element['style'] ?? [];
                $left = (float) ($element['left'] ?? 0);
                $top = (float) ($element['top'] ?? 0);
                $width = (float) ($element['width'] ?? 0);
                $height = (float) ($element['height'] ?? 0);
                $fontSize = (float) ($style['fontSize'] ?? 24);
                $requestedFontFamily = $style['fontFamily'] ?? 'Arial';
                $fill = $style['fill'] ?? '#111111';
                $fontWeight = ($style['fontWeight'] ?? 'normal') === 'bold' ? '700' : '400';
                $fontStyle = $style['fontStyle'] ?? 'normal';
                $underline = !empty($style['underline']) ? 'underline' : 'none';
                $textAlign = $style['textAlign'] ?? 'left';
                $lineHeight = (float) ($style['lineHeight'] ?? 1.16);
                $charSpacing = ((float) ($style['charSpacing'] ?? 0)) / 1000;

                $fontFamily = match ($requestedFontFamily) {
                    'Arial', 'Verdana', 'Tahoma' => 'Helvetica',
                    'Times New Roman', 'Georgia' => 'Times-Roman',
                    'Courier New' => 'Courier',
                    default => 'DejaVu Sans',
                };

                $scaledLeft = $left * $scaleX;
                $scaledTop = $top * $scaleY;
                $scaledWidth = $width * $scaleX;
                $scaledHeight = $height * $scaleY;
                $scaledFontSize = $fontSize * min($scaleX, $scaleY);
            @endphp

            @if (in_array($element['type'] ?? '', ['texto', 'campo_dinamico'], true))
                <div
                    class="element element-text"
                    style="
                        left: {{ $scaledLeft }}pt;
                        top: {{ $scaledTop }}pt;
                        width: {{ $scaledWidth }}pt;
                        min-height: {{ $scaledHeight }}pt;
                        font-size: {{ $scaledFontSize }}pt;
                        font-family: '{{ $fontFamily }}';
                        color: {{ $fill }};
                        font-weight: {{ $fontWeight }};
                        font-style: {{ $fontStyle }};
                        text-decoration: {{ $underline }};
                        text-align: {{ $textAlign }};
                        line-height: {{ $lineHeight }};
                        letter-spacing: {{ $charSpacing }}em;
                    "
                >{{ $element['text'] ?? '' }}</div>
            @elseif (($element['type'] ?? '') === 'qr')
                <div
                    class="element element-qr-box"
                    style="
                        left: {{ $scaledLeft }}pt;
                        top: {{ $scaledTop }}pt;
                        width: {{ $scaledWidth }}pt;
                        height: {{ $scaledHeight }}pt;
                    "
                ></div>
            @elseif (!empty($element['image_src']))
                <div
                    class="element element-image"
                    style="
                        left: {{ $scaledLeft }}pt;
                        top: {{ $scaledTop }}pt;
                        width: {{ $scaledWidth }}pt;
                        height: {{ $scaledHeight }}pt;
                    "
                >
                    <img src="{{ $element['image_src'] }}" alt="">
                </div>
            @endif
        @endforeach
    </div>
</body>
</html>
