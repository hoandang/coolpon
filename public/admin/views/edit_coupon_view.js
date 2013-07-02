// EDIT COUPON FORM
var EditCouponView = Backbone.View.extend({ 
    el: '.page',
    render: function(options) {
        var businesses = new Businesses();
        var machines   = new Machines();
        var that   = this;

        if (options.id)
        {
            that.coupon = new Coupon({id: options.id});

            $.when(that.coupon.fetch(), businesses.fetch(), machines.fetch()).done(function() {
                var template = _.template($('#edit-coupon-template').html(), {
                    businesses: businesses.models,
                    machines: machines.models,
                    coupon: that.coupon.toJSON()[0]
                });
                that.$el.html(template);
                $('#description').wysihtml5();

            });
        }
        else
        {
            $.when(businesses.fetch(), machines.fetch()).done(function() {
                var template = _.template($('#edit-coupon-template').html(), {
                    businesses: businesses.models,
                    machines: machines.models,
                    coupon: null
                });
                that.$el.html(template);
                $('#description').wysihtml5();
            });
        }
    },
    events: {
        'click .delete-coupon': 'delete_coupon'
    },
    delete_coupon: function(ev) {
        this.coupon.destroy({
            success: function() {
                console.log('Destroyed');
                router.navigate('#/coupons', {trigger: true});
            }
        });
        return false;
    }
});
