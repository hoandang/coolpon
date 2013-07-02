// EDIT BUSINESS FORM
var EditBusinessView = Backbone.View.extend({ 
    el: '.page',
    render: function(options) {
        var that = this;

        if (options.id) {
            that.business = new Business({ id: options.id });
            that.business.fetch({
                success: function(business) {
                    var template = _.template($('#edit-business-template').html(), 
                                              {business: business.attributes[0]});
                    that.$el.html(template);
                    $('#description').wysihtml5();
                }
            })
        }
        else {
            var template = _.template($('#edit-business-template').html(), {business: null});
            that.$el.html(template);
            $('#description').wysihtml5();
        }
    },
    events: {
        'submit .edit-business-form' : 'save_business',
        'click .delete-business' : 'delete_business'
    },
    save_business: function(ev) {
        var business_detail = $(ev.currentTarget).serializeObject();
        var business = new Business();
        business.save(business_detail, {
            success: function (business) {
                router.navigate('#/businesses', {trigger: true});
            },
            error: function() {
                $('.control-group').addClass('error').
                    append('<strong class="help-inline">Required</strong>');
            }
        });
        return false;
    },
    delete_business: function(ev) {
        this.business.destroy({
            success: function() {
                console.log('Destroyed');
                router.navigate('#/businesses', {trigger: true});
            }
        });
        return false;
    }
});

