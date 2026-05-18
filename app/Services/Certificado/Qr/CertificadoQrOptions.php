<?php

namespace App\Services\Certificado\Qr;

use chillerlan\QRCode\QROptions;

class CertificadoQrOptions extends QROptions
{
    protected string $cornerFrameShape = 'rounded';
    protected string $cornerDotShape = 'circle';
    protected bool $cornerTopLeft = true;
    protected bool $cornerTopRight = true;
    protected bool $cornerBottomLeft = true;

    protected function set_cornerFrameShape(string $value): void
    {
        $this->cornerFrameShape = in_array($value, ['none', 'square', 'rounded', 'circle'], true)
            ? $value
            : 'rounded';
    }

    protected function set_cornerDotShape(string $value): void
    {
        $this->cornerDotShape = in_array($value, ['none', 'square', 'circle'], true)
            ? $value
            : 'circle';
    }

    protected function set_cornerTopLeft(bool $value): void
    {
        $this->cornerTopLeft = $value;
    }

    protected function set_cornerTopRight(bool $value): void
    {
        $this->cornerTopRight = $value;
    }

    protected function set_cornerBottomLeft(bool $value): void
    {
        $this->cornerBottomLeft = $value;
    }
}
