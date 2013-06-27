//---------- COLLECTIONS --------------------------
var Machines = Backbone.Collection.extend({
    url: '/machines'
});

var Businesses = Backbone.Collection.extend({
    url: '/businesses'
});

var Coupons = Backbone.Collection.extend({ 
    initialize: function(models, options) {
        this.id = options.id;
    },
    url: function() {
        return '/machines/' + this.id + '/coupons';
    }
});

//---------- MODELS -----------------
var Machine = Backbone.Model.extend({
    urlRoot: '/machines'
});
var Business = Backbone.Model.extend({
    urlRoot: '/businesses'
});

//--------------- VIEWS --------------------

// Render home page contains machines
var MachinesView = Backbone.View.extend({
    el: '.page',
    render: function() {
        var that = this;
        var machines = new Machines();
        machines.fetch({
            success: function (machines) {
                var template = _.template($('#machines').html(), 
                                          { machines: machines.models });
                that.$el.html(template);
            }
        });
    }
});

// Render machine view detail contains coupons belong to that machine
var MachineDetailView = Backbone.View.extend({
    el: '.page',
    render: function(options) {
        var coupons           = new Coupons([], { id: options.id });
        var current_machine   = new Machine({ id: options.id });
        var machines          = new Machines();
        var that              = this;

        // Fetch multiple backbone collections synchronously
        $.when(machines.fetch(), coupons.fetch(), current_machine.fetch()).done(function() {
            var template = _.template($('#coupons').html(), {
                coupons:         coupons.models, 
                current_machine: current_machine.attributes[0],
                machines:        machines.models
            });
            that.$el.html(template);
        });
    }
});

var EditMachineView = Backbone.View.extend({
    el: '.page',
    render: function() {
        var template = _.template($('#edit-machine-template').html(), {});
        this.$el.html(template);
    },
    events: {
        'submit .edit-machine-form' : 'save_machine'
    },
    save_machine: function(ev) {
        var machine_detail = $(ev.currentTarget).serializeObject();
        var machine = new Machine();
        machine.save(machine_detail, {
            success: function (machine) {
                router.navigate('', {trigger: true});
            }
        });
        return false;
    }
})

var EditCouponView = Backbone.View.extend({ 
    el: '.page',
    render: function(options) {
        var current_machine = new Machine({ id: options.id });
        var that = this;

        current_machine.fetch({ 
            success: function(current_machine) {
                var template = _.template($('#edit-coupon-template').html(), {
                    current_machine: current_machine.attributes[0]
                });
                that.$el.html(template);
            }
        });
    }
});

var BusinessesView = Backbone.View.extend({ 
    el: '.page',
    render: function() {
        var that = this;
        var businesses = new Businesses();
        businesses.fetch({
            success: function (businesses) {
                var template = _.template($('#businesses').html(), 
                                          { businesses: businesses.models });
                that.$el.html(template);
            }
        });
    }
});

var EditBusinessView = Backbone.View.extend({ 
    el: '.page',
    render: function() {
        var template = _.template($('#edit-business-template').html(), {});
        this.$el.html(template);
    },
    events: {
        'submit .edit-business-form' : 'save_business'
    },
    save_business: function(ev) {
        var business_detail = $(ev.currentTarget).serializeObject();
        var business = new Business();
        business.save(business_detail, {
            success: function (business) {
                router.navigate('#/businesses', {trigger: true});
            }
        });
        return false;
    }
});
//------------- ROUTER ---------------------
var Router = Backbone.Router.extend({
    routes: {
        '' : 'home',
        'machines/new-machine' : 'editMachine',
        'machines/:id/new-coupon' : 'editCoupon',
        'machines/:id' : 'machineDetail',
        'businesses' : 'viewBusinesses',
        'businesses/new-business' : 'editBusiness'
    }
});

var router = new Router();
router.on('route:home', function() {
    var machinesView = new MachinesView();
    machinesView.render();
});
router.on('route:editMachine', function() {
    var editMachineView = new EditMachineView();
    editMachineView.render();
});
router.on('route:editCoupon', function(id) {
    var editCouponView = new EditCouponView();
    editCouponView.render({ id: id });
});
router.on('route:machineDetail', function(id) {
    var machineDetailView = new MachineDetailView();
    machineDetailView.render({ id: id });
});
router.on('route:viewBusinesses', function() {
    var businessesView = new BusinessesView();
    businessesView.render();
});
router.on('route:editBusiness', function() {
    var editBusinessView = new EditBusinessView();
    editBusinessView.render();
})

Backbone.history.start();
