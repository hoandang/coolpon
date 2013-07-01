// EDIT MACHINE FORM
var EditMachineView = Backbone.View.extend({
    el: '.page',
    render: function(options) {
        var that = this;
        if (options.id)
        {
            that.machine = new Machine({ id: options.id });
            that.machine.fetch({
                success: function() {
                    var template = _.template($('#edit-machine-template').html(), {machine: that.machine.toJSON()[0]});
                    that.$el.html(template);
                    location_autocomplete();
                }
            });
        }
        else
        {
            var template = _.template($('#edit-machine-template').html(), {machine: null});
            that.$el.html(template);
            location_autocomplete();
            $('#machine-category').tokenInput('/categories/search', {
                theme: 'facebook'
            });
        }
    },
    events: {
        'submit .edit-machine-form' : 'save_machine',
        'click .delete-machine': 'delete_machine'
    },
    delete_machine: function(ev) {
        this.machine.destroy({
            success: function() {
                console.log('Destroyed');
                router.navigate('#/machines', {trigger: true});
            }
        });
        return false;
    },
    save_machine: function(ev) {
        var machine_detail = $(ev.currentTarget).serializeObject();
        if (is_validated(machine_detail))
        {
            setup_errors();

            var geocoder = new google.maps.Geocoder();
            var address  = machine_detail.address + ', ' + machine_detail.suburb; 
            geocoder.geocode({ 'address': address }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK)
                {
                    var result = results[0].formatted_address;
                    if (address.toUpperCase() != result.toUpperCase())
                    {
                        var num_commas = result.match(/,/g).length;
                        var address_number = result.split(' ');
                        if (num_commas == 2 && !isNaN(address_number[0]))
                        {
                            console.log('Did you mean: ' + result.toUpperCase());
                            indicate_location_errors();
                        }
                        else
                        {
                            console.log('The suburb and location are not valid or existed.');
                            indicate_location_errors();
                        }
                    }
                    else
                    {
                        console.log('Valid');
                        var new_machine = new Machine();
                        new_machine.save(machine_detail, {
                            success: function (new_machine) {
                                router.navigate('#/machines', {trigger: true});
                            }
                        });
                    }
                }
            });
        }
        else 
        {
            setup_errors();
            indicate_errors(machine_detail);
        }
        return false;
    }
});

function location_autocomplete() 
{
    $('p#machine-suburb input').autocomplete({ 
        minLength: 3,
        source: function (request, response) {
            $.get('/locations/search?q=' + request.term, function(data) {
                response($.map(data.slice(0, 10), function (item) {
                    return {
                        label: item.location + ' ' + item.state + ' ' + item.postcode + ', AUSTRALIA',
                        value: item.location + ' ' + item.state + ' ' + item.postcode + ', AUSTRALIA',
                    }
                }));
            });
        }
    });
}

function indicate_location_errors()
{
    $('.edit-machine-form #machine-suburb').addClass('error');
    $('.edit-machine-form #machine-address').addClass('error');
}

function is_name_validated(machine)
{
    return machine.name !== '';
}
function is_suburb_validated(machine)
{
    return machine.suburb !== '';
}
function is_address_validated(machine)
{
    return machine.address !== '';
}
function is_validated(machine)
{
    return is_suburb_validated(machine)
        && is_address_validated(machine)
        && is_name_validated(machine);
}
function error_msg(msg)
{
    return '<strong class="help-inline">' + msg + '</strong>';
}
function setup_errors()
{
    $('.help-inline').remove();
    $('.edit-machine-form #machine-name').removeClass('error');
    $('.edit-machine-form #machine-suburb').removeClass('error');
    $('.edit-machine-form #machine-address').removeClass('error');
}
function indicate_errors(machine)
{
    if (!is_name_validated(machine)) {
        $('.edit-machine-form #machine-name').addClass('error').
            append(error_msg('Required'));
    }
    if (!is_suburb_validated(machine)) {
        $('.edit-machine-form #machine-suburb').addClass('error').
            append(error_msg('Required'));
    }
    if (!is_address_validated(machine)) {
        $('.edit-machine-form #machine-address').addClass('error').
            append(error_msg('Required'));
    }
}
