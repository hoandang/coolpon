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
var Categories = Backbone.Collection.extend({
    url: '/categories'
});

var Machines = Backbone.Collection.extend({
    url: '/machines'
});

var Businesses = Backbone.Collection.extend({
    url: '/businesses'
});

var Coupons = Backbone.Collection.extend({ 
    url: '/coupons'
});
var CouponsByMachine = Backbone.Collection.extend({ 
    initialize: function(options) {
        this.id = options.id;
    },
    url: function() {
        return '/machines/' + this.id + '/coupons';
    }
});

var CategoriesByMachine = Backbone.Collection.extend({ 
    initialize: function(options) {
        this.id = options.id;
    },
    url: function() {
        return '/machines/' + this.id + '/categories';
    }
});

var PostCodes = Backbone.Collection.extend({ 
    initialize: function(options) {
        this.query = options.query;
    },
    url: function() {
        return '/locations/search?q=' + this.query;
    }
});

//---------- MODELS -----------------
var Machine = Backbone.Model.extend({
    urlRoot: '/machines'
});
var Business = Backbone.Model.extend({
    urlRoot: '/businesses'
});
var Coupon = Backbone.Model.extend({
    urlRoot: '/coupons'
});
var Category = Backbone.Model.extend({
    urlRoot: '/categories'
});
