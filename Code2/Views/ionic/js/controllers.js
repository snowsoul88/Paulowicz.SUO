angular.module('sideMenuApp.controllers', ['FBAngular', 'ionic'])

    .controller('AppController', function ($scope, $rootScope, $location, $window, $auth, $ionicSideMenuDelegate, $ionicNavBarDelegate, MenuService) {
        var render_menu = function () {
            MenuService.all().success(
                function(data) {
                    $rootScope.list = data;
                }
            );
        }
        $scope.goTo = function(page, target) {
            if (target == 'self') {
                $ionicSideMenuDelegate.toggleLeft();
                $location.url('/' + page);
            } else if (target == '_blank') {
                $window.open('/#/' + page);
            }
        }
        $scope.$on('$viewContentLoaded', function(){
            render_menu();
        });
    })

    .controller('TicketController', function ($scope, $http, $stateParams, $ionicPopover, $ionicModal, $ionicNavBarDelegate, Fullscreen, AdminService) {
        $scope.getPreviousTitle = function() {
            return $ionicNavBarDelegate.getPreviousTitle();
        }
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.ticket.add.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal_ticket_add = modal;
        });
        // $ionicPopover.fromTemplateUrl('/Views/ionic/templates/admin.ticket.popover.html', function(popover) {
        //     $scope.popover = popover;
        // });
        var show_ticket = function (ticketId) {
            AdminService.admin_tickets_ticket(ticketId).success(
                function(data) {
                    $scope.admin_tickets_ticket = data;
                }
            );
        }
        $scope.data = {
            showDelete: false,
            showReorder: false
        }
        $scope.admin_tickets_priority = function(product, fromIndex, toIndex, ticketId) {
            $scope.admin_tickets_ticket.products.splice(fromIndex, 1);
            $scope.admin_tickets_ticket.products.splice(toIndex, 0, product);
            var data = {};
            for (var i = 0; i < $scope.admin_tickets_ticket.products.length; i++) {
                AdminService.admin_tickets_priority(ticketId, $scope.admin_tickets_ticket.products[i].product_id, i).success(
                    function(data) {
                        $scope.admin_tickets_priority_changed = true;
                    }
                );
            }
        }
        $scope.admin_tickets_cancel = function (item, ticketId, productId) {
            AdminService.admin_tickets_cancel(ticketId, productId).success(
                function(data) {
                    $scope.admin_tickets_ticket.products.splice(item, 1);
                }
            );
        }
        $scope.ticket_add = function (action_type) {
            $scope.button_add_action_type = action_type;
            $scope.modal_ticket_add.show();
            AdminService.config_products().success(
                function(data) {
                    $scope.config_products_data = [];
                    for (var i = 0; i < data.length; i++) {
                        $scope.config_products_data[i] = {
                            product_id:data[i].product_id,
                            product_name:data[i].product_name,
                            enabled: false
                        }
                    }
                }
            );
        }
        $scope.ticket_add_products = function () {
            for (var i = 0; i < $scope.config_products_data.length; i++) {
                if ($scope.config_products_data[i].enabled == true) {
                    AdminService.admin_tickets_add_product($scope.admin_tickets_ticket.ticket_id, $scope.config_products_data[i].product_id).success(
                        function(data) {
                            $scope.modal_ticket_add.hide();
                            $scope.admin_tickets_ticket = data;
                        }
                    );
                }
            }
        }
        $scope.ticket_new_products = function () {
            var params = {};
            params['product_id'] = {};
            for (var i = 0; i < $scope.config_products_data.length; i++) {
                if ($scope.config_products_data[i].enabled == true) {
                    params['product_id'][i] = $scope.config_products_data[i].product_id;
                }
            }
            AdminService.admin_tickets_new(params).success(
                function(data) {
                    $scope.admin_tickets.splice(0, 0, data);
                    $scope.modal_ticket_add.hide();
                }
            );
        }
        $scope.button_add_action = function () {
            if ($scope.button_add_action_type == 'ADD') {
                $scope.ticket_add_products();
            } else if ($scope.button_add_action_type == 'NEW') {
                $scope.ticket_new_products();
            }
        }
        
        $scope.$on('$viewContentLoaded', function(){
            show_ticket($stateParams.ticketId);
        });
    })

    .controller('AdminController', function ($scope, $http, $stateParams, $ionicPopover, $ionicModal, $interval, Fullscreen, AdminService) {
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.ticket.add.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal_ticket_add = modal;
        });
        $scope.ticket_add = function (action_type) {
            $scope.button_add_action_type = action_type;
            $scope.modal_ticket_add.show();
            AdminService.config_products().success(
                function(data) {
                    $scope.config_products_data = [];
                    for (var i = 0; i < data.length; i++) {
                        $scope.config_products_data[i] = {
                            product_id:data[i].product_id,
                            product_name:data[i].product_name,
                            enabled: false
                        }
                    }
                }
            );
        }
        $scope.ticket_add_products = function () {
            for (var i = 0; i < $scope.config_products_data.length; i++) {
                if ($scope.config_products_data[i].enabled == true) {
                    AdminService.admin_tickets_add_product($scope.admin_tickets_ticket.ticket_id, $scope.config_products_data[i].product_id).success(
                        function(data) {
                            $scope.modal_ticket_add.hide();
                            $scope.admin_tickets_ticket = data;
                        }
                    );
                }
            }
        }
        $scope.ticket_new_products = function () {
            var params = {};
            params['product_id'] = {};
            for (var i = 0; i < $scope.config_products_data.length; i++) {
                if ($scope.config_products_data[i].enabled == true) {
                    params['product_id'][i] = $scope.config_products_data[i].product_id;
                }
            }
            AdminService.admin_tickets_new(params).success(
                function(data) {
                    $scope.admin_tickets.splice(0, 0, data);
                    $scope.modal_ticket_add.hide();
                }
            );
        }
        $scope.button_add_action = function () {
            if ($scope.button_add_action_type == 'ADD') {
                $scope.ticket_add_products();
            } else if ($scope.button_add_action_type == 'NEW') {
                $scope.ticket_new_products();
            }
        }
        $scope.format_time = function (unix_timestamp) {
            var date = new Date(unix_timestamp*1000);
            var hours = date.getHours();
            var minutes = "0" + date.getMinutes();
            var seconds = "0" + date.getSeconds();
            var formattedTime = hours + ':' + minutes.substr(minutes.length-2) + ':' + seconds.substr(seconds.length-2);
            return formattedTime;
        }
        $scope.ticket_class = function (action_id) {
            var ticket_class = '';
            switch (action_id) {
                case 'CANC':
                    ticket_class = 'item-stable';
                    break;
                case 'WAIT':
                    ticket_class = 'item-positive';
                    break;
            }
            return ticket_class;
        }
        var admin_tickets = function () {
            AdminService.admin_tickets().success(
                function(data) {
                    $scope.admin_tickets = data;
                }
            );
        }
        var timer = $interval(function() {
            admin_tickets();
        }, 30000);
        $scope.$on('$viewContentLoaded', function(){
            admin_tickets();
        });
    })

    .controller('UsersController', function ($scope, $http, $stateParams, $ionicLoading, $ionicModal, AdminService) {
        var ionicLoadingShow = function() {
            $ionicLoading.show({
                template: 'Загрузка...'
            });
        }
        var ionicLoadingHide = function(){
            $ionicLoading.hide();
        }
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.user.add.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal_user_add = modal;
        });
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.users.services.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal_bind_products = modal;
        });
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.users.situations.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal_bind_situations = modal;
        });
        $scope.list = {
            showDelete: false
        }
        $scope.add_bindings = function () {
            ionicLoadingShow();
            var products = [];
            for (var i = 0; i < $scope.config_products_data.length; i++) {
                if ($scope.config_products_data[i].enabled == true) {
                    products.splice(0, 0, $scope.config_products_data[i].product_id);
                }
            }
            AdminService.add_up_binding($scope.user_id, products).success(
                function(data) {
                    $scope.user_id = null;
                    $scope.modal_bind_products.hide();
                    ionicLoadingHide();
                }
            );
        }
        $scope.add_binded_situations = function () {
            ionicLoadingShow();
            var binded_situations = [];
            for (var i = 0; i < $scope.situations.length; i++) {
                if ($scope.situations[i].enabled == true) {
                    binded_situations.splice(0, 0, $scope.situations[i].situation_id);
                }
            }
            AdminService.add_us_binding($scope.user_id, binded_situations).success(
                function(data) {
                    $scope.user_id = null;
                    $scope.modal_bind_situations.hide();
                    ionicLoadingHide();
                }
            );
        }
        $scope.bind_products = function (user_id) {
            ionicLoadingShow();
            $scope.user_id = user_id;
            AdminService.user_binded_products(user_id).success(
                function(data) {
                    $scope.config_products_data = [];
                    for (var i = 0; i < data.length; i++) {
                        $scope.config_products_data[i] = {
                            product_id:data[i].product_id,
                            product_name:data[i].product_name,
                            enabled:data[i].product_enabled
                        }
                    }
                    $scope.modal_bind_products.show();
                    ionicLoadingHide();
                }
            );
        }
        $scope.bind_situations = function (user_id) {
            ionicLoadingShow();
            $scope.user_id = user_id;
            AdminService.user_binded_situations(user_id).success(
                function(data) {
                    $scope.situations = [];
                    for (var i = 0; i < data.length; i++) {
                        $scope.situations[i] = {
                            situation_id:data[i].situation_id,
                            situation_name:data[i].situation_name,
                            enabled:data[i].situation_enabled
                        }
                    }
                    $scope.modal_bind_situations.show();
                    ionicLoadingHide();
                }
            );
        }
        $scope.delete = function (item, uid) {
            ionicLoadingShow();
            AdminService.admin_user_delete(uid).success(
                function(data) {
                    $scope.admin_users.splice(item, 1);
                    ionicLoadingHide();
                }
            );
        }
        var admin_users = function () {
            ionicLoadingShow();
            AdminService.admin_users().success(
                function(data) {
                    $scope.admin_users = data;
                    ionicLoadingHide();
                }
            );
        }();
        $scope.adata = {
            login: $scope.login,
            password: $scope.password,
            sname: $scope.sname,
            fname: $scope.fname,
            mname: $scope.mname,
            role: $scope.role,
        };
        console.log($scope.adata);
        $scope.admin_user_add = function () {
            ionicLoadingShow();
            AdminService.admin_user_add($scope.adata).success(
                function(data) {
                    $scope.admin_users.splice(0, 0, data);
                    $scope.modal_user_add.hide();
                    ionicLoadingHide();
                }
            );
        }
    })

    .controller('WorkplacesController', function ($scope, $http, $stateParams, $ionicLoading, $ionicModal, AdminService, ConfigService) {
        var ionicLoadingShow = function() {
            $ionicLoading.show({
                template: 'Загрузка...'
            });
        }
        var ionicLoadingHide = function(){
            $ionicLoading.hide();
        }
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.wp.add.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal_wp_add = modal;
        });
        $scope.list = {
            showDelete: false
        }
        $scope.delete = function (item, wid) {
            AdminService.admin_workplace_cancel(wid).success(
                function(data) {
                    $scope.admin_workplaces.splice(item, 1);
                }
            );
        }
        var admin_workplaces = function () {
            ionicLoadingShow();
            AdminService.admin_workplaces().success(
                function(data) {
                    $scope.admin_workplaces = data;
                    ionicLoadingHide();
                }
            );
        }();
        var config_clients = function () {
            ConfigService.config_clients().success(
                function(data) {
                    $scope.config_clients = data;
                }
            );
        }();
        var config_wp_types = function () {
            ConfigService.config_wp_types().success(
                function(data) {
                    $scope.config_wp_types = data;
                }
            );
        }();
        $scope.adata = {
            workplace_number: $scope.workplace_number,
            workplace_name: $scope.workplace_name,
            workplace_type: $scope.workplace_type,
            cid: $scope.cid
        };
        $scope.admin_wp_add = function () {
            ionicLoadingShow();
            AdminService.admin_wp_add($scope.adata).success(
                function(data) {
                    $scope.admin_workplaces = data;
                    $scope.modal_wp_add.hide();
                    ionicLoadingHide();
                }
            );
        }
    })

    .controller('ClientsController', function ($scope, $http, $stateParams, $ionicLoading, $ionicModal, AdminService, ConfigService) {
        var ionicLoadingShow = function() {
            $ionicLoading.show({
                template: 'Загрузка...'
            });
        }
        var ionicLoadingHide = function(){
            $ionicLoading.hide();
        }
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.cl.add.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal_cl_add = modal;
        });
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.clients.services.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal_bind_products = modal;
        });
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.clients.situations.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal_bind_situations = modal;
        });
        $scope.list = {
            showDelete: false
        }
        $scope.add_bindings = function () {
            ionicLoadingShow();
            var products = [];
            for (var i = 0; i < $scope.config_products_data.length; i++) {
                if ($scope.config_products_data[i].enabled == true) {
                    products.splice(0, 0, $scope.config_products_data[i].product_id);
                }
            }
            AdminService.add_cp_binding($scope.cid, products).success(
                function(data) {
                    $scope.cid = null;
                    $scope.modal_bind_products.hide();
                    ionicLoadingHide();
                }
            );
        }
        $scope.add_binded_situations = function () {
            ionicLoadingShow();
            var binded_situations = [];
            for (var i = 0; i < $scope.situations.length; i++) {
                if ($scope.situations[i].enabled == true) {
                    binded_situations.splice(0, 0, $scope.situations[i].situation_id);
                }
            }
            AdminService.add_cs_binding($scope.cid, binded_situations).success(
                function(data) {
                    $scope.cid = null;
                    $scope.modal_bind_situations.hide();
                    ionicLoadingHide();
                }
            );
        }
        $scope.bind_products = function (cid) {
            ionicLoadingShow();
            $scope.cid = cid;
            AdminService.client_binded_products(cid).success(
                function(data) {
                    $scope.config_products_data = [];
                    for (var i = 0; i < data.length; i++) {
                        $scope.config_products_data[i] = {
                            product_id:data[i].product_id,
                            product_name:data[i].product_name,
                            enabled:data[i].product_enabled
                        }
                    }
                    $scope.modal_bind_products.show();
                    ionicLoadingHide();
                }
            );
        }
        $scope.bind_situations = function (cid) {
            ionicLoadingShow();
            $scope.cid = cid;
            AdminService.client_binded_situations(cid).success(
                function(data) {
                    $scope.situations = [];
                    for (var i = 0; i < data.length; i++) {
                        $scope.situations[i] = {
                            situation_id:data[i].situation_id,
                            situation_name:data[i].situation_name,
                            enabled:data[i].situation_enabled
                        }
                    }
                    $scope.modal_bind_situations.show();
                    ionicLoadingHide();
                }
            );
        }
        $scope.delete = function (item, id) {
            ionicLoadingShow();
            AdminService.admin_client_delete(id).success(
                function(data) {
                    $scope.config_clients.splice(item, 1);
                    ionicLoadingHide();
                }
            );
        }
        var config_clients = function () {
            ionicLoadingShow();
            ConfigService.config_clients().success(
                function(data) {
                    $scope.config_clients = data;
                    ionicLoadingHide();
                }
            );
        }();
        $scope.adata = {
            client_id: $scope.client_id,
            role: $scope.role,
            REMOTE_ADDR: $scope.REMOTE_ADDR,
            HTTP_X_FORWARDED_FOR: $scope.HTTP_X_FORWARDED_FOR
        };
        $scope.admin_cl_add = function () {
            ionicLoadingShow();
            AdminService.admin_cl_add($scope.adata).success(
                function(data) {
                    $scope.config_clients = data;
                    $scope.modal_cl_add.hide();
                    ionicLoadingHide();
                }
            );
        }
    })

    .controller('SheduleController', function ($scope, $http, $stateParams, $ionicLoading, $ionicModal, AdminService, ConfigService) {
        var ionicLoadingShow = function() {
            $ionicLoading.show({
                template: 'Загрузка...'
            });
        }
        var ionicLoadingHide = function(){
            $ionicLoading.hide();
        }
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.ts.add.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal_ts_add = modal;
        });
        $scope.list = {
            showDelete: false
        }
        $scope.delete = function (item, id) {
            ionicLoadingShow();
            AdminService.admin_shedule_delete(id).success(
                function(data) {
                    $scope.shedule_types.splice(item, 1);
                    ionicLoadingHide();
                }
            );
        }
        $scope.change = function (id) {
            ionicLoadingShow();
            AdminService.admin_shedule_delete(id).success(
                function(data) {
                    $scope.shedule_types.splice(item, 1);
                    ionicLoadingHide();
                }
            );
        }
        var config_shedule = function() {
            ConfigService.config_shedule().success(
                function(data) {
                    $scope.shedule_types = data;
                }
            );
        }();
        $scope.adata = {
            type_name: $scope.type_name,
            type_description: $scope.type_description,
            type_operable: $scope.type_operable
        };
        $scope.admin_ts_add = function () {
            ionicLoadingShow();
            AdminService.admin_ts_add($scope.adata).success(
                function(data) {
                    $scope.shedule_types = data;
                    $scope.modal_ts_add.hide();
                    ionicLoadingHide();
                }
            );
        }
    })

    .controller('KioskController', function ($scope, $rootScope, $location, $window, $auth, $ionicModal, $ionicNavBarDelegate, $ionicLoading, $timeout, BookingService, Fullscreen, QZPrint) {
        if (!Fullscreen.isEnabled()) { Fullscreen.all(); }
        var ionicLoadingShow = function() {
            $ionicLoading.show({
                template: 'Загрузка...'
            });
        }
        var ionicLoadingHide = function(){
            $ionicLoading.hide();
        }
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/kiosk.ticket.html', {
            scope: $scope,
            animation: 'fade-in-out'
        }).then(function(modal) {
            $scope.modal = modal;
        });
        var load_booking_schedule = function () {
            BookingService.load_booking_schedule().success(
                function(data) {
                    $rootScope.booking_schedule = data;
                    var data_length = Object.keys(data).length;
                    if (data_length != 0) {
                        for (var i = 0; i < data_length; i++) {
                            var key = Object.keys(data)[i];
                        }
                    }
                }
            );
        }
        $rootScope.print_ticket = function (sid) {
            console.log(sid);
            ionicLoadingShow();
            var params = {
                situation_id:sid
            }
            BookingService.booking_tickets_new(params).success(
                function(data) {
                    ionicLoadingHide();
                    $scope.ticket_content = data;
                    $scope.modal.show();
                    $timeout( function() {
                        $scope.modal.hide();
                    }, 3000);
                    QZPrint.printHTML(data);
                }
            );
        }
        var load_booking_menu = function (sid) {
            BookingService.load_booking_menu(sid).success(
                function(data) {
                    for (var d = 0; d < data.length; d++) {
                        for (var i = 0; i < data[d].products.length; i++) {
                            data[d].products[i].enabled = false;
                        }
                    }
                    $rootScope.booking_menu = data;
                }
            );
        }
        var load_booking_product = function (pid) {
            BookingService.load_booking_product(pid).success(
                function(data) {
                    $rootScope.product_info = data;
                }
            );
        }
        $scope.$on('$viewContentLoaded', function(){
            load_booking_schedule();
            load_booking_menu(null);
            if (!Fullscreen.isEnabled()) {
                Fullscreen.all();
            }
        });
    })

    .controller('BookingController', function ($scope, $rootScope, $http, $stateParams, $ionicLoading, Fullscreen) {
        $scope = $rootScope;
        if (!Fullscreen.isEnabled()) { Fullscreen.all(); }
        $scope.$on('$viewContentLoaded', function(){

        });
    })

    .controller('BookingSubmitController', function ($scope, $rootScope, $http, $timeout, $stateParams, $ionicModal, $ionicLoading, $location, Fullscreen, QZPrint, BookingService) {
        if (!Fullscreen.isEnabled()) { Fullscreen.all(); }
        var ionicLoadingShow = function() {
            $ionicLoading.show({
                template: 'Загрузка...'
            });
        }
        var ionicLoadingHide = function(){
            $ionicLoading.hide();
        }
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/kiosk.ticket.html', {
            scope: $scope,
            animation: 'fade-in-out'
        }).then(function(modal) {
            $scope.modal = modal;
        });
        $scope.adata = {
            pincode: $scope.pincode
        };
        $scope.ticket_data = {};
        $scope.ticket_data.ticket_exists = false;
        $scope.adata.pincode = '';
        $scope.write = function(sym) {
            $scope.adata.pincode += sym;
            if ($scope.adata.pincode.length == 6) {
                check_ticket();
            } else {
                $scope.ticket_data = {};
                $scope.ticket_data.ticket_exists = false;
            }
        }
        $scope.backspace = function() {
            $scope.adata.pincode = $scope.adata.pincode.slice(0, - 1);
            if ($scope.adata.pincode.length == 6) {
                check_ticket();
            } else {
                $scope.ticket_data = {};
                $scope.ticket_data.ticket_exists = false;
            }
        }
        $scope.print_ticket = function () {
            ionicLoadingShow();
            BookingService.booking_tickets_submit($scope.adata.pincode).success(
                function(data) {
                    ionicLoadingHide();
                    $scope.ticket_content = data;
                    $scope.modal.show();
                    $timeout( function() {
                        $scope.modal.hide();
                    }, 3000);
                    $location.url('/kiosk/booking');
                    QZPrint.printHTML(data);
                }
            );
        }
        var check_ticket = function () {
            BookingService.booking_tickets_check($scope.adata.pincode).success(
                function(data) {
                    $scope.ticket_data = data;
                }
            );
        }
        $scope.$on('$viewContentLoaded', function(){
            $ionicLoading.hide();
        });
    })

    .controller('BookingSheduleController', function ($scope, $rootScope, $http, $timeout, $stateParams, $ionicModal, $ionicLoading, $location, Fullscreen, QZPrint, BookingService) {
        if (!Fullscreen.isEnabled()) { Fullscreen.all(); }
        var ionicLoadingShow = function() {
            $ionicLoading.show({
                template: 'Загрузка...'
            });
        }
        var ionicLoadingHide = function(){
            $ionicLoading.hide();
        }
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/kiosk.ticket.html', {
            scope: $scope,
            animation: 'fade-in-out'
        }).then(function(modal) {
            $scope.modal = modal;
        });
        $scope.selected_products = angular.fromJson($stateParams.selected_products);
        $scope.servcount = Object.keys($scope.selected_products['product_id']).length;
        $scope.shedule_date = '';
        $scope.shedule_hours = '';
        $scope.shedule_minutes = '';
        var schedule = function() {
            ionicLoadingShow();
            BookingService.load_schedule($scope.selected_products).success(
                function(data) {
                    console.log(data);
                    $scope.schedule = data;
                    $scope.shedule_date = data[0]['date'];
                    $scope.shedule_hours = data[0]['time'][0]['hour'];
                    $scope.shedule_minutes = data[0]['time'][0]['minutes'][0];
                    $scope.adata.not_before = $scope.schedule[0]['d'] + '.' + $scope.schedule[0]['m'] + '.' + $scope.schedule[0]['y'] + ' ' + $scope.schedule[0]['time'][0]['hour'] + ':' + $scope.schedule[0]['time'][0]['minutes'][0];
                    ionicLoadingHide();
                }
            );
        }();
        var date_i = 0;
        var hours_i = 0;
        var minutes_i = 0;
        $scope.date = function(act) {
            if (date_i+act < $scope.schedule.length && date_i+act > -1) {
                date_i += act;
                $scope.shedule_date = $scope.schedule[date_i]['date'];
                $scope.shedule_hours = $scope.schedule[date_i]['time'][0]['hour'];
                $scope.shedule_minutes = $scope.schedule[date_i]['time'][0]['minutes'][0];
                hours_i = 0;
                minutes_i = 0;
                $scope.adata.not_before = $scope.schedule[date_i]['d'] + '.' + $scope.schedule[date_i]['m'] + '.' + $scope.schedule[date_i]['y'] + ' ' + $scope.schedule[date_i]['time'][0]['hour'] + ':' + $scope.schedule[date_i]['time'][0]['minutes'][0];
            }
        }
        $scope.hours = function(act) {
            if (hours_i+act < $scope.schedule[date_i]['time'].length && hours_i+act > -1) {
                hours_i += act;
                $scope.shedule_hours = $scope.schedule[date_i]['time'][hours_i]['hour'];
                $scope.shedule_minutes = $scope.schedule[date_i]['time'][hours_i]['minutes'][0];
                minutes_i = 0;
                $scope.adata.not_before = $scope.schedule[date_i]['d'] + '.' + $scope.schedule[date_i]['m'] + '.' + $scope.schedule[date_i]['y'] + ' ' + $scope.schedule[date_i]['time'][hours_i]['hour'] + ':' + $scope.schedule[date_i]['time'][hours_i]['minutes'][0];
            }
        }
        $scope.minutes = function(act) {
            if (minutes_i+act < $scope.schedule[date_i]['time'][hours_i]['minutes'].length && minutes_i+act > -1) {
                minutes_i += act;
                $scope.shedule_minutes = $scope.schedule[date_i]['time'][hours_i]['minutes'][minutes_i];
                $scope.adata.not_before = $scope.schedule[date_i]['d'] + '.' + $scope.schedule[date_i]['m'] + '.' + $scope.schedule[date_i]['y'] + ' ' + $scope.schedule[date_i]['time'][hours_i]['hour'] + ':' + $scope.schedule[date_i]['time'][hours_i]['minutes'][minutes_i];
            }
        }
        $scope.adata = {
            guest_name: $scope.guest_name,
            not_before: $scope.not_before
        };
        $scope.adata.guest_name = '';
        $scope.write = function(sym) {
            $scope.adata.guest_name += sym;
        }
        $scope.backspace = function() {
            $scope.adata.guest_name = $scope.adata.guest_name.slice(0, - 1);
        }
        $scope.print_ticket = function () {
            ionicLoadingShow();
            var params = {};
            params['product_id'] = $scope.selected_products['product_id'];
            params['situation_id'] = $scope.selected_products['situation_id'];
            params['visitor'] = $scope.adata.guest_name;
            params['not_before'] = $scope.adata.not_before;
            BookingService.booking_tickets_new(params).success(
                function(data) {
                    ionicLoadingHide();
                    $scope.ticket_content = data;
                    $scope.modal.show();
                    $timeout( function() {
                        $scope.modal.hide();
                    }, 3000);
                    $location.url('/kiosk/booking');
                    QZPrint.printHTML(data);
                }
            );
        }
        $scope.$on('$viewContentLoaded', function(){
            $ionicLoading.hide();
        });
    })

    .controller('BookingSituationController', function ($window, $scope, $rootScope, $timeout, $http, $stateParams, $location, $ionicLoading, $ionicModal, Fullscreen, QZPrint, BookingService) {
        if (!Fullscreen.isEnabled()) { Fullscreen.all(); }
        var ionicLoadingShow = function() {
            $ionicLoading.show({
                template: 'Загрузка...'
            });
        }
        var ionicLoadingHide = function(){
            $ionicLoading.hide();
        }
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/kiosk.ticket.html', {
            scope: $scope,
            animation: 'fade-in-out'
        }).then(function(modal) {
            $scope.modal = modal;
        });
        $scope.print_ticket = function () {
            ionicLoadingShow();
            var params = {};
            params['product_id'] = {};
            params['situation_id'] = $stateParams.sid;
            for (var d = 0; d < $scope.booking_menu.length; d++) {
                for (var i = 0; i < $scope.booking_menu[d].products.length; i++) {
                    if ($scope.booking_menu[d].products[i].enabled == true) {
                        params['product_id'][i] = $scope.booking_menu[d].products[i].product_id;
                    }
                }
            }
            BookingService.booking_tickets_new(params).success(
                function(data) {
                    ionicLoadingHide();
                    $scope.ticket_content = data;
                    $scope.modal.show();
                    $timeout( function() {
                        $scope.modal.hide();
                    }, 3000);
                    $location.url('/kiosk/booking');
                    QZPrint.printHTML(data);
                }
            );
        }
        $scope.booking_shedule = function () {
            ionicLoadingShow();
            var params = {};
            params['product_id'] = {};
            params['situation_id'] = $stateParams.sid;
            for (var d = 0; d < $scope.booking_menu.length; d++) {
                for (var i = 0; i < $scope.booking_menu[d].products.length; i++) {
                    if ($scope.booking_menu[d].products[i].enabled == true) {
                        params['product_id'][i] = $scope.booking_menu[d].products[i].product_id;
                    }
                }
            }
            $location.url('/kiosk/booking/shedule/'+angular.toJson(params));
        }
        $scope.servcount = 0;
        $scope.terminal_servcount = function() {
            var count = 0;
            var selected_products = {};
            for (var d = 0; d < $scope.booking_menu.length; d++) {
                for (var i = 0; i < $scope.booking_menu[d].products.length; i++) {
                    if ($scope.booking_menu[d].products[i].enabled == true) {
                        count = count + 1;
                    }
                }
            }
            $scope.servcount = count;
        }
        var serialize = function(obj, prefix) {
            var str = [];
            for(var p in obj) {
                if (obj.hasOwnProperty(p)) {
                    var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
                    str.push(typeof v == "object" ? serialize(v, k) : encodeURIComponent(k) + "=" + encodeURIComponent(v));
                }
            }
            return str.join("&");
        }
        $scope.sid = $stateParams.sid;
        var load_booking_menu = function (sid) {
            BookingService.load_booking_menu(sid).success(
                function(data) {
                    for (var d = 0; d < data.length; d++) {
                        for (var i = 0; i < data[d].products.length; i++) {
                            data[d].products[i].enabled = false;
                        }
                    }
                    $scope.booking_menu = data;
                }
            );
        }
        $scope.$on('$viewContentLoaded', function(){
            load_booking_menu($stateParams.sid);
        });
        // Cleanup the modal when we're done with it!
        $scope.$on('$destroy', function() {
            $scope.booking_menu = [];
        });
    })

    .controller('BookingProductController', function ($scope, $http, $stateParams, $location, $ionicLoading, Fullscreen, BookingService) {
        var load_booking_menu = function (sid) {
            BookingService.load_booking_menu(sid).success(
                function(data) {
                    $scope.booking_menu = data;
                }
            );
        }
        $scope.ticket_add = function (action_type) {
            $scope.button_add_action_type = action_type;
            AdminService.config_products().success(
                function(data) {
                    $scope.config_products_data = [];
                    for (var i = 0; i < data.length; i++) {
                        $scope.config_products_data[i] = {
                            product_id:data[i].product_id,
                            product_name:data[i].product_name,
                            enabled: false
                        }
                    }
                }
            );
        }
        $scope.ticket_add_products = function () {
            for (var i = 0; i < $scope.config_products_data.length; i++) {
                if ($scope.config_products_data[i].enabled == true) {
                    AdminService.admin_tickets_add_product($scope.admin_tickets_ticket.ticket_id, $scope.config_products_data[i].product_id).success(
                        function(data) {
                            $scope.modal_ticket_add.hide();
                            $scope.admin_tickets_ticket = data;
                        }
                    );
                }
            }
        }
        var load_booking_product = function (pid) {
            BookingService.load_booking_product(pid).success(
                function(data) {
                    $scope.product_info = data;
                }
            );
        }
        $scope.$on('$viewContentLoaded', function(){
            load_booking_product($stateParams.pid);
        });
    })

    .controller('PlasmaController', function ($scope, $http, $timeout, $interval, Fullscreen, PlasmaService) {
        // Notifications
        var notification_hash = '';
        var audio_notification_player = document.getElementById("audio_notification_player");

        var load_notifications_queue = function () {
            PlasmaService.load_notifications_queue().success(
                function(data) {
                    $scope.notifications_queue = data;
                }
            );
        }
        var queue_recent_notification = function () {
            PlasmaService.queue_recent_notification().success(
                function(data) {
                    $scope.queue_recent_notification = data;
                    check_notification_hash(data);
                }
            );
        }
        var check_notification_hash = function (data) {
            if (notification_hash == data.hash && notification_hash != null && notification_hash != 'undefined') {

            } else {
                notification_hash = data.hash;
                PlasmaService.load_audio_notification().success(
                    function(data) {
                        audio_notification_player.setAttribute('src', 'data:audio/mpeg;base64,'+data);
                        audio_notification_player.play();
                    }
                );
            }
        }
        var load_marquee = function () {
            PlasmaService.load_marquee().success(
                function(data) {
                    $scope.marquee = data;
                }
            );
        }();
        var timer = $interval(function() {
                load_notifications_queue();
                queue_recent_notification();
        }, 15000);
        $scope.$on('$viewContentLoaded', function(){
            load_notifications_queue();
            queue_recent_notification();
            if (!Fullscreen.isEnabled()) {
                Fullscreen.all();
            }
        });
    })

    .controller('OperatorController', function ($scope, $location, $interval, $ionicModal, $ionicLoading, AdminService, OperationService, AuthService) {
        var ionicLoadingShow = function() {
            $ionicLoading.show({
                template: 'Загрузка...'
            });
        }
        var ionicLoadingHide = function(){
            $ionicLoading.hide();
        }
        var config_workplace = function () {
            AuthService.config_workplace().success(
                function(data) {
                    $scope.config_workplace = data;
                }
            );
        }
        var operation_queue = function() {
            OperationService.operation_queue().success(
                function(data) {
                    $scope.operation_queue = data;
                    $scope.operation_queue_count = data.length;
                    operation_recent();
                }
            );
        }
        var operation_recent = function() {
            OperationService.operation_recent().success(
                function(data) {
                    $scope.operation_recent = data;
                    ionicLoadingHide();
                }
            );
        }
        $scope.operation_reject = function() {
            ionicLoadingShow();
            OperationService.operation_reject().success(
                function(data) {
                    operation_queue();
                    // operation_recent();
                }
            );
        }
        $scope.operation_invite = function() {
            ionicLoadingShow();
            OperationService.operation_invite().success(
                function(data) {
                    operation_queue();
                    // operation_recent();
                }
            );
        }
        $scope.operation_start = function() {
            ionicLoadingShow();
            OperationService.operation_start().success(
                function(data) {
                    operation_queue();
                    // operation_recent();
                }
            );
        }
        $scope.operation_complete = function() {
            ionicLoadingShow();
            OperationService.operation_complete().success(
                function(data) {
                    operation_queue();
                    // operation_recent();
                }
            );
        }
        $scope.operation_call_again = function() {
            OperationService.operation_call_again();
        }
        $scope.format_time = function (unix_timestamp) {
            var date = new Date(unix_timestamp*1000);
            var hours = date.getHours();
            var minutes = "0" + date.getMinutes();
            var seconds = "0" + date.getSeconds();
            var formattedTime = hours + ':' + minutes.substr(minutes.length-2) + ':' + seconds.substr(seconds.length-2);
            return formattedTime;
        }
        var timer = $interval(function() {
            operation_queue();
        }, 30000);
        $scope.$on('$viewContentLoaded', function(){
            ionicLoadingShow();
            config_workplace();
        });
    })

    .controller('StatisticsController', function ($scope, $location, StatisticsService) {
        var statistics_timing = function() {
            StatisticsService.statistics_timing().success(
                function(data) {
                    $scope.statistics_timing = data;
                }
            );
        }
        var statistics_counting = function() {
            StatisticsService.statistics_counting().success(
                function(data) {
                    $scope.statistics_counting = data;
                }
            );
        }
        var statistics_visitors = function() {
            StatisticsService.statistics_visitors().success(
                function(data) {
                    $scope.statistics_visitors = data;
                }
            );
        }
        var statistics_categories = function() {
            StatisticsService.statistics_categories().success(
                function(data) {
                    $scope.statistics_categories = data;
                }
            );
        }
        $scope.$on('$viewContentLoaded', function(){
            statistics_timing();
            statistics_counting();
            statistics_visitors();
            statistics_categories();
        });
    })

    .controller('SettingsController', function ($scope, $location, $ionicLoading, $ionicModal, AdminService) {
        var ionicLoadingShow = function() {
            $ionicLoading.show({
                template: 'Загрузка...'
            });
        }
        var ionicLoadingHide = function(){
            $ionicLoading.hide();
        }
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.settings.entity.add.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal = modal;
        });
        $scope.list = {
            showDelete: false
        }
        $scope.delete = function (item, entity) {
            ionicLoadingShow();
            AdminService.settings_entities_unset(entity).success(
                function(data) {
                    $scope.settings_entities.splice(item, 1);
                    ionicLoadingHide();
                }
            );
        }
        var settings_entities_get = function() {
            ionicLoadingShow();
            AdminService.settings_entities_get().success(
                function(data) {
                    $scope.settings_entities = data;
                    ionicLoadingHide();
                }
            );
        }();
        $scope.adata = {
            entity: $scope.entity,
            description: $scope.description
        };
        $scope.admin_entity_add = function() {
            ionicLoadingShow();
            AdminService.settings_entities_set($scope.adata).success(
                function(data) {
                    $scope.settings_entities.splice(0, 0, $scope.adata);
                    ionicLoadingHide();
                    $scope.modal.hide();
                }
            );
        };
    })

    .controller('EntityController', function ($scope, $location, $ionicLoading, $ionicModal, $stateParams, AdminService) {
        var ionicLoadingShow = function() {
            $ionicLoading.show({
                template: 'Загрузка...'
            });
        }
        var ionicLoadingHide = function(){
            $ionicLoading.hide();
        }
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.settings.entity.param.add.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal = modal;
        });
        $scope.list = {
            showDelete: false
        }
        $scope.delete = function (item, id) {
            ionicLoadingShow();
            AdminService.settings_parameters_unset(id).success(
                function(data) {
                    $scope.settings_parameters.splice(item, 1);
                    ionicLoadingHide();
                }
            );
        }
        var settings_parameters_get = function(entity) {
            ionicLoadingShow();
            AdminService.settings_parameters_get(entity).success(
                function(data) {
                    $scope.settings_parameters = data;
                    ionicLoadingHide();
                }
            );
        };
        $scope.adata = {
            entity: $stateParams.entity,
            parameter: $scope.parameter,
            value: $scope.value,
            description: $scope.description
        };
        $scope.settings_parameters_set = function() {
            ionicLoadingShow();
            AdminService.settings_parameters_set($scope.adata).success(
                function(data) {
                    $scope.settings_parameters.splice(0, 0, $scope.adata);
                    ionicLoadingHide();
                    $scope.modal.hide();
                }
            );
        };
        $scope.$on('$viewContentLoaded', function(){
            settings_parameters_get($stateParams.entity);
        });
    })

    .controller('ConfigController', function ($scope, $location, $ionicLoading, $ionicModal, ConfigService, AdminService) {
        var ionicLoadingShow = function() {
            $ionicLoading.show({
                template: 'Загрузка...'
            });
        }
        var ionicLoadingHide = function(){
            $ionicLoading.hide();
        }
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.settings.product.add.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal_product_add = modal;
        });
        $scope.list = {
            showDelete: false
        }
        $scope.delete = function (item, id) {
            ionicLoadingShow();
            AdminService.settings_products_unset(id).success(
                function(data) {
                    $scope.config_products.splice(item, 1);
                    ionicLoadingHide();
                }
            );
        }
        var config_products = function() {
            ionicLoadingShow();
            ConfigService.config_products().success(
                function(data) {
                    $scope.config_products = data;
                    ionicLoadingHide();
                }
            );
        }
        $scope.show_product_add = function() {
            $scope.modal_product_add.show();
            $scope.situations = [];
            $scope.adata = {
                product_name:$scope.product_name,
                product_recordlimit:$scope.product_recordlimit,
                product_timereglament:$scope.product_timereglament,
                product_description:$scope.product_description,
                situations:$scope.situations
            }
            ionicLoadingShow();
            ConfigService.config_situations().success(
                function(data) {
                    $scope.config_situations = data;
                    ionicLoadingHide();
                }
            );
        }
        $scope.add_product = function() {
            ionicLoadingShow();
            for (var i = 0; i < $scope.config_situations.length; i++) {
                if ($scope.config_situations[i].enabled == true) {
                    $scope.situations.splice(0, 0, $scope.config_situations[i].id);
                }
            }
            AdminService.settings_products_add($scope.adata).success(
                function(data) {
                    config_products();
                    ionicLoadingHide();
                    $scope.modal_product_add.hide();
                }
            );
        }
        $scope.run_queue_admin_sync = function() {
            ionicLoadingShow();
            AdminService.queue_admin_sync().success(
                function(data) {
                    $scope.queue_admin_sync = data;
                    config_products();
                }
            );
        }
        $scope.$on('$viewContentLoaded', function(){
            config_products();
        });
    })

    .controller('SituationsController', function ($scope, $location, $ionicLoading, $ionicModal, ConfigService, AdminService) {
        var ionicLoadingShow = function() {
            $ionicLoading.show({
                template: 'Загрузка...'
            });
        }
        var ionicLoadingHide = function(){
            $ionicLoading.hide();
        }
        $ionicModal.fromTemplateUrl('/Views/ionic/templates/admin.settings.situation.add.html', {
            scope: $scope,
            animation: 'slide-in-up'
        }).then(function(modal) {
            $scope.modal = modal;
        });
        $scope.list = {
            showDelete: false
        }
        $scope.delete = function (item, id) {
            ionicLoadingShow();
            AdminService.settings_products_unset(id).success(
                function(data) {
                    $scope.config_situations.splice(item, 1);
                    ionicLoadingHide();
                }
            );
        }
        var config_situations = function() {
            ionicLoadingShow();
            ConfigService.config_situations().success(
                function(data) {
                    $scope.config_situations = data;
                    ionicLoadingHide();
                }
            );
        }
        var config_products = function() {
            ionicLoadingShow();
            ConfigService.config_products().success(
                function(data) {
                    $scope.config_products = data;
                    ionicLoadingHide();
                }
            );
        }
        $scope.show_situation_add = function() {
            $scope.modal.show();
            $scope.products = [];
            $scope.adata = {
                situation_name:$scope.situation_name,
                situation_prefix:$scope.situation_prefix,
                kioskmenu_expanded:$scope.kioskmenu_expanded,
                products:$scope.products
            }
            ionicLoadingShow();
            ConfigService.config_products().success(
                function(data) {
                    $scope.config_products = data;
                    ionicLoadingHide();
                }
            );
        }
        $scope.add_situation = function() {
            ionicLoadingShow();
            for (var i = 0; i < $scope.config_products.length; i++) {
                if ($scope.config_products[i].enabled == true) {
                    $scope.products.splice(0, 0, $scope.config_products[i].product_id);
                }
            }
            console.log($scope.adata);
            AdminService.settings_situation_add($scope.adata).success(
                function(data) {
                    config_situations();
                    ionicLoadingHide();
                    $scope.modal.hide();
                }
            );
        }
        $scope.run_queue_admin_sync = function() {
            ionicLoadingShow();
            AdminService.queue_admin_sync().success(
                function(data) {
                    $scope.queue_admin_sync = data;
                    config_situations();
                }
            );
        }
        $scope.$on('$viewContentLoaded', function(){
            config_situations();
        });
    })

    .controller('LedController', function ($scope, $location, $interval, OperationService, AuthService) {
        var config_workplace = function () {
            AuthService.config_workplace().success(
                function(data) {
                    $scope.config_workplace = data;
                }
            );
        }
        var operator_led = function() {
            OperationService.operator_led().success(
                function(data) {
                    $scope.operator_led = data;
                }
            );
        }
        var timer = $interval(function() {
            operator_led();
        }, 10000);
        $scope.$on('$viewContentLoaded', function(){
            config_workplace();
        });
    })

    .controller('LoginController', function ($scope, $auth, $window, AuthService) {
        var config_clients = function() {
            AuthService.config_clients().success(
                function(data) {
                    $scope.clients = data;
                }
            );
        }
        var config_shedule = function() {
            AuthService.config_shedule('operable=1').success(
                function(data) {
                    $scope.shedule_types = data;
                }
            );
        }
        $scope.login = localStorage.username;
        $scope.password = localStorage.password;
        $scope.adata = {
            login: $scope.login,
            password: $scope.password,
            client_id: $scope.client_id,
            shedule: $scope.shedule
        }
        $scope.login = function() {
            $auth.login($scope.adata).then(function() {
                $window.location.reload();
            }).catch(function(response) {
                console.log(response);
            });
        }
        $scope.authenticate = function (provider) {
            $auth.authenticate(provider).then(function() {
            }).catch(function(response) {
            });
        }
        $scope.$on('$viewContentLoaded', function(){
            config_clients();
            config_shedule();
        });
    })

    .controller('LogoutController', function ($window, $scope, $auth, $ionicPopup, $window, AuthService) {
        if (!$auth.isAuthenticated()) {
            return;
        }
        var show_confirm = function() {
            var config_shedule = function() {
                AuthService.config_shedule('operable=0').success(
                    function(data) {
                        $scope.shedule_types = data;
                    }
                );
            }
            config_shedule();
            var confirm_popup = $ionicPopup.confirm({
                title: 'Причина завершения',
                template: '<label class="item item-input item-select"><div class="input-label">Статус</div><select name="shedule" ng-model="adata.shedule"><option ng-repeat="shedule_type in shedule_types" value="{{shedule_type.type_id}}">{{shedule_type.type_name}}</option></select></label>',
                scope: $scope
            });
            $scope.adata = {
                shedule: $scope.shedule
            }
            confirm_popup.then(function(res) {
                if(res) {
                    AuthService.operation_logout($scope.adata.shedule).success(function (data) {
                        $auth.logout().then(function() {
                            $window.location.reload();
                        });
                    });
                } else {
                    $window.history.back();
                }
            });
        }
        show_confirm();
    });
