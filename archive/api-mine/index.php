<?php

include "vendor/autoload.php";

use EdSDK\FlmngrServer\FlmngrServer;


if (!isset($isConfiguredExternally) || $isConfiguredExternally !== TRUE) {

    // This script is auto configured when you use it from
    // the same directory where you store files.
    // But optionally you can relocate and rename it
    // (this is more secure).
    //
    // In this case specify variables above to set manual locations
    // if you did not just drop index.php into a directory with files
    // but store them separately.
    //
    // For example you have copied this script as:
    //   /var/www/example.com/scripts/fm.php
    // and files in:
    //   /var/www/example.com/public/
    // So your configuration will be:
    //
    //   $urlFileManager = '/scripts/fm.php';
    //   $urlFiles = '/public/';
    //   $dirFiles = '/var/www/example.com/public/';
    //
    // We recommend you to use absolute URLs and directory paths
    // wherever is is possible. So here both two URLs start from the root
    // of your website, and directory starts from the root of file system.

    $urlFileManager = null;
    $urlFiles = null;
    $dirFiles = null;


    // In case you wish to protect this page from unauthorized access (recommended)
    // please set the variables below and a server will ask user and password on load.
    // All file manager requests will be also password protected.

    $authUser = null;
    $authPassword = null;

}


function unauthorized() {
    header('WWW-Authenticate: Basic realm="Flmngr"');
    header('HTTP/1.0 401 Unauthorized');
}

if ($authUser != null && $authPassword != null) {
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        unauthorized();
        exit;
    } else {
        if ($_SERVER['PHP_AUTH_USER'] !== $authUser || $_SERVER['PHP_AUTH_PW'] !== $authPassword) {
            unauthorized();
            exit;
        }
    }
}

if ($urlFileManager == null || $dirFiles == null) {

    $urlFileManager = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $urlFiles = $urlFileManager;
    $i = strpos($urlFiles, "?");
    if ($i !== FALSE)
        $urlFiles = substr($urlFiles, 0, $i);
    if (strrpos($urlFileManager, ".php") === strlen($urlFileManager - 4)) {
        $i = strrpos($urlFiles, "/");
        if ($i !== FALSE)
            $urlFiles = substr($urlFiles, 0, $i + 1);
    }
    $dirFiles = dirName(__FILE__);
    $i = strrpos($dirFiles, "/");
    if ($i !== FALSE)
        $i = strrpos($dirFiles, "\\");
    if ($i !== FALSE)
        $dirFiles = ".." . DIRECTORY_SEPARATOR . substr($dirFiles, $i + 1);

}

function showIndex($urlFileManager, $urlFiles, $authUser, $authPassword) {
    ?><!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,minimum-scale=1.0"/>
            <title>Flmngr file manager</title>
            <link rel="icon" type="image/png" href="https://flmngr.com/img/favicons/favicon.png"/>
        </head>
        <body>

        <div style="width:100vw;height:100vh;top:0;left:0;position:absolute;display:flex;align-items:center;justify-content:center">
            <div style="display:flex;align-items:center">
                <img src="data:image/svg+xml,%3C%3Fxml version='1.0' encoding='UTF-8' standalone='no'%3F%3E%3Csvg xmlns:svg='http://www.w3.org/2000/svg' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' version='1.0' width='16px' height='16px' viewBox='0 0 128 128' xml:space='preserve'%3E%3Cg%3E%3Cpath d='M75.4 126.63a11.43 11.43 0 0 1-2.1-22.65 40.9 40.9 0 0 0 30.5-30.6 11.4 11.4 0 1 1 22.27 4.87h.02a63.77 63.77 0 0 1-47.8 48.05v-.02a11.38 11.38 0 0 1-2.93.37z' fill='%23007fff'/%3E%3CanimateTransform attributeName='transform' type='rotate' from='0 64 64' to='360 64 64' dur='800ms' repeatCount='indefinite'%3E%3C/animateTransform%3E%3C/g%3E%3C/svg%3E"/>
                <span style="margin-left:15px">Loading...</span>
            </div>
        </div>

        <script>
            window.onFlmngrAndImgPenLoaded = function() {
                var flmngr = window.flmngr.create({
                    integration: 'indexphp-flmngr',
                    isMaximized: true,
                    showCloseButton: false,
                    showMaximizeButton: false,

                    urlFileManager: '<?php echo $urlFileManager ?>',
                    urlFileManager__user: '<?php echo $authUser ?>',
                    urlFileManager__password: '<?php echo $authPassword ?>',
                    urlFiles: '<?php echo $urlFiles ?>',
                    <?php
                        if ($authUser != null && $authPassword != null) {
                    ?>
                    <?php
                        }
                    ?>
                    defaultUploadDir: '/',

                    hideFiles: [
                        "index.php",
                        ".htaccess",
                        ".htpasswd"
                    ],
                    hideDirs: [
                        "vendor"
                    ],
                    imgPen: window.imgpen.create({})
                });
                flmngr.browse({
                    isMultiple: null,
                    acceptExtensions: ["png", "jpeg", "jpg", "svg", "webp", "bmp", "gif"],
                });
            }
        </script>
        <script src="https://cloud.n1ed.com/cdn/FLMNINDX/n1flmngr.js"></script>
        <script src="https://cloud.n1ed.com/cdn/FLMNINDX/n1imgpen.js"></script>

        </body>
        </html>
    <?php
}

function callFlmngr($dirFiles) {

    FlmngrServer::flmngrRequest(
        array(
            'dirFiles' => $dirFiles
        )
    );
}

if (count($_POST) === 0)
    showIndex($urlFileManager, $urlFiles, $authUser, $authPassword);
else
    callFlmngr($dirFiles);