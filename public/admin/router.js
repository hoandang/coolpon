//------------- ROUTER ---------------------
var Router = Backbone.Router.extend ({
    routes: {
        '':                    'viewCategories',
        'categories/new':      'editCategory',
        'categories/:id/edit': 'editCategory',

        'machines':            'viewMachines',
        'machines/new':        'editMachine',
        'machines/:id/edit':   'editMachine',
        'machines/:id/new':    'editCouponByMachine',
        'machines/:id':        'machineDetail',

        'businesses':          'viewBusinesses',
        'businesses/new':      'editBusiness',
        'businesses/:id/edit': 'editBusiness',

        'coupons':             'viewCoupons',
        'coupons/new':         'editCoupon',
        'coupons/:id/edit':    'editCoupon'
    }
});

var router = new Router();

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
router.on('route:editCouponByMachine', function(id) {
    editCouponByMachineView.render({ id: id });
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
router.on('route:editBusiness', function() {
    editBusinessView.render();
});

// COUPON VIEW
var couponsView = new CouponsView();
router.on('route:viewCoupons', function() {
    couponsView.render();
});
var editCouponView = new EditCouponView();
router.on('route:editCoupon', function() {
    editCouponView.render();
});

Backbone.history.start();
