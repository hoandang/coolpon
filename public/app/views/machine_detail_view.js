var MachineDetailView = Backbone.View.extend({
    el: '.page',
    render: function(options) {
        var that       = this;
        var machine_id = options.id;
        var location   = options.location;
        var machine    = new Machine({id: machine_id });
        var searched_machines = new SearchedMachines({query: location });
        var categories        = new Categories();

        $.when(searched_machines.fetch(), machine.fetch(), categories.fetch()).done(function() {
            var template = _.template($('#coupons').html(), {
                searched_machines: searched_machines.models,
                machine: machine.attributes[0],
                categories: categories.models
            });
            that.$el.html(template);
            $('.coupon-name').condense( { condensedLength: 40 });
            // Autocomplete
            $('#machine-detail-form-search input').typeahead({ 
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
        'submit #machine-detail-form-search' : 'search_machines'
    },
    search_machines: function(ev) {
        var machinesView = new MachinesView();
        var searchedMachines = new SearchedMachines({query: $('#machine-detail-form-search input').val()});
        searchedMachines.fetch({
            success: function(searchedMachines) {
                var machinesView = new MachinesView();
                machinesView.render({searched_machines: searchedMachines});
            }
        });
        return false;
    }
});
