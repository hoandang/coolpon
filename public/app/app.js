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
    initialize: function(options) {
        this.query = options.query;
    },
    url: function() {
        return '/machines/search?q=' + this.query;
    }
});

var MachinesCoupons = Backbone.Collection.extend({
    initialize: function(options) {
        this.id = options.id;
    },
    url: function() {
        return '/machines/' + this.id + '/coupons';
    }
});

var Categories = Backbone.Collection.extend({
    url: '/categories'
});

//---------- MODELS -----------------
var Machine = Backbone.Model.extend({
    urlRoot: '/machines'
});
 
var Coupon = Backbone.Model.extend({
    urlRoot: '/coupons'
});
var Business = Backbone.Model.extend({
    urlRoot: '/businesses'
});
var Category = Backbone.Model.extend({
    urlRoot: '/categories'
});
