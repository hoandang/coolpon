var BannersView = Backbone.View.extend({
    el: '#banner-left',
    render: function() {
        var that = this;
        var banners = new Banners();
        banners.fetch({
            success: function(banners) {
                var template = _.template($('#left-banners').html(), {
                    banners: _.first(banners.models, 3)
                });
                that.$el.html(template);

                var template = _.template($('#right-banners').html(), {
                    banners: _.rest(banners.models, 3)
                });
                $('#banner-right').html(template);
            }
        })
    }
});
