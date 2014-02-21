//------------- ROUTER ---------------------
var Router = Backbone.Router.extend({
    routes: {
        '': 'home',
        'machines/:id/location?location=:location': 'machine_detail',
        'machines/:machine_id/coupons/:coupon_id' : 'coupon_detail',
        'categories/:id' : 'category_detail'
    }
});

var router = new Router();
var bannersView = new BannersView();

var homeView = new HomeView();
router.on('route:home', function() {
    bannersView.render();
    homeView.render();
});

var machineDetailView = new MachineDetailView();
router.on('route:machine_detail', function(id, location) {
    bannersView.render();
    machineDetailView.render({ id: id, location: location });
});

var couponDetailView = new CouponDetailView();
router.on('route:coupon_detail', function(machine_id, coupon_id) {
    bannersView.render();
    couponDetailView.render({ machine_id: machine_id, coupon_id: coupon_id });
});

var categoryDetailView = new CategoryDetailView();
router.on('route:category_detail', function(id) {
    bannersView.render();
    categoryDetailView.render({id: id });
});

Backbone.history.start();

