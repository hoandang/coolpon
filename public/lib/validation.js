function is_name_validated(machine)
{
    return machine.name !== '';
}
function is_suburb_validated(machine)
{
    //var geocoder = new google.maps.Geocoder();
    //var address  = machine.address + ', ' + machine.suburb; 
    //geocoder.geocode({ 'address': address }, function(results, status) {
        //if (status == google.maps.GeocoderStatus.OK)
        //{
            //var result = results[0].formatted_address;
            //if (address.toUpperCase() != result.toUpperCase())
                //console.log('Did you mean: ' + result.toUpperCase());
            //else
                //console.log('Valid');
        //}
    //});
    return machine.suburb !== '';
}
function is_location_validated(machine)
{
    var geocoder = new google.maps.Geocoder();
    var address  = machine.address + ', ' + machine.suburb; 
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
                    $('.edit-machine-form #machine-suburb').addClass('error');
                    $('.edit-machine-form #machine-address').addClass('error');
                }
                else
                {
                    console.log('The address is not valid or existed');
                    $('.edit-machine-form #machine-suburb').addClass('error');
                    $('.edit-machine-form #machine-address').addClass('error');
                }
            }
            else
            {
                console.log('Valid');
            }
        }
    });
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
    setup_errors();
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
