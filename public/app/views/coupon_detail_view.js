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
            // Autocomplete
            $('#coupon-detail-form-search input').typeahead({ 
                minLength: 3,
                source: function(query, process) {
                    return $.get('/machines/search_location', {q: query}, function(result) {
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
