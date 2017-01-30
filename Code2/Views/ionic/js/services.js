angular.module('sideMenuApp.services', [])

.factory('MenuService', function ($auth, $http) {
    // var menuItems = [
    //     { text: 'Запись в очередь', iconClass: 'icon ion-ios7-calendar-outline', rightIconClass: 'icon', link: 'kiosk/booking', target: '_blank'},
    //     { text: 'Индикатор очереди', iconClass: 'icon ion-ios7-pulse', rightIconClass: 'icon ion-ios7-upload-outline', link: 'plasma', target: '_blank'}
    // ];

    return {
        all: function() {
            return  $http({
                method: 'GET',
                url: '/config/ionicmenu'
            })
        }
    }
})

.factory('AuthService', function ($http) {
    var config_clients = '/config/clients';
    var config_workplace = '/config/workplace';
    var operation_logout = '/operation/logout';
    return {
        config_clients: function() {
            return  $http({
               method: 'GET',
               url: config_clients
            })
        },
        config_workplace: function() {
            return  $http({
               method: 'GET',
               url: config_workplace
            })
        },
        config_shedule: function(params) {
            return  $http({
               method: 'GET',
               url: '/config/shedule?'+params
            })
        },
        operation_logout: function(shedule) {
            return  $http({
               method: 'POST',
               url: operation_logout,
               data: {
                    shedule: shedule
               }
            })
        }
    }
})

.factory('BookingService', function ($http) {
    var booking_schedule = '/booking/schedule/days';
    var booking_menu = '/booking/menu';
    var booking_product = '/booking/product/';
    return {
        load_booking_schedule: function() {
            return  $http({
               method: 'GET',
               url: booking_schedule
            })
        },
        load_schedule: function(data) {
            return  $http({
               method: 'POST',
               url: '/booking/schedule',
               data: data
            })
        },
        load_booking_menu: function(sid) {
            return  $http({
               method: 'GET',
               url: booking_menu+'?situationId='+sid
            })
        },
        load_booking_product: function(pid) {
            return  $http({
               method: 'GET',
               url: booking_product+pid
            })
        },
        booking_tickets_new: function(products) {
            var booking_tickets_new = '/booking/tickets/new';
            return  $http({
               method: 'POST',
               url: booking_tickets_new,
               data: products
            })
        },
        booking_tickets_submit: function(pincode) {
            return  $http({
               method: 'POST',
               url: '/booking/tickets/submit',
               data: {pincode:pincode}
            })
        },
        booking_tickets_check: function(pincode) {
            return  $http({
               method: 'POST',
               url: '/booking/tickets/check',
               data: {pincode:pincode}
            })
        }
    }
})

