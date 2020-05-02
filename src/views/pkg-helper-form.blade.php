<!DOCTYPE html>
<html dir="ltr" lang="{{ app()->getLocale() }}" ng-app="app">
    <head>
        @include($theme.'-pkg::head')
        @include('helper-pkg::angular-js/css')
    </head>
    <body>
        <div class="main-wrap">
            @include('themes/'.$theme.'/includes/common/header')
            <div class="content-wrap">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-header-inner">
                        <div class="page-header-content-left">
                            <div class="page-header-content title-block">
                                <h1 class="title">PKG Helper Form</h1>
                            </div>
                        </div>
                    </div><!-- Page Header Inner -->
                </div><!-- Page Header -->

                <!-- Page Main Content -->
                <form action="{{route('generatePkg')}}" method="post" id="form">
                    @csrf
                <div class="page-main-content">
                    <div class="container">
                       <div class="row">
                            <div class="col-sm-12">
                                <div class="page-form pb-60">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6">
                                            <div class="input-text form-group">
                                                <label>
                                                    PKG Name
                                                    <span class="mandatory"> *</span>
                                                </label>
                                                <input type="text" name="pkg_name" class="form-control" placeholder="Eg: location-pkg">
                                            </div><!-- Field -->
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="input-text form-group">
                                                <label>
                                                    Template
                                                    <span class="mandatory"> *</span>
                                                </label>
                                                <input type="text" name="template" value="theme2" class="form-control" placeholder="Eg: theme2">
                                            </div><!-- Field -->
                                        </div>
                                    </div>
                                    @for($i=1;$i<=5;$i++)
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6">
                                            <div class="input-text form-group">
                                                <label>
                                                    Module Name
                                                    <span class="mandatory"> *</span>
                                                </label>
                                                <input type="text" name="module_name[]" class="form-control" placeholder="Eg: country">
                                            </div><!-- Field -->
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="input-text form-group">
                                                <label>
                                                    Module Plural Name
                                                    <span class="mandatory"> *</span>
                                                </label>
                                                <input type="text" name="module_plural_name[]" class="form-control" placeholder="Eg: countries">
                                            </div><!-- Field -->
                                        </div>
                                    </div>
                                    @endfor


                              </div><!-- Page Form -->
                          </div><!-- Column -->
                       </div><!-- Row -->
                     </div><!-- Container -->
                    <div class="page-form-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div><!-- Modal Footer -->
                </div><!-- Page Main Content -->
                </form>
            </div><!-- Content Wrap -->

        </div><!-- Main Wrap -->
        @include('themes/'.$theme.'/includes/common/footer')
        @include($theme.'-pkg::js')
        <script type="text/javascript">
            var logged_user_permissions = [];
            @foreach(Auth::user()->perms() as $permission_name)
                logged_user_permissions.push('{{$permission_name}}');
            @endforeach
        </script>
        @include('role-pkg::setup')
        @include('company-pkg::setup')
        @include('user-pkg::setup')
        @include('location-pkg::setup')
        @include('import-cron-job-pkg::setup')
        @include('entity-pkg::setup')
        @include('project-pkg::setup')
        @include('module-pkg::setup')
        @include('employee-pkg::setup')
        @include('status-pkg::setup')
        @include('auth-pkg::auth-setup')
    </body>

</html>



