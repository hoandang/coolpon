var CategoryDetailView = Backbone.View.extend({
    el: '.page',
    render: function(options) {
        var that       = this;
        var category = new Category({id: options.id });
        var menu_categories = new Categories();

        $.when(category.fetch(), menu_categories.fetch()).done(function() {
            var template = _.template($('#category-detail').html(), { 
                category: category.attributes[0],
                menu_categories: menu_categories.models
            });
            that.$el.html(template);

            $('.coupon-name').condense( { condensedLength: 40 });

            // Autocomplete
            $('#category-detail-form-search input').typeahead({ 
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
        'submit #category-detail-form-search' : 'search_machines'
    },
    search_machines: function(ev) {
        var machinesView = new MachinesView();
        var searchedMachines = new SearchedMachines({query: $('#category-detail-form-search input').val()});
        searchedMachines.fetch({
            success: function(searchedMachines) {
                var machinesView = new MachinesView();
                machinesView.render({searched_machines: searchedMachines});
            }
        });
        return false;
    }
});
