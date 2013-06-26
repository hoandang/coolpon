
//---------- COLLECTIONS --------------------------
var Machines = Backbone.Collection.extend({
    url: '/machines'
});

//---------- MODELS -----------------
var Machine = Backbone.Model.extend({
    urlRoot: '/machines'
});

var Coupon = Backbone.Model.extend({ 
    initialize: function(models, options) {
        this.machine_id = options.machine_id;
    },
    url: function() {
        return '/machines/' + this.machine_id + '/coupons';
    }
});

//--------------- VIEWS --------------------
var MachinesView = Backbone.View.extend({
    el: '.page',
    render: function() {
        var that = this;
        var machines = new Machines();
        machines.fetch({
            success: function (machines) {
                var template = _.template($('#machines-list').html(), 
                                          { machines: machines.models });
                that.$el.html(template);
            }
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
    //events: {
        //'submit .edit-coupon-form' : 'save_coupon'
    //},
    //save_coupon: function(ev) {
        //var coupon_detail = $(ev.currentTarget);
        //console.log(coupon_detail);
        ////var machine_id    = $('#machine_id').val();
        ////var coupon        = new Coupon([], { machine_id : machine_id});
        ////console.log(coupon_detail);
        ////coupon.save(coupon_detail, {
            ////success: function (coupon) {
                ////console.log(coupon);
            ////}
        ////});
        //return false;
    //}
});

//------------- ROUTER ---------------------
var Router = Backbone.Router.extend({
    routes: {
        '': 'home',
        'new-machine': 'editMachine',
        'machine/:id/new-coupon': 'editCoupon'
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

Backbone.history.start();