.factory('AdminService', function ($http) {
    return {
        admin_login: function(login, password) {
            var admin_login = '/queue/admin/login/'+login+'/'+password;
            return  $http({
               method: 'POST',
               url: admin_login
            })
        },
        admin_tickets: function() {
            var admin_tickets = '/queue/admin/tickets';
            return  $http({
               method: 'GET',
               url: admin_tickets
            })
        },
        admin_tickets_booking: function() {
            var admin_tickets_booking = '/queue/admin/booking';
            return  $http({
               method: 'GET',
               url: admin_tickets_booking
            })
        },
        admin_tickets_ticket: function(ticketId) {
            var admin_tickets_ticket = '/queue/admin/tickets/'+ticketId;
            return  $http({
               method: 'GET',
               url: admin_tickets_ticket
            })
        },
        admin_tickets_new: function(products) {
            var admin_tickets_new = '/queue/admin/tickets/new';
            return  $http({
               method: 'POST',
               url: admin_tickets_new,
               data: products
            })
        },
        admin_tickets_add_product: function(ticketId, productId) {
            var admin_tickets_add_product = '/queue/admin/tickets/'+ticketId+'/addProduct/'+productId;
            return  $http({
               method: 'POST',
               url: admin_tickets_add_product
            })
        },
        admin_tickets_cancel: function(ticketId, productId) {
            var admin_tickets_cancel = '/queue/admin/tickets/'+ticketId+'/'+productId+'/cancel';
            return  $http({
               method: 'POST',
               url: admin_tickets_cancel
            })
        },
        admin_tickets_priority: function(ticketId, productId, priority) {
            var admin_tickets_priority = '/queue/admin/tickets/'+ticketId+'/'+productId+'/setPriority';
            return  $http({
               method: 'POST',
               url: admin_tickets_priority,
               data: {
                    priority: priority
               }
            })
        },
        admin_redirect_user: function(productId) {
            var admin_redirect_user = '/queue/admin/redirect/users/'+productId;
            return  $http({
               method: 'POST',
               url: admin_redirect_user
            })
        },
        admin_redirect_workplace: function(productId) {
            var admin_redirect_workplace = '/queue/admin/redirect/workPlaces/'+productId;
            return  $http({
               method: 'POST',
               url: admin_redirect_workplace
            })
        },
        config_products: function() {
            var config_products = '/config/products';
            return  $http({
               method: 'GET',
               url: config_products
            })
        },
        user_binded_products: function(uid) {
            return  $http({
               method: 'GET',
               url: '/config/products/user/'+uid
            })
        },
        user_binded_situations: function(uid) {
            return  $http({
               method: 'GET',
               url: '/config/situations/user/'+uid
            })
        },
        client_binded_products: function(cid) {
            return  $http({
               method: 'GET',
               url: '/config/products/client/'+cid
            })
        },
        client_binded_situations: function(cid) {
            return  $http({
               method: 'GET',
               url: '/config/situations/client/'+cid
            })
        },
        queue_admin_sync: function() {
            var queue_admin_sync = '/queue/admin/sync';
            return  $http({
               method: 'POST',
               url: queue_admin_sync
            })
        },
        admin_users: function() {
            return  $http({
               method: 'GET',
               url: '/queue/admin/users'
            })
        },
        admin_user_add: function(data) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/user/new',
               data: data
            })
        },
        admin_user_delete: function(uid) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/user/delete',
               data: {user_id:uid}
            })
        },
        admin_user_change: function(data) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/user/change',
               data: data
            })
        },
        admin_client_delete: function(id) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/client/delete',
               data: {id:id}
            })
        },
        admin_workplaces: function() {
            return  $http({
               method: 'GET',
               url: '/queue/admin/workplaces'
            })
        },
        admin_wp_add: function(data) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/workplace/new',
               data: data
            })
        },
        admin_cl_add: function(data) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/client/new',
               data: data
            })
        },
        admin_cl_change: function(data) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/client/change',
               data: data
            })
        },
        admin_ts_add: function(data) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/shedule/type/new',
               data: data
            })
        },
        admin_shedule_delete: function(id) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/shedule/type/delete',
               data: {type_id:id}
            })
        },
        admin_shedule_change: function(data) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/shedule/type/change',
               data: data
            })
        },
        admin_workplace_cancel: function(wid) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/workplace/delete',
               data: {workplace_id:wid}
            })
        },
        admin_workplace_change: function(data) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/workplace/change',
               data: data
            })
        },
        settings_entities_get: function() {
            return  $http({
               method: 'GET',
               url: '/queue/admin/settings/entities'
            })
        },
        settings_entities_set: function(data) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/settings/entities/set',
               data: data
            })
        },
        settings_entities_unset: function(entity) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/settings/entities/unset',
               data: {entity:entity}
            })
        },
        settings_parameters_get: function(entity) {
            return  $http({
               method: 'GET',
               url: '/queue/admin/settings/parameters?entity='+entity
            })
        },
        settings_parameters_set: function(data) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/settings/parameters/set',
               data: data
            })
        },
        settings_parameters_unset: function(id) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/settings/parameters/unset',
               data: {id:id}
            })
        },
        settings_products_unset: function(id) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/settings/products/unset',
               data: {id:id}
            })
        },
        settings_products_add: function(data) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/settings/products/add',
               data: data
            })
        },
        settings_situation_add: function(data) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/settings/situation/add',
               data: data
            })
        },
        add_up_binding: function(user_id, products) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/user/up',
               data: {user_id:user_id, products:products}
            })
        },
        add_us_binding: function(user_id, situations) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/user/us',
               data: {user_id:user_id, situations:situations}
            })
        },
        add_cp_binding: function(cid, products) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/client/cp',
               data: {cid:cid, products:products}
            })
        },
        add_cs_binding: function(cid, situations) {
            return  $http({
               method: 'POST',
               url: '/queue/admin/client/cs',
               data: {cid:cid, situations:situations}
            })
        },
    }
})

