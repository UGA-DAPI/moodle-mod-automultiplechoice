define(['jquery', 'core/str', 'core/notification', 'core/config', 'core/ajax'], function ($, str, notification, mdlcfg, ajax) {

    return {
          init: function (apiUrl, quizId) {
              this.apiUrl = apiUrl;
              this.quizId = quizId;
              $('.call-api-one').on('click', function(e){
                  this.callApiOne(e);
              }.bind(this));

              $('.btn-call-amc-api').on('click', function(e){
                  this.btnCallAmcApi(e);
              }.bind(this));
          },
          callApiOne: function(e) {

            // Call "local API"
            if (!this.apiUrl || this.apiUrl === undefined || this.apiUrl === '') {
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
                var testurl = 'https://en.wikipedia.org/api/rest_v1/feed/announcements';
                $.ajax({
                    method: 'GET',
                    url:  testurl, //this.apiurl,
                    //data: { action: 'sayhello', data: {name: 'Donald', firstname: 'Duck'} }
                    data: {}
                  }).done(function(response) {
                      console.log('done', response);
                  }).fail(function(jqXHR, textStatus) {
                      console.log(jqXHR, textStatus);
                  });
            }

          },
          btnCallAmcApi: function(e) {
            // get data from action caller
            var action = $(e.target).data('action');
            var spinner = $(e.target).data('spinner-id');
            var params = $(e.target).data('params');
            this.callAmcApi(action, params, spinner, this.apiUrl);

          },
          callAmcApi: function (action, params, spinner, url) {
              if(!action || !params || !spinner) {
                console.log('one or several mandatory parameter(s) missing.');
                return false;
              }
              console.log(action, params);
              $('#' + spinner).show();
              if(!url) {
                  var promises = ajax.call([
                    { methodname: 'mod_automultiplechoice_call_amc', args: { action: action, params:  JSON.stringify(params) } }
                  ]);

                  promises[0].done(function(response) {
                    console.log('response from mod_automultiplechoice_call_api', response);
                    $('#' + spinner).hide();
                  }).fail(function(ex) {
                     console.log('mod_automultiplechoice_call_api failed', ex);
                     $('#' + spinner).hide();
                  });
              } else {
                  var testurl = 'https://en.wikipedia.org/api/rest_v1/feed/announcements';
                  $.ajax({
                      method: 'GET', // 'POST'
                      url:  testurl, //url,
                      //data: { action: action, data: params }
                      data: {}
                  }).done(function(response) {
                        $('#' + spinner).hide();
                        console.log('done', response);
                  }).fail(function(jqXHR, textStatus) {
                        console.log(jqXHR, textStatus);
                        $('#' + spinner).hide();
                  });
              }

          }
      }
});
