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

