<?php

namespace App\Services\Certificado\Qr;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRMarkupSVG;

class CertificadoQrSvgOutput extends QRMarkupSVG
{
    protected function paths(): string
    {
        $this->options->connectPaths = true;

        return parent::paths() . $this->getFinderPatterns();
    }

    protected function module(int $x, int $y, int $M_TYPE): string
    {
        if (
            $this->matrix->checkType($x, $y, QRMatrix::M_FINDER)
            || $this->matrix->checkType($x, $y, QRMatrix::M_FINDER_DOT)
        ) {
            return '';
        }

        if ($this->matrix->checkType($x, $y, QRMatrix::M_ALIGNMENT_DARK)) {
            return parent::module($x, $y, QRMatrix::M_DATA_DARK);
        }

        if ($this->matrix->checkType($x, $y, QRMatrix::M_ALIGNMENT)) {
            return parent::module($x, $y, QRMatrix::M_DATA);
        }

        return parent::module($x, $y, $M_TYPE);
    }

    protected function getFinderPatterns(): string
    {
        $qz = $this->options->addQuietzone ? $this->options->quietzoneSize : 0;
        $corners = [
            ['enabled' => $this->options->cornerTopLeft, 'x' => 0 + $qz, 'y' => 0 + $qz],
            ['enabled' => $this->options->cornerBottomLeft, 'x' => 0 + $qz, 'y' => $this->moduleCount - $qz - 7],
            ['enabled' => $this->options->cornerTopRight, 'x' => $this->moduleCount - $qz - 7, 'y' => 0 + $qz],
        ];

        $svg = [];

        foreach ($corners as $corner) {
            if (! $corner['enabled']) {
                continue;
            }

            $svg[] = $this->buildFinderFrame((float) $corner['x'], (float) $corner['y']);
            $svg[] = $this->buildFinderDot((float) $corner['x'], (float) $corner['y']);
        }

        return implode($this->options->eol, array_filter($svg));
    }

    protected function buildFinderFrame(float $x, float $y): string
    {
        $shape = $this->options->cornerFrameShape;

        if ($shape === 'none') {
            return '';
        }

        if ($shape === 'circle') {
            $cx = $x + 3.5;
            $cy = $y + 3.5;
            $outer = $this->circlePath($cx, $cy, 3.5);
            $inner = $this->circlePath($cx, $cy, 2.5);

            return $this->buildLightCutFinder($outer, $inner);
        }

        if ($shape === 'square') {
            $outer = sprintf('M%1$s,%2$s h7 v7 h-7Z', $x, $y);
            $inner = sprintf('M%1$s,%2$s h5 v5 h-5Z', $x + 1, $y + 1);

            return $this->buildLightCutFinder($outer, $inner);
        }

        $path = sprintf(
            'M%1$s,%2$s m2,0 h3 q2,0 2,2 v3 q0,2 -2,2 h-3 q-2,0 -2,-2 v-3 q0,-2 2,-2z m0,1 q-1,0 -1,1 v3 q0,1 1,1 h3 q1,0 1,-1 v-3 q0,-1 -1,-1z',
            $x,
            $y
        );

        return $this->path($path, QRMatrix::M_FINDER_DARK);
    }

    protected function buildFinderDot(float $x, float $y): string
    {
        $shape = $this->options->cornerDotShape;

        if ($shape === 'none') {
            return '';
        }

        $path = $shape === 'square'
            ? sprintf('M%1$s,%2$s h3 v3 h-3Z', $x + 2, $y + 2)
            : $this->circlePath($x + 3.5, $y + 3.5, 1.5);

        return $this->path($path, QRMatrix::M_FINDER_DOT);
    }

    protected function buildLightCutFinder(string $outerPath, string $innerPath): string
    {
        return implode($this->options->eol, [
            $this->path($outerPath, QRMatrix::M_FINDER_DARK),
            $this->path($innerPath, QRMatrix::M_FINDER),
        ]);
    }

    protected function circlePath(float $cx, float $cy, float $r): string
    {
        return sprintf(
            'M%1$s %2$s m-%3$s,0 a%3$s %3$s 0 1 0 %4$s 0 a%3$s %3$s 0 1 0 -%4$s 0Z',
            $cx,
            $cy,
            $r,
            $r * 2
        );
    }
}
