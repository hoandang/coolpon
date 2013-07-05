var BannersView = Backbone.View.extend({
    el: '#banner-left',
    render: function() {
        var that = this;
        var banners = new Banners();
        banners.fetch({
            success: function(banners) {
                var template = _.template($('#banners').html(), {
                    banners: banners.models
                });
                that.$el.html(template);
            }
        })
    }
});
