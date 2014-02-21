// EDIT COUPON FORM
var EditCouponView = Backbone.View.extend({ 
    el: '.page',
    render: function(options) {
        var businesses = new Businesses();
        var machines   = new Machines();
        var categories = new Categories();
        var that   = this;

        if (options.id)
        {
            that.id = options.id;
            that.coupon = new Coupon({id: options.id});
            $.when(that.coupon.fetch(), categories.fetch(), businesses.fetch(), machines.fetch()).done(function() {
                var template = _.template($('#edit-coupon-template').html(), {
                    categories: categories.models,
                    businesses: businesses.models,
                    machines: machines.models,
                    coupon: that.coupon.toJSON()[0]
                });
                that.$el.html(template);
                $(".imgLiquidFill").imgLiquid();
                $('#description').wysihtml5();
            });
        }
        else
        {
            $.when(categories.fetch(), businesses.fetch(), machines.fetch()).done(function() {
                var template = _.template($('#edit-coupon-template').html(), {
                    categories: categories.models,
                    businesses: businesses.models,
                    machines: machines.models,
                    coupon: null
                });
                that.$el.html(template);
                $(".imgLiquidFill").imgLiquid();
                $('#description').wysihtml5();
            });
        }
    },
    events: {
        'click .delete-coupon': 'delete_coupon',
        'submit #edit-coupon-form': 'save_coupon'
    },
    delete_coupon: function(ev) {
        this.coupon.destroy({
            success: function() {
                console.log('Destroyed');
                router.navigate('#/coupons', {trigger: true});
            }
        });
        return false;
    },
    save_coupon: function(ev) {
        $(ev.currentTarget).ajaxSubmit({
            url: '/coupons',
            type: 'post',
            success: function(response) {
                if (response === 'Failed')
                    $('.control-group').addClass('error');
                else
                    router.navigate('#/coupons', {trigger: true});
            }
        });
        return false;
    }
});
