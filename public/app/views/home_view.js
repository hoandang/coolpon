//--------------- VIEWS --------------------
var HomeView = Backbone.View.extend({
    el: '.page',
    render: function() {
        var template = _.template($('#home').html(), {});
        this.$el.html(template);

        //var banners = new Banners();
        //banners.fetch({
            //success: function(banners) {
                //var template = _.template($('#banners').html(), {
                    //banners: banners.models
                //});
                //$('body').find('#banner-left').html(template);
            //}
        //})

        // Autocomplete
        $('#homepage-form-search input').typeahead({ 
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
    },
    events: {
        'submit #homepage-form-search' : 'search_machines'
    },
    search_machines: function(ev) {
        var machinesView = new MachinesView();
        var searchedMachines = new SearchedMachines({query: $('#homepage-form-search input').val()});
        searchedMachines.fetch({
            success: function(searchedMachines) {
                var machinesView = new MachinesView();
                machinesView.render({searched_machines: searchedMachines});
            },
            error: function() {
                $('.error').html('Machine Not Found');
            }
        });
        return false;
    }
});
