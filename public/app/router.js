//------------- ROUTER ---------------------
var Router = Backbone.Router.extend({
    routes: {
        '': 'home',
        'machines/:machine_id/coupons/:coupon_id' : 'coupon_detail'
    }
});

var router = new Router();
router.on('route:home', function() {
    var homeView = new HomeView();
    homeView.render();
});

router.on('route:coupon_detail', function(machine_id, coupon_id) {
    var couponDetailView = new CouponDetailView();
    couponDetailView.render({ machine_id: machine_id, coupon_id: coupon_id });
});

Backbone.history.start();

