define(['jquery', 'core/str', 'core/notification', 'core/config', 'core/ajax'], function ($, str, notification, mdlcfg, ajax) {

    return {
        init: function (apiUrl, quizId) {
            this.apiUrl = apiUrl;
            this.quizId = quizId;
            $('.call-api-one').on('click', function(e){
                this.callApiOne(e);
            }.bind(this));
        },
        callApiOne:function(e){
        // Call "local API"
        if (this.apiUrl === undefined || this.apiUrl === '') {
            // params and action could be retrieved from event caller data attributes
            // those attributes could be set via renderer and properly encoded via php
            // and then assigned via mustache templates.
            var params = {
              id: this.quizId,
              firstname: 'Mickey',
              lastname: 'Mouse'
            }

            var promises = ajax.call([
              { methodname: 'mod_automultiplechoice_call_amc', args: { action: 'hello', params:  JSON.stringify(params) } }
            ]);

            promises[0].done(function(response) {
              console.log('response from mod_automultiplechoice_call_api', response);
            }).fail(function(ex) {
               console.log('mod_automultiplechoice_call_api failed', ex);
            });
        } else {
            console.log('not implemented yet...');
            /*
            $.ajax({
                method: 'POST',
                url:  this.apiurl,
                data: { action: 'sayhello', data: {name: 'Donald', firstname: 'Duck'} }
              }).done(function(response) {
                  console.log('done', response);
              }).fail(function(jqXHR, textStatus) {
                  console.log(jqXHR, textStatus);
              });
            }
            */
        }
    }
  }
});
