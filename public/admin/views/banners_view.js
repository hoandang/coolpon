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
        'submit .banner-form': 'upload_image',
        'click #banner-new': 'create_banner',
        'click .icon-remove-sign': 'delete_banner'
    },
    delete_banner: function(ev) {
        var id = $(ev.currentTarget).data('id');
        var banner = new Banner({ id: id });
        banner.destroy({
            success: function() {
                console.log('Destroyed');
                router.navigate('#/banners', {trigger: true});
            }
        });
        return false;
    },
    upload_image: function (ev) {
        var id = $(ev.currentTarget).serializeObject().id;
        if (id)
        { 
            console.log('update');
            $(ev.currentTarget).ajaxSubmit({
                url: '/banners/' + id,
                type: 'post',
                success: function(response) {
                    router.navigate('#/banners', {trigger: true});
                }
            });
        }
        else
        {
            $(ev.currentTarget).ajaxSubmit({
                url: '/banners',
                type: 'post',
                success: function(response) {
                    router.navigate('#/banners', {trigger: true});
                }
            });
        }
        return false;
    },
    create_banner: function(ev) {
        $('#banner-list').prepend(banner_item());
        $(".imgLiquidFill").imgLiquid();
        return false;
    }
});

function banner_item()
{
   return '<li>' +
            '<div class="fileupload fileupload-new " data-provides="fileupload">' +
                '<div class="fileupload-new thumbnail imgLiquidFill imgLiquid" style="width: 260px; height: 180px; line-height: 20px;">' +
                    '<img src="assets/no-image.jpg"/>' + 
                '</div>' +
                '<div class="fileupload-preview fileupload-exists thumbnail" style="width: 260px; height: 180px; line-height: 20px;"></div>' +
                '<form enctype="multipart/form-data" class="banner-form">' +
                    '<div class="center">' +
                        '<span class="btn btn-file btn-warning">' +
                            '<i class="icon-white icon-plus"></i>' +
                            '<span class="fileupload-new">Select Image</span> ' +
                            '<span class="fileupload-exists">Change</span> ' +
                            '<input type="file" id="fileupload" name="image"> ' +
                        '</span> ' +
                        '<a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a> ' +
                        '<button type="submit" class="btn-submit btn fileupload-exists btn-danger">Upload</button>' +
                    '</div>' +
                '</form>' +
            '</div>' +
        '</li>';
}
