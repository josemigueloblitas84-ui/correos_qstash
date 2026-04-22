@php($docsBrand = $docsBrand ?? app(\App\Services\ConfiguracionSistemaService::class)->getPresentationData())

<img
    src="{{ $docsBrand['logo_principal_url'] ?? asset('assets/img/logoFundacionTrans.png') }}"
    alt="{{ $docsBrand['nombre_institucion'] ?? config('app.name') }}"
    style="height: 30px; width: 30px; object-fit: contain; border-radius: 6px;"
>
