var MachinesView = Backbone.View.extend({
    el: '.page',
    render: function(options) {
        var that = this;
        var searched_machines = options.searched_machines;
        var categories        = new Categories();

        $.when(searched_machines.fetch(), categories.fetch()).done(function() {
            var template = _.template($('#searched-machines').html(), {
                searched_machines: searched_machines.models,
                categories:        categories.models
            });
            that.$el.html(template);

            $('#custom-form-search input').typeahead({ 
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
            load_map(locations(searched_machines.models));
        });
    },
    events: {
        'submit #custom-form-search' : 'search_machines'
    },
    search_machines: function(ev) {
        var machinesView = new MachinesView();
        var searchedMachines = new SearchedMachines({query: $('#custom-form-search input').val()});
        searchedMachines.fetch({
            success: function(searchedMachines) {
                var machinesView = new MachinesView();
                machinesView.render({searched_machines: searchedMachines});
            }
        });
        return false;
    }
});

function locations(machines)
{
    return $.map(machines, function(machine) {
        return machine.get('address') + ', ' + machine.get('suburb');
    });
}

// ---------- INVOKE GOOGLE MAP WITH MULTIPLE MARKERS BY GIVEN MULTIPLE LOCATION --------------
var map, geocoder;
function initialize() 
{
    geocoder = new google.maps.Geocoder();
    // Map attributes options
    var opts = {
        center   : new google.maps.LatLng(-33.890542, 151.274856), // Sydney by default
        zoom     : 14,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    // Parse map to the DOM
    map = new google.maps.Map(document.getElementById('map-canvas'), opts);
}

function load_map(locations)
{
    initialize();
    var infowindow = new google.maps.InfoWindow();

    for (var i = 0; i < locations.length; i++) 
    {
        geocoder.geocode({ 'address': locations[i] }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK)
            {
                map.setCenter(results[0].geometry.location);
                var marker = new google.maps.Marker({
                    map: map,
                    position: results[0].geometry.location
                });
            }
            else
                console.log('Geocode was not successful for the following reason ' + status);
        });
    }
}