.factory('OperationService', function ($http) {
    return {
        redirect_users: function(productId) {
            var redirect_users = '/operation/redirect/users/'+productId;
            return  $http({
               method: 'POST',
               url: redirect_users
            })
        },
        redirect_workplaces: function(productId) {
            var redirect_workplaces = '/operation/redirect/workPlaces/'+productId;
            return  $http({
               method: 'POST',
               url: redirect_workplaces
            })
        },
        redirect: function(productId, userId, workPlaceId, returnAfter) {
            var redirect = '/operation/redirect/'+productId;
            return  $http({
               method: 'POST',
               url: redirect,
               data: {
                    userId: userId, 
                    workPlaceId: workPlaceId, 
                    returnAfter: returnAfter
               }
            })
        },
        operator_led: function() {
            var operator_led = '/operation/recent';
            return  $http({
               method: 'GET',
               url: operator_led
            })
        },
        operation_queue: function() {
            var operation_queue = '/operation/queue';
            return  $http({
               method: 'GET',
               url: operation_queue
            })
        },
        operation_queue_count: function() {
            var operation_queue_count = '/operation/queue/count';
            return  $http({
               method: 'GET',
               url: operation_queue_count
            })
        },
        operation_recent: function() {
            var operation_recent = '/operation/recent';
            return  $http({
               method: 'GET',
               url: operation_recent
            })
        },
        operation_reject: function() {
            var operation_reject = '/operation/reject';
            return  $http({
               method: 'POST',
               url: operation_reject
            })
        },
        operation_invite: function() {
            var operation_invite = '/operation/invite';
            return  $http({
               method: 'POST',
               url: operation_invite
            })
        },
        operation_start: function() {
            var operation_start = '/operation/start';
            return  $http({
               method: 'POST',
               url: operation_start
            })
        },
        operation_complete: function() {
            var operation_complete = '/operation/complete';
            return  $http({
               method: 'POST',
               url: operation_complete
            })
        },
        operation_call_again: function() {
            var call_again = '/operation/callagain';
            return  $http({
               method: 'POST',
               url: call_again
            })
        }
    }
})

.factory('StatisticsService', function ($http) {
    var statistics_timing = '/queue/statistics/timing';
    var statistics_counting = '/queue/statistics/counting';
    var statistics_visitors = '/queue/statistics/visitors';
    var statistics_categories = '/queue/statistics/categories';
    return {
        statistics_timing: function() {
            return  $http({
               method: 'GET',
               url: statistics_timing
            })
        },
        statistics_counting: function() {
            return  $http({
               method: 'GET',
               url: statistics_counting
            })
        },
        statistics_visitors: function() {
            return  $http({
               method: 'GET',
               url: statistics_visitors
            })
        },
        statistics_categories: function() {
            return  $http({
               method: 'GET',
               url: statistics_categories
            })
        }
    }
})

.factory('ConfigService', function ($http) {
    var config_products = '/config/products';
    return {
        config_products: function() {
            return  $http({
               method: 'GET',
               url: config_products
            })
        },
        config_situations: function() {
            return  $http({
               method: 'GET',
               url: '/config/situations'
            })
        },
        config_clients: function() {
            return  $http({
               method: 'GET',
               url: 'config/clients'
            })
        },
        config_wp_types: function() {
            return  $http({
               method: 'GET',
               url: 'config/workplaces/types'
            })
        },
        config_shedule: function(params) {
            return  $http({
               method: 'GET',
               url: 'config/shedule?'+params
            })
        }
    }
})

