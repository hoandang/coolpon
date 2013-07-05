// USER VIEW LIST
var UsersView = Backbone.View.extend({
    el: '.page',
    render: function() {
        var that = this;
        var users = new Users();
        users.fetch({
            success: function (users) {
                var template = _.template($('#users').html(), 
                                          { users: users.models });
                that.$el.html(template);
            }
        });
    }
});

