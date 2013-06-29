//---------- COLLECTIONS --------------------------
var Machines = Backbone.Collection.extend({
    url: '/machines'
});

var Businesses = Backbone.Collection.extend({
    url: '/businesses'
});

var Coupons = Backbone.Collection.extend({ 
    url: '/coupons'
});
var CouponsByMachine = Backbone.Collection.extend({ 
    initialize: function(models, options) {
        this.id = options.id;
    },
    url: function() {
        return '/machines/' + this.id + '/coupons';
    }
});
var PostCodes = Backbone.Collection.extend({ 
    initialize: function(options) {
        this.query = options.query;
    },
    url: function() {
        return '/locations/search?q=' + this.query;
    }
});

//---------- MODELS -----------------
var Machine = Backbone.Model.extend({
    urlRoot: '/machines'
});
var Business = Backbone.Model.extend({
    urlRoot: '/businesses'
});
var Coupon = Backbone.Model.extend({
    urlRoot: '/coupons'
});

//--------------- VIEWS --------------------
// MACHINE LIST
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
        var coupons           = new CouponsByMachine([], { id: options.id });
        var current_machine   = new Machine({ id: options.id });
        var machines          = new Machines();
        var that              = this;

        // Fetch multiple backbone collections synchronously
        $.when(machines.fetch(), coupons.fetch(), current_machine.fetch()).done(function() {
            var template = _.template($('#coupons-by-machine').html(), {
                coupons:         coupons.models, 
                current_machine: current_machine.attributes[0],
                machines:        machines.models
            });
            that.$el.html(template);
        });
    }
});

// EDIT MACHINE FORM
var EditMachineView = Backbone.View.extend({
    el: '.page',
    render: function() {
        var template = _.template($('#edit-machine-template').html(), {});
        this.$el.html(template);

        $('p#machine-suburb input').autocomplete({ 
            minLength: 3,
            source: function (request, response) {
                $.get('/locations/search?q=' + request.term, function(data) {
                    response($.map(data, function (item) {
                        return {
                            label: item.location + ' ' + item.state + ' ' + item.postcode + ', AUSTRALIA',
                            value: item.location + ' ' + item.state + ' ' + item.postcode + ', AUSTRALIA',
                        }
                    }));
                });
            }
        });
    },
    events: {
        'submit .edit-machine-form' : 'save_machine'
    },
    save_machine: function(ev) {
        var machine_detail = $(ev.currentTarget).serializeObject();
        if (is_validated(machine_detail))
        {
            $('.edit-machine-form #machine-suburb').removeClass('error');
            $('.edit-machine-form #machine-address').removeClass('error');

            var geocoder = new google.maps.Geocoder();
            var address  = machine_detail.address + ', ' + machine_detail.suburb; 
            geocoder.geocode({ 'address': address }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK)
                {
                    var result = results[0].formatted_address;
                    if (address.toUpperCase() != result.toUpperCase())
                    {
                        var num_commas = result.match(/,/g).length;
                        var address_number = result.split(' ');
                        if (num_commas == 2 && !isNaN(address_number[0]))
                        {
                            console.log('Did you mean: ' + result.toUpperCase());
                            $('.edit-machine-form #machine-suburb').addClass('error');
                            $('.edit-machine-form #machine-address').addClass('error');
                        }
                        else
                        {
                            console.log('The address is not valid or existed');
                            $('.edit-machine-form #machine-suburb').addClass('error');
                            $('.edit-machine-form #machine-address').addClass('error');
                        }
                    }
                    else
                    {
                        console.log('Valid');
                        var machine = new Machine();
                        machine.save(machine_detail, {
                            success: function (machine) {
                                router.navigate('', {trigger: true});
                            }
                        });
                    }
                }
            });
        }
        else 
        {
            indicate_errors(machine_detail);
        }
        return false;
    }
});

// EDIT COUPON BY MACHINE FORM
var EditCouponByMachineView = Backbone.View.extend({ 
    el: '.page',
    render: function(options) {
        var current_machine = new Machine({ id: options.id });
        var businesses      = new Businesses();
        var that = this;

        $.when(businesses.fetch(), current_machine.fetch()).done(function() {
            var template = _.template(
                $('#edit-coupon-by-machine-template').html(), 
                {
                    current_machine: current_machine.attributes[0],
                    businesses: businesses.models
                });

            that.$el.html(template);
            $('#description').wysihtml5();
        });
    }
});

// BUSINESS LIST
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

// EDIT BUSINESS FORM
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

// COUPONS LIST
var CouponsView = Backbone.View.extend({
    el: '.page',
    render: function() {
        var that = this;
        var coupons = new Coupons();
        coupons.fetch({
            success: function (coupons) {
                var template = _.template($('#coupons').html(), 
                                          { coupons: coupons.models });
                that.$el.html(template);
            }
        });
    }
});
// EDIT COUPON FORM
var EditCouponView = Backbone.View.extend({ 
    el: '.page',
    render: function() {
        var businesses = new Businesses();
        var machines   = new Machines();
        var that       = this;

        $.when(businesses.fetch(), machines.fetch()).done(function() {
            var template = _.template($('#edit-coupon-template').html(), {
                businesses: businesses.models,
                machines: machines.models
            });
            that.$el.html(template);
            $('#description').wysihtml5();
        });
    }
});

//------------- ROUTER ---------------------
var Router = Backbone.Router.extend({
    routes: {
        '' : 'home',
        'machines/new-machine' : 'editMachine',
        'machines/:id/new-coupon' : 'editCouponByMachine',
        'machines/:id' : 'machineDetail',
        'businesses' : 'viewBusinesses',
        'businesses/new-business' : 'editBusiness',
        'coupons' : 'viewCoupons',
        'coupons/new-coupon' : 'editCoupon',
        'users': 'viewUsers'
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
router.on('route:editCouponByMachine', function(id) {
    var editCouponByMachineView = new EditCouponByMachineView();
    editCouponByMachineView.render({ id: id });
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
});
router.on('route:viewCoupons', function() {
    var couponsView = new CouponsView();
    couponsView.render();
});
router.on('route:editCoupon', function() {
    var editCouponView = new EditCouponView();
    editCouponView.render();
});
router.on('route:viewUsers', function() {
    console.log('view users');
});

Backbone.history.start();
