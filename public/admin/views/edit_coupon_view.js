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
