/**
 * Created by vfahrenholz on 13.03.16.
 */

function toggleRestoreSettings(event, scope) {
    Event.stop(event);
    var id = scope.getAttribute('data-id');
    var status = scope.getAttribute('data-status');
    var url = scope.getAttribute('href');

    new Ajax.Request(url, {
        method: 'post',
        parameters: {id: id, oldStatus: status},
        onSuccess: function(transport) {
            $$('.search-field form').first().submit();
        }
    });


}