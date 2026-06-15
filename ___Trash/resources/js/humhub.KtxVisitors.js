humhub.module('KtxVisitors', function(module, require, $) {
    var client = require('client');

    var create = function(evt) {
        evt.preventDefault();
        console.log('Create button clicked:', this);
        client.post($(this).attr('href'), {}).then(function(response) {
            console.log('Create action response:', response);
            humhub.message.success('Create action successful!');
        }).catch(function(error) {
            humhub.message.error('Create action failed: ' + error);
            console.error('Create action failed:', error);
        });
    };

    var init = function() {
        console.log('KtxVisitors module activated');
    };

    module.export({
        initOnPjaxLoad: true, // Setze initOnPjaxLoad auf true
        init: init,
        create: create, // Exportiere den create Handler
        hello: hello
    });
});
