<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Login | ERP - Whitelion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta content="Whitelion" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @if (Config::get('app.env') == 'local')
    <style>
        body {
         background-image: url({{ asset('assets/images/is_local.jpg') }});
        }
        .ribbon-wrapper-green {
            width: 90px;
            height: 90px;
            overflow: hidden;
            position: absolute;
            top: -3px;
            left: 0px;
        }
        .ribbon-green {
            font: bold 15px Sans-Serif;
            color: #333;
            text-align: center;
            text-shadow: rgba(255, 255, 255, 0.5) 0px 1px 0px;
            -webkit-transform: rotate(314deg);
            -moz-transform: rotate(314deg);
            -ms-transform: rotate(314deg);
            -o-transform: rotate(314deg);
            position: relative;
            padding: 4px 0;
            left: -36px;
            top: 20px;
            width: 160px;
            background-color: #ed0000;
            background-image: -webkit-gradient(linear, left top, left bottom, from(#ed0000), to(#e77575));
            background-image: -webkit-linear-gradient(top, #ed0000, #e77575);
            background-image: -moz-linear-gradient(top, #ed0000, #e77575);
            background-image: -ms-linear-gradient(top, #ed0000, #e77575);
            background-image: -o-linear-gradient(top, #ed0000, #e77575);
            color: #fffef8;
            -webkit-box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.3);
            -moz-box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.3);
            box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.3);
        }

        .ribbon-green:before,
        .ribbon-green:after {
            content: "";
            border-top: 3px solid #6e8900;
            border-left: 3px solid transparent;
            border-right: 3px solid transparent;
            position: absolute;
            bottom: -3px;
        }

        .ribbon-green:before {
            left: 0;
        }

        .ribbon-green:after {
            right: 0;
        }
        .form-label,h4, .col-form-label{
                color: teal !important;
            }
    </style>
    @else
    <style>
        body {
            background-image: linear-gradient(to right top, #ffffff, #d6d0fc, #aaa4f7, #7579f2, #1251eb);
        }

        
    </style>
    @endif

</head>

<body >
    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card overflow-hidden">
                        <div class="bg-primary bg-soft">
                            <div class="row">
                                <div class="col-7">
                                    @if (Config::get('app.env') == 'local')
                                    <div class="ribbon-wrapper-green">
                                        <div class="ribbon-green">Local</div>
                                    </div>
                                    @endif
                                    <div class="text-primary p-4">
                                        <h5 class="text-primary">Welcome Back !</h5>
                                        <p>Sign in to continue to Whitelion ERP.</p>
                                    </div>
                                </div>
                                <div class="col-5 align-self-end">
                                    <img src="{{ asset('assets/images/profile-img.png') }}" alt=""
                                        class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0">

                            <div class="p-2">
                                <form class="needs-validation" action="{{ route('login.process') }}" method="POST"
                                    novalidate>
                                    @csrf





                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email / Phone number</label>
                                        <input type="text" class="form-control" id="email"
                                            placeholder="Enter email/phone number" required name="email">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <div class="input-group auth-pass-inputgroup">
                                            <input type="password" class="form-control" placeholder="Enter password"
                                                aria-label="Password" aria-describedby="password-addon" required
                                                name="password">
                                            <button class="btn btn-light " type="button" id="password-addon"><i
                                                    class="mdi mdi-eye-outline"></i></button>
                                        </div>
                                    </div>



                                    <div class="mt-3 d-grid">
                                        <button class="btn btn-primary waves-effect waves-light" type="submit">Log
                                            In</button>
                                    </div>



                                    <br>

                                    @if (session('error'))
                                        <div class="alert alert-danger" role="alert">
                                            <i class="mdi mdi-block-helper me-2"></i> {{ session('error') }}
                                        </div>
                                    @endif
                                    @if (session('success'))
                                        <div class="alert alert-success" role="alert">
                                            <i class="mdi mdi-check-all me-2"></i> {{ session('success') }}
                                        </div>
                                    @endif


                                    <div class="mt-4 text-center">
                                        <a href="{{ route('login.otp') }}" class="text-primary">Login With OTP</a>

                                    </div>


                                    <div class="mt-1 text-center">
                                        <a href="{{ route('forgot.password') }}" class="text-primary"><i
                                                class="mdi mdi-lock me-1"></i> Forgot your password?</a>

                                    </div>




                                </form>
                                {{-- <div class="mt-2" style="text-align: -webkit-center;">
                                    <label class="form-label font-size-16 text-danger">Erp will be resume on 1st april 9am.</br>Thanks</label>
                                </div> --}}
                            </div>

                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
    <!-- end account-pages -->

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('assets/js/client/client.min.js') }}"></script>
    

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script type="text/javascript">
        setTimeout(function() {

            $(".alert").hide(2000);

        }, 6000);
        var client = new ClientJS();
        var fingerprint = '';
        fingerprint = fingerprint + '\n getBrowserData : '+JSON.stringify(client.getBrowserData());
        fingerprint = fingerprint + '\n getFingerprint : '+client.getFingerprint();
        fingerprint = fingerprint + '\n getCustomFingerprint : '+client.getCustomFingerprint();
        fingerprint = fingerprint + '\n getUserAgent : '+client.getUserAgent();
        fingerprint = fingerprint + '\n getUserAgentLowerCase : '+client.getUserAgentLowerCase();
        fingerprint = fingerprint + '\n Browser Data : '+client.getBrowser();
        fingerprint = fingerprint + '\n getBrowserVersion : '+client.getBrowserVersion();
        fingerprint = fingerprint + '\n getBrowserMajorVersion : '+client.getBrowserMajorVersion();
        fingerprint = fingerprint + '\n isIE : '+client.isIE();
        fingerprint = fingerprint + '\n isChrome : '+client.isChrome();
        fingerprint = fingerprint + '\n isFirefox : '+client.isFirefox();
        fingerprint = fingerprint + '\n isSafari : '+client.isSafari();
        fingerprint = fingerprint + '\n isOpera : '+client.isOpera();
        fingerprint = fingerprint + '\n getEngine : '+client.getEngine();
        fingerprint = fingerprint + '\n getEngineVersion : '+client.getEngineVersion();
        fingerprint = fingerprint + '\n getOS : '+client.getOS();
        fingerprint = fingerprint + '\n getOSVersion : '+client.getOSVersion();
        fingerprint = fingerprint + '\n isWindows : '+client.isWindows();
        fingerprint = fingerprint + '\n isMac : '+client.isMac();
        fingerprint = fingerprint + '\n isLinux : '+client.isLinux();
        fingerprint = fingerprint + '\n isUbuntu : '+client.isUbuntu();
        fingerprint = fingerprint + '\n isSolaris : '+client.isSolaris();
        fingerprint = fingerprint + '\n getDevice : '+client.getDevice();
        fingerprint = fingerprint + '\n getDeviceType : '+client.getDeviceType();
        fingerprint = fingerprint + '\n getDeviceVendor : '+client.getDeviceVendor();
        fingerprint = fingerprint + '\n getCPU : '+client.getCPU();
        fingerprint = fingerprint + '\n isMobile : '+client.isMobile();
        fingerprint = fingerprint + '\n isMobileMajor : '+client.isMobileMajor();
        fingerprint = fingerprint + '\n isMobileAndroid : '+client.isMobileAndroid();
        fingerprint = fingerprint + '\n isMobileOpera : '+client.isMobileOpera();
        fingerprint = fingerprint + '\n isMobileWindows : '+client.isMobileWindows();
        fingerprint = fingerprint + '\n isMobileBlackBerry : '+client.isMobileBlackBerry();
        fingerprint = fingerprint + '\n isMobileIOS : '+client.isMobileIOS();
        fingerprint = fingerprint + '\n isIphone : '+client.isIphone();
        fingerprint = fingerprint + '\n isIpad : '+client.isIpad();
        fingerprint = fingerprint + '\n isIpod : '+client.isIpod();
        fingerprint = fingerprint + '\n getScreenPrint : '+client.getScreenPrint();
        fingerprint = fingerprint + '\n getColorDepth : '+client.getColorDepth();
        fingerprint = fingerprint + '\n getCurrentResolution : '+client.getCurrentResolution();
        fingerprint = fingerprint + '\n getAvailableResolution : '+client.getAvailableResolution();
        fingerprint = fingerprint + '\n getDeviceXDPI : '+client.getDeviceXDPI();
        fingerprint = fingerprint + '\n getDeviceYDPI : '+client.getDeviceYDPI();
        fingerprint = fingerprint + '\n getPlugins : '+client.getPlugins();
        fingerprint = fingerprint + '\n isJava : '+client.isJava();
        fingerprint = fingerprint + '\n getJavaVersion : '+client.getJavaVersion(); // functional only in java and full builds, throws an error otherwise
        fingerprint = fingerprint + '\n isFlash : '+client.isFlash();
        fingerprint = fingerprint + '\n getFlashVersion : '+client.getFlashVersion(); // functional only in flash and full builds, throws an error otherwise
        fingerprint = fingerprint + '\n isSilverlight : '+client.isSilverlight();
        fingerprint = fingerprint + '\n getSilverlightVersion : '+client.getSilverlightVersion();
        fingerprint = fingerprint + '\n getMimeTypes : '+client.getMimeTypes();
        fingerprint = fingerprint + '\n isMimeTypes : '+client.isMimeTypes();
        fingerprint = fingerprint + '\n isFont : '+client.isFont();
        fingerprint = fingerprint + '\n getFonts : '+client.getFonts();
        fingerprint = fingerprint + '\n isLocalStorage : '+client.isLocalStorage();
        fingerprint = fingerprint + '\n isSessionStorage : '+client.isSessionStorage();
        fingerprint = fingerprint + '\n isCookie : '+client.isCookie();
        fingerprint = fingerprint + '\n getTimeZone : '+client.getTimeZone();
        fingerprint = fingerprint + '\n getLanguage : '+client.getLanguage();
        fingerprint = fingerprint + '\n getSystemLanguage : '+client.getSystemLanguage();
        fingerprint = fingerprint + '\n isCanvas : '+client.isCanvas();
        fingerprint = fingerprint + '\n getCanvasPrint : '+client.getCanvasPrint();
        console.log(fingerprint);
    </script>
</body>


</html>