.factory('PlasmaService', function ($http) {
    var notifications_queue = '/notifications/queue';
    var queue_recent_notification = '/notifications/queue/recent';
    var load_audio_notification = '/notifications/queue/audio';
    return {
        load_notifications_queue: function() {
            return  $http({
               method: 'GET',
               url: notifications_queue
            })
        },
        queue_recent_notification: function() {
            return  $http({
               method: 'GET',
               url: queue_recent_notification
            })
        },
        load_audio_notification: function() {
            return  $http({
               method: 'GET',
               url: load_audio_notification
            })
        },
        load_marquee: function() {
            return  $http({
               method: 'GET',
               url: '/notifications/queue/marquee'
            })
        },
    }
})

.factory('transformRequestAsFormPost', function () {
                // I prepare the request data for the form post.
    function transformRequest(data, getHeaders) {
        var headers = getHeaders();
        headers[ "Content-type" ] = "application/x-www-form-urlencoded; charset=utf-8";
        return( serializeData( data ) );
    }
    // Return the factory value.
    return( transformRequest );
                // ---
                // PRVIATE METHODS.
                // ---
 
 
                // I serialize the given Object into a key-value pair string. This
                // method expects an object and will default to the toString() method.
                // --
                // NOTE: This is an atered version of the jQuery.param() method which
                // will serialize a data collection for Form posting.
                // --
                // https://github.com/jquery/jquery/blob/master/src/serialize.js#L45
    function serializeData(data) {
                    // If this is not an object, defer to native stringification.
        if (!angular.isObject(data)) {
            return ((data == null) ? "" : data.toString());
        }
        var buffer = [];
        // Serialize each key in the object.
        for (var name in data) {
            if (!data.hasOwnProperty(name)) {
                continue;
            }
            var value = data[ name ];
            buffer.push(encodeURIComponent(name) + "=" + encodeURIComponent((value == null) ? "" : value ));
        }
        // Serialize the buffer and clean it up for transportation.
        var source = buffer.join("&").replace(/%20/g, "+");
        return( source );
    }
})

.factory('QZPrint', function () {
    /***************************************************************************
    * Prototype function for printing plain HTML 1.0 to a PostScript capable 
    * printer.  Not to be used in combination with raw printers.
    * Usage:
    *    qz.appendHTML('<h1>Hello world!</h1>');
    *    qz.printPS();
    ***************************************************************************/ 
    var printHTML = function (html) {
        if (qz == 'undefined') { return; }
        // Preserve formatting for white spaces, etc.
        var ticketHTML = html;
        // qz.setEncoding("CP-1251");
        qz.setCopies(1);
        // Append our image (only one image can be appended per print)
        qz.appendHTML(ticketHTML);
        qz.printHTML();
    }
    
    
    /***************************************************************************
    ****************************************************************************
    * *                          HELPER FUNCTIONS                             **
    ****************************************************************************
    ***************************************************************************/
    
    
    /***************************************************************************
    * Gets the current url's path, such as http://site.com/example/dist/
    ***************************************************************************/
    function getPath() {
        var path = window.location.href;
        return path.substring(0, path.lastIndexOf("/")) + "/";
    }
    /**
    * Fixes some html formatting for printing. Only use on text, not on tags!
    * Very important!
    *   1.  HTML ignores white spaces, this fixes that
    *   2.  The right quotation mark breaks PostScript print formatting
    *   3.  The hyphen/dash autoflows and breaks formatting  
    */
    function fixHTML(html) {
        return html.replace(/ /g, "&nbsp;").replace(/’/g, "'").replace(/-/g,"&#8209;"); 
    }
    /**
    * Equivelant of VisualBasic CHR() function
    */
    function chr(i) {
        return String.fromCharCode(i);
    }
    return {
        printHTML: function(html) {
            printHTML(html);
        }
    }
});