// EDIT BUSINESS FORM
var EditBusinessView = Backbone.View.extend({ 
    el: '.page',
    render: function() {
        var template = _.template($('#edit-business-template').html(), {});
        this.$el.html(template);
    },
    events: {
        'submit .edit-business-form' : 'save_business'
    },
    save_business: function(ev) {
        var business_detail = $(ev.currentTarget).serializeObject();
        var business = new Business();
        business.save(business_detail, {
            success: function (business) {
                router.navigate('#/businesses', {trigger: true});
            }
        });
        return false;
    }
});

