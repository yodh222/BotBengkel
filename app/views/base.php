<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Auto Car | <?= isset($title) ? $title : '' ?></title>

    <link rel="stylesheet" href="/assets/css/bootstrap.css">
    <link rel="stylesheet" href="/assets/css/dataTables.bootstrap5.css">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0" />

    <script src="/assets/js/jquery-3.7.1.min.js"></script>
    <script src="/assets/js/masonry.pkgd.min.js"></script>
    <script src="/assets/js/imagesloaded.pkgd.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/perfect-scrollbar.min.js"></script>
    <script src="/assets/js/smooth-scrollbar.min.js"></script>
    <script src="/assets/js/dataTables.js"></script>
    <script src="/assets/js/dataTables.bootstrap5.js"></script>
    <?= isset($scripts) ? $scripts : '' ?>

</head>

<body class="g-sidenav-show bg-gray-100">
    <div class="min-height-300 bg-dark position-absolute w-100"></div>
    <aside
        class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4"
        id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
                aria-hidden="true" id="iconSidenav"></i>
            <div class="navbar-brand m-0">
                <?php // Gambar logo dapat dimasukkan jika diperlukan 
                ?>
                <span class="ms-1 font-weight-bold">R.auto Car</span>
            </div>
        </div>
        <hr class="horizontal dark mt-0" />
        <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <?php /* Contoh menu; komentar Blade dihapus atau dikonversi ke komentar PHP */ ?>
                <li class="nav-item">
                    <a class="nav-link <?= (isset($currentRoute) && $currentRoute == '') ? 'active' : '' ?>" href="/">
                        <span
                            class="material-symbols-rounded border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            send
                        </span>
                        <span class="nav-link-text ms-1">Messages Management</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (isset($currentRoute) && $currentRoute == 'log-messages') ? 'active' : '' ?>"
                        href="/log-messages">
                        <span
                            class="material-symbols-rounded border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            library_books
                        </span>
                        <span class="nav-link-text ms-1">Log Message</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (isset($currentRoute) && $currentRoute == 'bot-settings') ? 'active' : '' ?>"
                        href="/bot-settings">
                        <span
                            class="material-symbols-rounded border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            precision_manufacturing
                        </span>
                        <span class="nav-link-text ms-1">Bot Settings</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>
    <main class="main-content position-relative border-radius-lg">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur"
            data-scroll="false">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                        <li class="breadcrumb-item text-sm">
                            <a class="opacity-5 text-white" href="javascript:;">Pages</a>
                        </li>
                        <li class="breadcrumb-item text-sm text-white active" aria-current="page">
                            <?= isset($title) ? $title : '' ?>
                        </li>
                    </ol>
                    <h6 class="font-weight-bolder text-white mb-0">
                        <?= isset($title) ? $title : '' ?>
                    </h6>
                </nav>
            </div>
        </nav>
        <div class="px-0 mx-4 mt-4 shadow-none border-radius-xl">
            <div class="container-fluid py-1 px-3">
                <?= isset($content) ? $content : '' ?>
            </div>
        </div>
    </main>
</body>

</html>