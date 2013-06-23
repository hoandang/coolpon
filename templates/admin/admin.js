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

var Machines = Backbone.Collection.extend({
    url: '/machines'
});
var Machine = Backbone.Model.extend({
    url: '/machines'
});

var MachinesView = Backbone.View.extend({
    el: '.page',
    render: function() {
        var that = this;
        var machines = new Machines();
        machines.fetch({
            success: function (machines) {
                var template = _.template($('#machines-list').html(), 
                                          { machines: machines.models });
                that.$el.html(template);
            }
        });
    }
});

var EditMachineView = Backbone.View.extend({
    el: '.page',
    render: function() {
        var template = _.template($('#edit-machine-template').html(), {});
        this.$el.html(template);
    },
    events: {
        'submit .edit-machine-form' : 'save_machine'
    },
    save_machine: function(ev) {
        var machine_detail = $(ev.currentTarget).serializeObject();
        var machine = new Machine();
        machine.save(machine_detail, {
            success: function (machine) {
                router.navigate('', {trigger: true});
            }
        });
        return false;
    }
})

var Router = Backbone.Router.extend({
    routes: {
        '': 'home',
        'new': 'editMachine'
    }
});

var router = new Router();
router.on('route:home', function() {
    var machinesView = new MachinesView();
    machinesView.render();
});
router.on('route:editMachine', function() {
    var editMachineView = new EditMachineView();
    editMachineView.render();
});

Backbone.history.start();
