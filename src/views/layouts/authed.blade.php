<!DOCTYPE html>
<html dir="ltr" lang="{{ app()->getLocale() }}" ng-app="app">
    <head>
        @include($theme.'/partials/common/head')
        @yield('header_css')
    </head>
    <body>
        @include($theme.'/partials/common/common-header')
        <div class="main-wrap">
            @include($theme.'/partials/common/header')
            @yield('content')
        </div><!-- Main Wrap -->
        @include($theme.'/partials/common/footer')
        @include($theme.'/partials/common/common-js')
        @yield('footer_js')
    </body>

</html>
