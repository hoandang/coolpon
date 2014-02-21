//------------- ROUTER ---------------------
var Router = Backbone.Router.extend ({
    routes: {
        '':                    'viewCategories',
        'categories/new':      'editCategory',
        'categories/:id/edit': 'editCategory',

        'machines':          'viewMachines',
        'machines/new':      'editMachine',
        'machines/:id/edit': 'editMachine',

        'machines/:id/coupons':              'machineDetail',
        'machines/:id/coupons/new':          'editCouponByMachine',
        'machines/:m_id/coupons/:c_id/edit': 'editCouponByMachine',

        'businesses':          'viewBusinesses',
        'businesses/new':      'editBusiness',
        'businesses/:id/edit': 'editBusiness',

        'coupons':          'viewCoupons',
        'coupons/new':      'editCoupon',
        'coupons/:id/edit': 'editCoupon',

        'users': 'viewUsers',

        'email/to?email=:email': 'email_view',
        'email/to?email=all': 'email_view',

        'banners': 'viewBanners',
        'banners/:id/edit': 'editBanner'
    }
});

var router = new Router();

// BANNER VIEW
var bannersView = new BannersView();
router.on('route:viewBanners', function() {
    bannersView.render();
});

// EDIT BANNER VIEW
var editBannerView = new EditBannerView();
router.on('route:editBanner', function(id) {
    editBannerView.render({id: id});
});

// EMAIL VIEW
var emailView = new EmailView();
router.on('route:email_view', function(email) {
    emailView.render({ email: email });
});

// USERS VIEW
var usersView = new UsersView();
router.on('route:viewUsers', function() {
    usersView.render();
});

// CATEGORY VIEW
var categoriesView = new CategoriesView();
router.on('route:viewCategories', function() {
    categoriesView.render();
});
var editCategoryView = new EditCategoryView();
router.on('route:editCategory', function(id) {
    editCategoryView.render({id : id});
});

// MACHINE VIEW
var machinesView = new MachinesView();
router.on('route:viewMachines', function() {
    machinesView.render();
});
var editMachineView = new EditMachineView();
router.on('route:editMachine', function(id) {
    editMachineView.render({ id: id });
});
var editCouponByMachineView = new EditCouponByMachineView();
router.on('route:editCouponByMachine', function(m_id, c_id) {
    editCouponByMachineView.render({ machine_id: m_id, coupon_id: c_id });
});
var machineCouponsView = new MachineCouponsView();
router.on('route:machineDetail', function(id) {
    machineCouponsView.render({ id: id });
});

// BUSINESS VIEW
var businessesView = new BusinessesView();
router.on('route:viewBusinesses', function() {
    businessesView.render();
});
var editBusinessView = new EditBusinessView();
router.on('route:editBusiness', function(id) {
    editBusinessView.render({ id: id });
});

// COUPON VIEW
var couponsView = new CouponsView();
router.on('route:viewCoupons', function() {
    couponsView.render();
});
var editCouponView = new EditCouponView();
router.on('route:editCoupon', function(id) {
    editCouponView.render({ id: id });
});

Backbone.history.start();
