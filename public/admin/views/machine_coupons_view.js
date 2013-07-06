// Render machine view detail contains coupons belong to that machine
var MachineCouponsView = Backbone.View.extend({
    el: '.page',
    render: function(options) {
        var machine = new Machine({id: options.id });
        var that    = this;

        machine.fetch({
            success: function(machine) {
                var template = _.template ($('#coupons-by-machine').html(), {
                    machine: machine.attributes[0]
                });
                that.$el.html(template);
            }
        });
    }
});
