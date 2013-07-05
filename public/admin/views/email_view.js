// EMAIL
var EmailView = Backbone.View.extend({
    el: '.page',
    render: function(options) {
        var template;
        var that = this;

        if (options.email === 'all')
        {
            that.email = null;
            that.users = new Users();
            that.users.fetch({
                success: function (users) {
                    var user_emails = $.map(users.models, function(user) {
                        return user.attributes.email;
                    }).join(', ');
                    template = _.template($('#email-view').html(), { to_email: user_emails});
                    that.$el.html(template);
                }
            });
        }
        else
        {
            that.users = null;
            that.email = options.email;
            template = _.template($('#email-view').html(), { to_email: options.email});
            that.$el.html(template);
        }

        $('#email_body').wysihtml5();
    },
    events: {
        'submit #email-form': 'send_email'
    },
    send_email: function(ev) {
        var from_email = $('#from_email').val();
        var subject    = $('#email_subject').val();
        var content    = $('#email_body').val();
        var to_email;

        if (this.email)
            to_email = this.email;
        else
            to_email = $.map(this.users.models, function(user) { return user.attributes.email; });

        $.ajax({
            type: "post",
            url: '/email',
            data: { 
                from_email: from_email,
                to_email:   to_email,
                subject:    subject,
                content:    content
            },
            success: function(data) {
                console.log(data);
            }
        });

        return false;
    }
});

