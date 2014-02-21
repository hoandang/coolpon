var EditBannerView = Backbone.View.extend({
    el: '.page',
    render: function(options) {
        var that = this;
        var banner = new Banner({ id: options.id});
        banner.fetch({ 
            success: function (banner) {
                var template = _.template($('#edit-banner-view').html(), {
                    banner: banner.attributes[0]
                });
                that.$el.html(template);
            }
        });
    }
});
