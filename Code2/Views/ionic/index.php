<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Электронная очередь</title>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <!-- ionic css -->
    <link href="/Views/ionic/css/ionic.min.css" rel="stylesheet">
    <link href="/Views/ionic/css/themes/ionic-ios7.min.css" rel="stylesheet">

    <!-- your app's css -->
    <link href="/Views/ionic/css/app.css" rel="stylesheet">

    <!-- angularjs scripts -->
    <script src="/Views/ionic/js/vendor/ionic/ionic.min.js"></script>
    <script src="/Views/ionic/js/vendor/angular/angular.js"></script>
    <script src="/Views/ionic/js/vendor/angular/angular-animate.min.js"></script>
    <script src="/Views/ionic/js/vendor/angular/angular-resource.min.js"></script>
    <script src="/Views/ionic/js/vendor/angular/angular-sanitize.min.js"></script>
    <script src="/Views/ionic/js/vendor/angular/angular-ui-router.min.js"></script>
    <script src="/Views/ionic/js/vendor/angular/angular-fullscreen.min.js"></script>
    <script src="/Views/ionic/js/vendor/angular/angular-route.min.js"></script>
    <script src="/Views/ionic/js/vendor/angular/satellizer.js"></script>
    <script src="/Views/ionic/js/vendor/angular/angular-media-player.min.js"></script>
    <script src="/Views/ionic/js/vendor/ionic/ionic-angular.min.js"></script>

    <!-- cordova script -->
    <!--script src="cordova.js"></script-->

    <!-- your app's script -->
    <script src="/Views/ionic/js/app.js"></script>
    <script src="/Views/ionic/js/services.js"></script>
    <script src="/Views/ionic/js/controllers.js"></script>
    <script src="/Views/ionic/js/directives.js"></script>
</head>

<!-- 'sideMenuApp' is the name of this angular module (js/app.js)-->
<body ng-app="sideMenuApp">
    <pane nav-router animation="slide-left-right-ios7">
        <ion-nav-view></ion-nav-view>
    </pane>
</body>
</html>
