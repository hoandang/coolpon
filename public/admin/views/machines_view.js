// MACHINE LIST
var MachinesView = Backbone.View.extend({
    el: '.page',
    render: function() {
        var that = this;
        var machines = new Machines();
        machines.fetch({
            success: function (machines) {
                var template = _.template($('#machines').html(), 
                                          { machines: machines.models });
                that.$el.html(template);
            }
        });
    }
});
