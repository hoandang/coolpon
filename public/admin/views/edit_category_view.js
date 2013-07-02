// EDIT CATEGORY FORM
var EditCategoryView = Backbone.View.extend({ 
    el: '.page',
    render: function(options) {
        var that = this;
        if (options.id)
        {
            that.category = new Category({id: options.id});
            that.category.fetch({
                success: function(category) {
                    var template = _.template($('#edit-category-template').html(), 
                                              {category: category.toJSON()[0]});
                    that.$el.html(template);
                }
            });
        }
        else
        {
            var template = _.template($('#edit-category-template').html(), 
                                      {category: null});
            this.$el.html(template);
        }
    },
    events: {
        'submit .edit-category-form' : 'save_category',
        'click .delete-category': 'delete_category'
    },
    delete_category: function(ev) {
        this.category.destroy({
            success: function() {
                console.log('Destroyed');
                router.navigate('', {trigger: true});
            }
        });
        return false;
    },
    save_category: function(ev) {
        var category_detail = $(ev.currentTarget).serializeObject();
        var new_category = new Category();
        new_category.save(category_detail, {
            success: function (new_category) {
                router.navigate('', {trigger: true});
            }, 
            error: function() {
                $(ev.currentTarget).children('.control-group').
                    addClass('error').
                    append('<strong class="help-inline">Required</strong>');
            }
        });
        return false;
    }
});
