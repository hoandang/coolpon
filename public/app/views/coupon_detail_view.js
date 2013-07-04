var CouponDetailView = Backbone.View.extend({
    el: '.page',
    render: function(options) {
        var that       = this;
        var machine_id = options.machine_id;
        var coupon_id  = options.coupon_id;
        var coupon     = new Coupon({id: coupon_id });
        var categories = new Categories();

        $.when(coupon.fetch(),categories.fetch()).done(function() {
            var template = _.template($('#coupon').html(), {
                coupon: coupon.attributes[0],
                categories: categories.models
            });
            that.$el.html(template);
            load_facebook();
            load_twitter();

            $('.share-section').append('<script src="http://tjs.sjs.sinajs.cn/open/api/js/wb.js" type="text/javascript" charset="utf-8"></script>');

            // Autocomplete
            $('#coupon-detail-form-search input').typeahead({ 
                minLength: 3,
                source: function(query, process) {
                    return $.get('/machines/search', {q: query}, function(result) {
                        var resultList = result.map(function (item) {
                            return item.suburb;
                        });
                        return process($.unique(resultList));
                    });
                }
            });
        });
    },
    events: {
        'submit #coupon-detail-form-search' : 'search_machines'
    },
    search_machines: function(ev) {
        var machinesView = new MachinesView();
        var searchedMachines = new SearchedMachines({query: $('#coupon-detail-form-search input').val()});
        searchedMachines.fetch({
            success: function(searchedMachines) {
                var machinesView = new MachinesView();
                machinesView.render({searched_machines: searchedMachines});
            }
        });
        return false;
    }
});

function load_facebook()
{
    (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/zh_CN/all.js#xfbml=1&appId=670613199631241";
    fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
}

function load_twitter()
{
    !function(d,s,id){
var js,fjs=d.getElementsByTagName(s)[0];
    if(!d.getElementById(id)){js=d.createElement(s);
        js.id=id;js.src="https://platform.twitter.com/widgets.js";
        fjs.parentNode.insertBefore(js,fjs);}
    } (document,"script","twitter-wjs");
}
