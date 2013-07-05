var BannersView = Backbone.View.extend({ 
    el: '.page',
    render: function() {
        var banners = new Banners();
        var that = this;
        banners.fetch({ 
            success: function(banners) {
                var template = _.template($('#banners-view').html(), {
                    banners: banners.models
                });
                that.$el.html(template);
                $(".imgLiquidFill").imgLiquid();
            }
        });
    },
    events: {
        'submit .banner-form': 'upload_image'
    },
    upload_image: function (ev) {
        var id = $(ev.currentTarget).serializeObject().id;
        $(ev.currentTarget).ajaxSubmit({
            url: '/banners/' + id,
            type: 'PUT',
            success: function(response) {
                router.navigate('#/banners', {trigger: true});
            }
        });
        return false;
    }
});
