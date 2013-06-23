$.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
    options.url = 'http://localhost:8888' + options.url;
});

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

$('#form-search').submit(function(ev) {
    var input             = $(this).serializeObject();
    var searched_machines = $("#searched-machines");
    var locations         = [];

    searched_machines.empty();

    $.getJSON("/machines?post_code=" + input.post_code, function(machines) {
        $.each(machines, function() {
            var machine   = this;
            locations.push(this.address + ' Sydney, NSW');
            searched_machines.append('<li><a href="#">' + this.name + '</a></li>');
        });
        code_address(locations);
    });
    this.reset();
    ev.preventDefault();
});

var map, geocoder;
function initialize() 
{
    geocoder = new google.maps.Geocoder();
    // Map attributes options
    var opts = {
        center   : new google.maps.LatLng(-33.890542, 151.274856), // Sydney by default
        zoom     : 10,
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
