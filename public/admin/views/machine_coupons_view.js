// Render machine view detail contains coupons belong to that machine
var MachineCouponsView = Backbone.View.extend({
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
