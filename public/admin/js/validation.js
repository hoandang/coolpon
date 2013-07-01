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
