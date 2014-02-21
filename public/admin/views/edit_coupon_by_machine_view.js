// EDIT COUPON BY MACHINE FORM
var EditCouponByMachineView = Backbone.View.extend({ 
    el: '.page',
    render: function(options) {

        var that = this;
        that.current_machine = new Machine({ id: options.machine_id });
        var businesses      = new Businesses();

        if (options.coupon_id)
        {
            that.coupon = new Coupon({ id: options.coupon_id });
            $.when(that.coupon.fetch(), businesses.fetch(), that.current_machine.fetch()).done(function() {
                var template = _.template(
                    $('#edit-coupon-by-machine-template').html(), {
                        current_machine: that.current_machine.attributes[0],
                        businesses: businesses.models,
                        coupon: that.coupon.attributes[0]
                    });
                that.$el.html(template);
                $('#description').wysihtml5();
            });
        }
        else
        {
            $.when(businesses.fetch(), that.current_machine.fetch()).done(function() {
                var template = _.template(
                    $('#edit-coupon-by-machine-template').html(), {
                        current_machine: that.current_machine.attributes[0],
                        businesses: businesses.models,
                        coupon: null
                    });
                that.$el.html(template);
                $('#description').wysihtml5();
            });
        }
    },
    events: {
        'click .delete-coupon-machine': 'delete_coupon_machine'
    },
    delete_coupon_machine: function(ev) {
        var current_machine_id = this.current_machine.attributes[0].id;
        this.coupon.destroy({
            success: function() {
                console.log('Destroyed');
                router.navigate('#/machines/' + current_machine_id, {trigger: true});
            }
        });
        return false;
    }
});
