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

