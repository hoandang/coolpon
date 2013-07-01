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
