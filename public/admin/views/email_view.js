// EMAIL
var EmailView = Backbone.View.extend({
    el: '.page',
    render: function(options) {
        var template = _.template($('#email-view').html(), { to_email: options.email});
        this.$el.html(template);
        $('#email_body').wysihtml5();
    },
    events: {
        'submit #email-form': 'send_email'
    },
    send_email: function(ev) {
        var from_email = $('#from_email').val();
        var to_email   = $('#to_email').val();
        var subject    = $('#email_subject').val();
        var content    = $('#email_body').val();

        console.log(from_email);

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
                alert(data);
            }
        });

        return false;
    }
});

