var sideMenuApp = angular.module('sideMenuApp', [
    'ionic',
    'ngRoute',
    'ngSanitize',
    'ui.router',
    'sideMenuApp.controllers', 
    'sideMenuApp.services', 
    'sideMenuApp.directives', 
    'satellizer',
    'mediaPlayer'
]);
sideMenuApp.factory('authHttpResponseInterceptor',['$q', '$location', function($q, $location, $window){
    return {
        response: function(response){
            if (response.status === 401) {
            }
            return response || $q.when(response);
        },
        responseError: function(rejection) {
            if (rejection.status === 401) {
                delete localStorage['satellizer_Bearer'];
                $location.reload();
            }
            return $q.reject(rejection);
        }
    }
}])
sideMenuApp.run(function($rootScope, $auth){
    var otherwiseRoutes = function () {
        if (!$auth.isAuthenticated()) {
            sideMenuApp.stateProvider.state('login', {
                url: '/login',
                controller: 'LoginController',
                templateUrl: '/Views/ionic/templates/login.html',
                resolve: {
                    authenticated: function($location, $auth) {
                        if ($auth.isAuthenticated()) {
                            return $location.path('/');
                        }
                    }
                }
            });
            sideMenuApp.urlRouterProvider.otherwise('/login');
        } else {
            var token = $auth.getPayload();
            var role = token.rol;
            if (role == 0) {
                sideMenuApp.stateProvider.state('app', {
                    url: "",
                    abstract: true,
                    templateUrl: '/Views/ionic/templates/menu.html',
                    controller: 'AppController',
                    resolve: {
                        authenticated: function($location, $auth) {
                            if (!$auth.isAuthenticated()) {
                                return $location.path('/login');
                            }
                        }
                    }
                }).state('app.admin', {
                    url: '/admin',
                    views: {
                        'content': {
                            controller: 'AdminController',
                            templateUrl: '/Views/ionic/templates/admin.html'
                        }
                    }
                }).state('app.ticket', {
                    url: '/admin/ticket/:ticketId',
                    views: {
                        'content': {
                            controller: 'TicketController',
                            templateUrl: '/Views/ionic/templates/admin.ticket.html'
                        }
                    }
                }).state('app.statistics', {
                    url: '/admin/statistics',
                    views: {
                        'content': {
                            controller: 'StatisticsController',
                            templateUrl: '/Views/ionic/templates/statistics.html'
                        }
                    }
                }).state('app.services', {
                    url: '/admin/services',
                    views: {
                        'content': {
                            controller: 'ConfigController',
                            templateUrl: '/Views/ionic/templates/admin.services.html'
                        }
                    }
                }).state('app.situations', {
                    url: '/admin/situations',
                    views: {
                        'content': {
                            controller: 'SituationsController',
                            templateUrl: '/Views/ionic/templates/admin.situations.html'
                        }
                    }
                }).state('app.users', {
                    url: '/admin/users',
                    views: {
                        'content': {
                            controller: 'UsersController',
                            templateUrl: '/Views/ionic/templates/admin.users.html'
                        }
                    }
                }).state('app.workplaces', {
                    url: '/admin/workplaces',
                    views: {
                        'content': {
                            controller: 'WorkplacesController',
                            templateUrl: '/Views/ionic/templates/admin.workplaces.html'
                        }
                    }
                }).state('app.clients', {
                    url: '/admin/clients',
                    views: {
                        'content': {
                            controller: 'ClientsController',
                            templateUrl: '/Views/ionic/templates/admin.clients.html'
                        }
                    }
                }).state('app.shedule', {
                    url: '/admin/shedule',
                    views: {
                        'content': {
                            controller: 'SheduleController',
                            templateUrl: '/Views/ionic/templates/admin.shedule.html'
                        }
                    }
                }).state('app.settings', {
                    url: '/admin/settings',
                    views: {
                        'content': {
                            controller: 'SettingsController',
                            templateUrl: '/Views/ionic/templates/admin.settings.html'
                        }
                    }
                }).state('app.parameters', {
                    url: '/admin/settings/parameters/:entity',
                    views: {
                        'content': {
                            controller: 'EntityController',
                            templateUrl: '/Views/ionic/templates/admin.parameters.html'
                        }
                    }
                }).state('plasma', {
                    url: '/plasma',
                    controller: 'PlasmaController',
                    templateUrl: '/Views/ionic/templates/plasma.html'
                });
                sideMenuApp.urlRouterProvider.otherwise('/admin');
            } else if (role == 1) {
                sideMenuApp.stateProvider.state('app', {
                        url: "",
                        abstract: true,
                        templateUrl: '/Views/ionic/templates/menu.html',
                        controller: 'AppController',
                        resolve: {
                            authenticated: function($location, $auth) {
                                if (!$auth.isAuthenticated()) {
                                    return $location.path('/login');
                                }
                            }
                        }
                    }).state('app.operator', {
                    url: '/operator',
                    views: {
                        'content': {
                            controller: 'OperatorController',
                            templateUrl: '/Views/ionic/templates/operator.html'
                        }
                    }
                })
                .state('led', {
                    url: '/led',
                    controller: 'LedController',
                    templateUrl: '/Views/ionic/templates/led.html'
                })
                .state('plasma', {
                    url: '/plasma',
                    controller: 'PlasmaController',
                    templateUrl: '/Views/ionic/templates/plasma.html'
                });
                sideMenuApp.urlRouterProvider.otherwise('/operator');
            } else if (role == 2) {
                sideMenuApp.stateProvider.state('kiosk', {
                    url: "/kiosk",
                    abstract: true,
                    templateUrl: '/Views/ionic/templates/kiosk.html',
                    controller: 'KioskController'
                })
                .state('kiosk.booking', {
                    url: '/booking',
                    views: {
                        'content': {
                            controller: 'BookingController',
                            templateUrl: '/Views/ionic/templates/booking.html'
                        }
                    }
                })
                .state('kiosk.situation', {
                    url: '/situation/:sid',
                    views: {
                        'content': {
                            controller: 'BookingSituationController',
                            templateUrl: '/Views/ionic/templates/situation.html'
                        }
                    }
                })
                .state('kiosk.shedule', {
                    url: '/booking/shedule/:selected_products',
                    views: {
                        'content': {
                            controller: 'BookingSheduleController',
                            templateUrl: '/Views/ionic/templates/booking.shedule.html'
                        }
                    }
                })
                .state('kiosk.submit', {
                    url: '/booking/submit',
                    views: {
                        'content': {
                            controller: 'BookingSubmitController',
                            templateUrl: '/Views/ionic/templates/booking.submit.html'
                        }
                    }
                })
                .state('kiosk.booksituation', {
                    url: '/booking/situation/:sid',
                    views: {
                        'content': {
                            controller: 'BookingSituationController',
                            templateUrl: '/Views/ionic/templates/booking.situation.html'
                        }
                    }
                })
                .state('product', {
                    url: '/booking/product/:pid',
                    controller: 'BookingProductController',
                    templateUrl: '/Views/ionic/templates/product.html'
                })
                .state('plasma', {
                    url: '/plasma',
                    controller: 'PlasmaController',
                    templateUrl: '/Views/ionic/templates/plasma.html'
                });
                sideMenuApp.urlRouterProvider.otherwise('/kiosk/booking');
            } else if (role == 3) {
                sideMenuApp.stateProvider.state('plasma', {
                    url: '/plasma',
                    controller: 'PlasmaController',
                    templateUrl: '/Views/ionic/templates/plasma.html'
                });
                sideMenuApp.urlRouterProvider.otherwise('/plasma');
            }
            sideMenuApp.stateProvider.state('logout', {
                url: '/logout',
                template: null,
                controller: 'LogoutController'
            });
        }
    }();
});
sideMenuApp.config(function($provide){
    $provide.decorator("$sanitize", function($delegate, $log){
        return function(text, target){
 
            var result = $delegate(text, target);
            // $log.info("$sanitize input: " + text);
            // $log.info("$sanitize output: " + result);
 
            return result;
        };
    });
});
sideMenuApp.config(['$httpProvider', '$compileProvider', '$stateProvider', '$urlRouterProvider', '$authProvider', function($httpProvider, $compileProvider, stateProvider, urlRouterProvider, $authProvider) {
    sideMenuApp.stateProvider = stateProvider;
    sideMenuApp.urlRouterProvider = urlRouterProvider;
    $httpProvider.interceptors.push('authHttpResponseInterceptor');
    $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|file|tel):/);
    $authProvider.loginOnSignup = true;
    $authProvider.loginRedirect = '/admin';
    $authProvider.logoutRedirect = '/login';
    $authProvider.signupRedirect = '/login';
    $authProvider.loginUrl = '/operation/identify';
    $authProvider.tokenName = 'Bearer';
    $authProvider.tokenPrefix = 'satellizer'; // Local Storage name prefix
    $authProvider.authHeader = 'Authorization';
}]);
