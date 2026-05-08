<!doctype html>
<html lang="es">
<!--begin::Head-->

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Fundación Unifranz | Agenda </title>

  <!--begin::Accessibility Meta Tags-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
  <link rel="icon" href="{{ asset('assets/img/funda.ico') }}" type="image/x-icon">
  <!--end::Accessibility Meta Tags-->

  <!--begin::Primary Meta Tags-->
  <meta name="title" content="AdminLTE v4 | Dashboard" />
  <meta name="author" content="ColorlibHQ" />
  <meta name="description"
    content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS. Fully accessible with WCAG 2.1 AA compliance." />
  <meta name="keywords"
    content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard, accessible admin panel, WCAG compliant" />
  <!--end::Primary Meta Tags-->

  <!--begin::Accessibility Features-->
  <!-- Skip links will be dynamically added by accessibility.js -->
  <meta name="supported-color-schemes" content="light dark" />
  <link rel="preload" href="{{ asset('adminlte/dist/css/adminlte.css') }}" as="style" />
  <!--end::Accessibility Features-->

  <!--begin::Fonts-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print"
    onload="this.media = 'all'" />
  <!--end::Fonts-->

  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(OverlayScrollbars)-->

  <!--begin::Third Party Plugin(Bootstrap Icons)-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(Bootstrap Icons)-->

  <!--begin::Required Plugin(AdminLTE)-->
  <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}" />
  <!--end::Required Plugin(AdminLTE)-->
  <!--Letrita del sistema-->
  <link rel="stylesheet" href="{{ asset('assets/css/gloBal-font.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/app-layout.css') }}">

  <!-- apexcharts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
    integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous" />

  <!-- jsvectormap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
    integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4=" crossorigin="anonymous" />
  <!--FONTAWESOME-->
  <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
  <!--DATABALES-->
  <!-- DataTables Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css">
  <!--Estilo de SweetAlert2-->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.24/dist/sweetalert2.min.css" rel="stylesheet">
  <!--Estilos de Select2-->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!--Estilos de FormPMultiPasos-->
  @stack('styles')
</head>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg sidebar-mini bg-body-tertiary">
  <!--begin::App Wrapper-->
  <div class="app-wrapper">
    <!--begin::Header-->
    @include('plantilla.header')
    <!--end::Header-->
    <!--begin::Sidebar-->
    @include('plantilla.menu')
    <!--end::Sidebar-->
    <!--begin::App Main-->
    <main class="app-main">
      @yield('content')
    </main>
    <!--end::App Main-->
    <!--begin::Footer-->
    <footer class="app-footer app-footer-custom">
      <!--begin::To the end-->
      <!--end::To the end-->
      <!--begin::Copyright-->
      <strong class="app-footer-custom__copy">
        Copyright &copy; 2025-2026&nbsp;
        <span>Fundación Unifranz</span>
      </strong>
      <span class="app-footer-custom__rights">Todos los derechos reservados.</span>
      <!--end::Copyright-->
    </footer>
    <!--end::Footer-->
  </div>
  <!--end::App Wrapper-->
  <!--begin::Script-->
  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
    crossorigin="anonymous"></script>
  <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    crossorigin="anonymous"></script>
  <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
  <script src="{{ asset('adminlte/dist/js/adminlte.js') }}"></script>
  <!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
  <script>
    const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
    const Default = {
      scrollbarTheme: 'os-theme-light',
      scrollbarAutoHide: 'leave',
      scrollbarClickScroll: true,
    };
    document.addEventListener('DOMContentLoaded', function () {
      const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);

      // Disable OverlayScrollbars on mobile devices to prevent touch interference
      const isMobile = window.innerWidth <= 992;

      if (
        sidebarWrapper &&
        OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
        !isMobile
      ) {
        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
          scrollbars: {
            theme: Default.scrollbarTheme,
            autoHide: Default.scrollbarAutoHide,
            clickScroll: Default.scrollbarClickScroll,
          },
        });
      }
    });
  </script>
  <!--end::OverlayScrollbars Configure-->

  <!-- Esto es de las tablitas-->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
  <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
  <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.js"></script>
  <script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.colVis.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.24/dist/sweetalert2.all.min.js"></script>
  <!---->
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const table1 = document.querySelector('#example1');
    if (table1) {
      new DataTable('#example1', {
        responsive: true,
        lengthChange: true,
        pageLength: 10,
        autoWidth: false,
        pagingType: 'full_numbers',
        layout: {
          topStart: null,
          topEnd: 'search',
          bottomStart: ['pageLength', 'info'],
          bottomEnd: 'paging'
        },
        language: {
          url: "{{ asset('assets/datatables/i18n/es-ES.json') }}"
        }
      });
    }

    const table2 = document.querySelector('#example2');
    if (table2) {
      new DataTable('#example2', {
        paging: true,
        lengthChange: true,
        searching: false,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true
      });
    }
  });
</script>

  @stack('scripts')
</body>
<!--end::Body-->

</html>
