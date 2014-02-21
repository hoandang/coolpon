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

