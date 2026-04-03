<!DOCTYPE html>
<html>
  <head>
    <title>My Profile</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <style>
        [data-bs-theme="light"] {
        --bs-blue: #0d6efd;
        --bs-indigo: #6610f2;
        --bs-purple: #6f42c1;
        --bs-pink: #d63384;
        --bs-red: #dc3545;
        --bs-orange: #fd7e14;
        --bs-yellow: #ffc107;
        --bs-green: #198754;
        --bs-teal: #20c997;
        --bs-cyan: #0dcaf0;
        --bs-black: #000;
        --bs-white: #fff;
        --bs-gray: #6c757d;
        --bs-gray-dark: #343a40;
        --bs-gray-100: #f8f9fa;
        --bs-gray-200: #e9ecef;
        --bs-gray-300: #dee2e6;
        --bs-gray-400: #ced4da;
        --bs-gray-500: #adb5bd;
        --bs-gray-600: #6c757d;
        --bs-gray-700: #495057;
        --bs-gray-800: #343a40;
        --bs-gray-900: #212529;
        --bs-primary: #0d6efd;
        --bs-secondary: #6c757d;
        --bs-success: #198754;
        --bs-info: #0dcaf0;
        --bs-warning: #ffc107;
        --bs-danger: #dc3545;
        --bs-light: #f8f9fa;
        --bs-dark: #212529;
        --bs-primary-rgb: 13, 110, 253;
        --bs-secondary-rgb: 108, 117, 125;
        --bs-success-rgb: 25, 135, 84;
        --bs-info-rgb: 13, 202, 240;
        --bs-warning-rgb: 255, 193, 7;
        --bs-danger-rgb: 220, 53, 69;
        --bs-light-rgb: 248, 249, 250;
        --bs-dark-rgb: 33, 37, 41;
        --bs-primary-text-emphasis: #052c65;
        --bs-secondary-text-emphasis: #2b2f32;
        --bs-success-text-emphasis: #0a3622;
        --bs-info-text-emphasis: #055160;
        --bs-warning-text-emphasis: #664d03;
        --bs-danger-text-emphasis: #58151c;
        --bs-light-text-emphasis: #495057;
        --bs-dark-text-emphasis: #495057;
        --bs-primary-bg-subtle: #cfe2ff;
        --bs-secondary-bg-subtle: #e2e3e5;
        --bs-success-bg-subtle: #d1e7dd;
        --bs-info-bg-subtle: #cff4fc;
        --bs-warning-bg-subtle: #fff3cd;
        --bs-danger-bg-subtle: #f8d7da;
        --bs-light-bg-subtle: #fcfcfd;
        --bs-dark-bg-subtle: #ced4da;
        --bs-primary-border-subtle: #9ec5fe;
        --bs-secondary-border-subtle: #c4c8cb;
        --bs-success-border-subtle: #a3cfbb;
        --bs-info-border-subtle: #9eeaf9;
        --bs-warning-border-subtle: #ffe69c;
        --bs-danger-border-subtle: #f1aeb5;
        --bs-light-border-subtle: #e9ecef;
        --bs-dark-border-subtle: #adb5bd;
        --bs-white-rgb: 255, 255, 255;
        --bs-black-rgb: 0, 0, 0;
        --bs-font-sans-serif: system-ui, -apple-system, "Segoe UI", Roboto,
            "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif,
            "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        --bs-font-monospace: SFMono-Regular, Menlo, Monaco, Consolas,
            "Liberation Mono", "Courier New", monospace;
        --bs-gradient: linear-gradient(
            180deg,
            rgba(255, 255, 255, 0.15),
            rgba(255, 255, 255, 0)
        );
        --bs-body-font-family: var(--bs-font-sans-serif);
        --bs-body-font-size: 1rem;
        --bs-body-font-weight: 400;
        --bs-body-line-height: 1.5;
        --bs-body-color: #212529;
        --bs-body-color-rgb: 33, 37, 41;
        --bs-body-bg: #fff;
        --bs-body-bg-rgb: 255, 255, 255;
        --bs-emphasis-color: #000;
        --bs-emphasis-color-rgb: 0, 0, 0;
        --bs-secondary-color: rgba(33, 37, 41, 0.75);
        --bs-secondary-color-rgb: 33, 37, 41;
        --bs-secondary-bg: #e9ecef;
        --bs-secondary-bg-rgb: 233, 236, 239;
        --bs-tertiary-color: rgba(33, 37, 41, 0.5);
        --bs-tertiary-color-rgb: 33, 37, 41;
        --bs-tertiary-bg: #f8f9fa;
        --bs-tertiary-bg-rgb: 248, 249, 250;
        --bs-heading-color: inherit;
        --bs-link-color: #0d6efd;
        --bs-link-color-rgb: 13, 110, 253;
        --bs-link-decoration: underline;
        --bs-link-hover-color: #0a58ca;
        --bs-link-hover-color-rgb: 10, 88, 202;
        --bs-code-color: #d63384;
        --bs-highlight-color: #212529;
        --bs-highlight-bg: #fff3cd;
        --bs-border-width: 1px;
        --bs-border-style: solid;
        --bs-border-color: #dee2e6;
        --bs-border-color-translucent: rgba(0, 0, 0, 0.175);
        --bs-border-radius: 0.375rem;
        --bs-border-radius-sm: 0.25rem;
        --bs-border-radius-lg: 0.5rem;
        --bs-border-radius-xl: 1rem;
        --bs-border-radius-xxl: 2rem;
        --bs-border-radius-2xl: var(--bs-border-radius-xxl);
        --bs-border-radius-pill: 50rem;
        --bs-box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        --bs-box-shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        --bs-box-shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        --bs-box-shadow-inset: inset 0 1px 2px rgba(0, 0, 0, 0.075);
        --bs-focus-ring-width: 0.25rem;
        --bs-focus-ring-opacity: 0.25;
        --bs-focus-ring-color: rgba(13, 110, 253, 0.25);
        --bs-form-valid-color: #198754;
        --bs-form-valid-border-color: #198754;
        --bs-form-invalid-color: #dc3545;
        --bs-form-invalid-border-color: #dc3545;
        }
        [data-bs-theme="dark"] {
        color-scheme: dark;
        --bs-body-color: #dee2e6;
        --bs-body-color-rgb: 222, 226, 230;
        --bs-body-bg: #212529;
        --bs-body-bg-rgb: 33, 37, 41;
        --bs-emphasis-color: #fff;
        --bs-emphasis-color-rgb: 255, 255, 255;
        --bs-secondary-color: rgba(222, 226, 230, 0.75);
        --bs-secondary-color-rgb: 222, 226, 230;
        --bs-secondary-bg: #343a40;
        --bs-secondary-bg-rgb: 52, 58, 64;
        --bs-tertiary-color: rgba(222, 226, 230, 0.5);
        --bs-tertiary-color-rgb: 222, 226, 230;
        --bs-tertiary-bg: #2b3035;
        --bs-tertiary-bg-rgb: 43, 48, 53;
        --bs-primary-text-emphasis: #6ea8fe;
        --bs-secondary-text-emphasis: #a7acb1;
        --bs-success-text-emphasis: #75b798;
        --bs-info-text-emphasis: #6edff6;
        --bs-warning-text-emphasis: #ffda6a;
        --bs-danger-text-emphasis: #ea868f;
        --bs-light-text-emphasis: #f8f9fa;
        --bs-dark-text-emphasis: #dee2e6;
        --bs-primary-bg-subtle: #031633;
        --bs-secondary-bg-subtle: #161719;
        --bs-success-bg-subtle: #051b11;
        --bs-info-bg-subtle: #032830;
        --bs-warning-bg-subtle: #332701;
        --bs-danger-bg-subtle: #2c0b0e;
        --bs-light-bg-subtle: #343a40;
        --bs-dark-bg-subtle: #1a1d20;
        --bs-primary-border-subtle: #084298;
        --bs-secondary-border-subtle: #41464b;
        --bs-success-border-subtle: #0f5132;
        --bs-info-border-subtle: #087990;
        --bs-warning-border-subtle: #997404;
        --bs-danger-border-subtle: #842029;
        --bs-light-border-subtle: #495057;
        --bs-dark-border-subtle: #343a40;
        --bs-heading-color: inherit;
        --bs-link-color: #6ea8fe;
        --bs-link-hover-color: #8bb9fe;
        --bs-link-color-rgb: 110, 168, 254;
        --bs-link-hover-color-rgb: 139, 185, 254;
        --bs-code-color: #e685b5;
        --bs-highlight-color: #dee2e6;
        --bs-highlight-bg: #664d03;
        --bs-border-color: #495057;
        --bs-border-color-translucent: rgba(255, 255, 255, 0.15);
        --bs-form-valid-color: #75b798;
        --bs-form-valid-border-color: #75b798;
        --bs-form-invalid-color: #ea868f;
        --bs-form-invalid-border-color: #ea868f;
        }
        #nav {
        --bs-nav-link-padding-x: 1rem;
        --bs-nav-link-padding-y: 0.5rem;
        --bs-nav-link-font-weight: ;
        --bs-nav-link-color: var(--bs-link-color);
        --bs-nav-link-hover-color: var(--bs-link-hover-color);
        --bs-nav-link-disabled-color: var(--bs-secondary-color);
        display: flex;
        flex-wrap: wrap;
        padding-left: 0;
        margin-bottom: 0;
        list-style: none;
        }
        #nav-fill #nav-item #nav-link,
        #nav-justified #nav-item #nav-link {
        width: 100%;
        }
        #nav #nav-link {
        display: block;
        padding: var(--bs-nav-link-padding-y) var(--bs-nav-link-padding-x);
        font-size: var(--bs-nav-link-font-size);
        font-weight: var(--bs-nav-link-font-weight);
        color: var(--bs-nav-link-color);
        text-decoration: none;
        background: 0 0;
        border: 0;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out,
            border-color 0.15s ease-in-out;
        }
        @media (prefers-reduced-motion: reduce) {
        #nav #nav-link {
            transition: none;
        }
        }
        #nav-tabs #nav-link {
        margin-bottom: calc(-1 * var(--bs-nav-tabs-border-width));
        border: var(--bs-nav-tabs-border-width) solid transparent;
        border-top-left-radius: var(--bs-nav-tabs-border-radius);
        border-top-right-radius: var(--bs-nav-tabs-border-radius);
        }
        #nav-pills #nav-link {
        border-radius: var(--bs-nav-pills-border-radius);
        }
        #nav-pills #nav-link.active,
        #nav-pills .show > #nav-link {
        color: var(--bs-nav-pills-link-active-color);
        background-color: var(--bs-nav-pills-link-active-bg);
        }
        #nav-underline #nav-link {
        padding-right: 0;
        padding-left: 0;
        border-bottom: var(--bs-nav-underline-border-width) solid transparent;
        }
        #nav-underline #nav-link.active,
        #nav-underline .show > #nav-link {
        font-weight: 700;
        color: var(--bs-nav-underline-link-active-color);
        border-bottom-color: currentcolor;
        }
        #nav-fill #nav-item,
        #nav-fill > #nav-link {
        flex: 1 1 auto;
        text-align: center;
        }
        #nav-justified #nav-item,
        #nav-justified > #nav-link {
        flex-grow: 1;
        flex-basis: 0;
        text-align: center;
        }
        #nav-fill #nav-item #nav-link,
        #nav-justified #nav-item #nav-link {
        width: 100%;
        }
        #navbar-expand-sm #navbar-nav #nav-link {
            padding-right: var(--bs-navbar-nav-link-padding-x);
            padding-left: var(--bs-navbar-nav-link-padding-x);
        }
        #navbar-expand-md #navbar-nav #nav-link {
            padding-right: var(--bs-navbar-nav-link-padding-x);
            padding-left: var(--bs-navbar-nav-link-padding-x);
        }
          #navbar-expand-lg #navbar-nav #nav-link {
            padding-right: var(--bs-navbar-nav-link-padding-x);
            padding-left: var(--bs-navbar-nav-link-padding-x);
        }
        #navbar-expand-xl #navbar-nav #nav-link {
            padding-right: var(--bs-navbar-nav-link-padding-x);
            padding-left: var(--bs-navbar-nav-link-padding-x);
        }
        #navbar-expand-xxl #navbar-nav #nav-link {
            padding-right: var(--bs-navbar-nav-link-padding-x);
            padding-left: var(--bs-navbar-nav-link-padding-x);
        }
        #navbar-expand .navbar-nav #nav-link {
        padding-right: var(--bs-navbar-nav-link-padding-x);
        padding-left: var(--bs-navbar-nav-link-padding-x);
        }
        #nav-tabs #nav-item.show #nav-link,
        #nav-tabs #nav-link.active {
        color: var(--bs-nav-tabs-link-active-color);
        background-color: var(--bs-nav-tabs-link-active-bg);
        border-color: var(--bs-nav-tabs-link-active-border-color);
        }
        #nav-fill #nav-item #nav-link,
        #nav-justified #nav-item #nav-link {
        width: 100%;
        }
        #navbar-nav #nav-link.active,
        #navbar-nav #nav-link.show {
        color: var(--bs-navbar-active-color);
        }
        #card-header-tabs #nav-link.active {
        background-color: var(--bs-card-bg);
        border-bottom-color: var(--bs-card-bg);
        }
        #nav-link.disabled,
        #nav-link:disabled {
        color: var(--bs-nav-link-disabled-color);
        pointer-events: none;
        cursor: default;
        }
        #navbar-nav #nav-link.active,
        #navbar-nav #nav-link.show {
        color: var(--bs-navbar-active-color);
        }
        #card-header-tabs #nav-link.active {
        background-color: var(--bs-card-bg);
        border-bottom-color: var(--bs-card-bg);
        }
    </style>
  </head>
  <body>
    <ul id="nav">
        <li id="nav-item">
        <a id="nav-link" href="index.php">Home</a>
      </li>
      <li id="nav-item">
        <a id="nav-link" href="login.php">Login</a>
      </li>
      <li id="nav-item">
        <a id="nav-link" href="register.php">Register</a>
      </li>
      <li id="nav-item">
        <a id="nav-link" href="profile.php">My Profile</a>
      </li>
    </ul>

    <div class="col-sm-3" style=" height: 200px;">
        <div class="col-xs-4 col-sm-12" style="height: 200px; text-align: center; align-content: center;">
            <img src="images/download.jpg" style="width: 150px; padding-top: 10px;" class="img-circle">
        </div>
        <div class="col-xs-8 col-sm-12" style=" height: 200px; align-content: center;">
            <h4 class="text-center" style="margin-bottom: 0px;">John Doe</h4>
            <h6 class="text-center" style="margin-top: 0px;">john.doe</h6>
            <div style=" vertical-align: middle; text-align: center;">
                <button> Follow</button>
                <button> Message </button>
                <button> Report </button>
            </div>
            
        </div>
    </div>
    <div class="col-sm-9">
        <div class="" style="background-color: rgb(234, 234, 234); padding: 10px; border-radius: 5px; margin-top: 10px; margin-bottom: 10px;">
            <h4>The Power of Awesome Ideas</h4>
            <p>Today I explored how small creative ideas can turn into truly awesome projects with just a bit of persistence.</p>
            <p>March 1, 2026</p>
        </div>
        <div class="" style="background-color: rgb(234, 234, 234); padding: 10px; border-radius: 5px; margin-top: 10px; margin-bottom: 10px;">
            <h4>The Power of Awesome Ideas</h4>
            <p>Today I explored how small creative ideas can turn into truly awesome projects with just a bit of persistence.</p>
            <p>March 1, 2026</p>
        </div>
        <div class="" style="background-color: rgb(234, 234, 234); padding: 10px; border-radius: 5px; margin-top: 10px; margin-bottom: 10px;">
            <h4>The Power of Awesome Ideas</h4>
            <p>Today I explored how small creative ideas can turn into truly awesome projects with just a bit of persistence.</p>
            <p>March 1, 2026</p>
        </div>
        <div class="" style="background-color: rgb(234, 234, 234); padding: 10px; border-radius: 5px; margin-top: 10px; margin-bottom: 10px;">
            <h4>The Power of Awesome Ideas</h4>
            <p>Today I explored how small creative ideas can turn into truly awesome projects with just a bit of persistence.</p>
            <p>March 1, 2026</p>
        </div>
    </div>
  </body>
</html>
