// Filter url caller, instead of giving full url, 
// we need only the url's suffix. Eg: /machines
$.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
    options.url = 'http://localhost:8888' + options.url;
});

// Serialise input form into JSON Object
$.fn.serializeObject = function() {
  var o = {};
  var a = this.serializeArray();
  $.each(a, function() {
      if (o[this.name] !== undefined) {
          if (!o[this.name].push) {
              o[this.name] = [o[this.name]];
          }
          o[this.name].push(this.value || '');
      } else {
          o[this.name] = this.value || '';
      }
  });
  return o;
};

//---------- COLLECTIONS --------------------------
var SearchedMachines = Backbone.Collection.extend({
    initialize: function(models, options) {
        this.post_code = options.post_code;
    },
    url: function() {
        return '/machines?post_code=' + this.post_code;
    }
});

var MachinesCoupons = Backbone.Collection.extend({
    initialize: function(models, options) {
        this.id = options.id;
    },
    url: function() {
        return '/machines/' + this.id + '/coupons';
    }
});

//---------- MODELS -----------------
var Machine = Backbone.Model.extend({
    urlRoot: '/machines'
});

//--------------- VIEWS --------------------
var HomeView = Backbone.View.extend({
    el: '.page',
    render: function() {
        var template = _.template($('#home').html(), {});
        this.$el.html(template);
    },
    events: {
        'submit .form-search' : 'search_machines'
    },
    search_machines: function(ev) {

        //------ DIRTY CODE, WILL REFACT IT ----------------------------------------------------------
        var post_code              = $("#post_code").val();
        var searched_machines      = new SearchedMachines([], { post_code: post_code });
        var searched_machines_list = $("#searched-machines");
        var error                  = $('.error');
        var map_canvas             = $('#map-canvas');
        var locations              = [];

        searched_machines.fetch({
            success: function (searched_machines) {
                error.empty();
                searched_machines_list.empty();
                map_canvas.empty().css('background-color', '');

                var searched_machines_models = searched_machines.models;
                if (searched_machines_models.length > 0)
                {
                    $.each(searched_machines_models, function() {
                        var machine   = this;
                        locations.push(this.attributes.address + ' Sydney, NSW');
                        searched_machines_list.append('<li><a href="#/machines/' + this.attributes.id + '?post_code=' + post_code + '">' 
                                                      + this.attributes.name + '</a></li>');
                    });
                    code_address(locations);
                }
                else
                {
                    $('.error').fadeIn("slow").html('Machine not found');
                }
            }
        });
        //-------------------------------------------------------------------------------------------

        return false;
    }
});

var MachineView = Backbone.View.extend({
    el: '.page',
    render: function(options) {
        var coupons           = new MachinesCoupons([], { id: options.id });
        var searched_machines = new SearchedMachines([], { post_code: options.post_code });
        var current_machine   = new Machine({ id: options.id });
        var that              = this;

        // Fetch multiple backbone collections synchronously
        $.when(searched_machines.fetch(), coupons.fetch(), current_machine.fetch()).done(function() {
            var template = _.template($('#coupons').html(), {
                coupons: coupons.models, 
                searched_machines: searched_machines.models,
                current_machine: current_machine.attributes[0]
            });
            console.log(template);
            that.$el.html(template);
        });
    }
});

//------------- ROUTER ---------------------
var Router = Backbone.Router.extend({
    routes: {
        '': 'home',
        'machines/:id?post_code=:post_code' : 'machine_detail'
    }
});

var router = new Router();
router.on('route:home', function() {
    var homeView = new HomeView();
    homeView.render();
});
router.on('route:machine_detail', function(id, post_code) {
    var machineView = new MachineView();
    machineView.render({ id: id, post_code: post_code });
});

Backbone.history.start();

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

function code_address(locations)
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
