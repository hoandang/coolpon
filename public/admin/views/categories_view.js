// CATEGORIES LIST
var CategoriesView = Backbone.View.extend({
    el: '.page',
    render: function() {
        var that = this;
        var categories = new Categories();
        categories.fetch({
            success: function (coupons) {
                var template = _.template($('#categories').html(), 
                                          { categories: categories.models });
                that.$el.html(template);
            }
        });
    }
});

