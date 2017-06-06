function Admin_JsWebFormsResultModal(title, url) {
    var win = new Window({
        className: 'magento',
        title: title,
        url: url,
        width: 820,
        height: 473,
        zIndex: 10000
    });
    win.showCenter(true);
}

function setResultStatus(el, url) {
    // hide action buttons
    el.up().select('.grid-button-action').invoke('hide');
    el.up().select('.request-progress').invoke('show');
    new Ajax.Request(url, {
        onSuccess: function (transport){
            el.up().select('.request-progress').invoke('hide');

            var response = transport.responseText.evalJSON(true);
            var indicator = el.up().select('.grid-status')[0];

            indicator.update(response.text);
            indicator.removeClassName('approved');
            indicator.removeClassName('notapproved');
            indicator.removeClassName('pending');

            switch(response.status){
                case -1:
                    indicator.addClassName('notapproved');
                    el.up().select('.approve').invoke('show');
                    break;
                case 1:
                    indicator.addClassName('approved');
                    el.up().select('.reject').invoke('show');
                    el.up().select('.complete').invoke('show');
                    break;
                case 2:
                    indicator.addClassName('approved');
                    break;
            }
        },
        onFailure: function (transport){
            el.up().select('.grid-button-action').invoke('show');
            el.up().select('.request-progress').invoke('hide');
            alert('Error occured during request.');
        }
    });
}