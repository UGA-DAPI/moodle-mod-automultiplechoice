define(['jquery', 'core/str', 'core/notification', 'core/config', 'core/ajax', 'core/templates', 'core/modal_factory'], function ($, str, notification, mdlcfg, ajax, templates, ModalFactory) {

    return {
          init: function (apiUrl, quizId) {
              this.apiUrl = apiUrl;
              this.quizId = quizId;
              // simulate a call to the api at page load
              this.callAmcApi('hello', {id:4, firstname: 'Mickey', lastname:'Mouse'}, 'hello-ajax-spinner', this.apiUrl, 'ajax-response');
              $('.btn-call-amc-api').on('click', function(e){
                  this.btnCallAmcApi(e);
              }.bind(this));
          },
          btnCallAmcApi: function(e) {
            // get data from action caller
            var action = $(e.target).data('action');
            var spinner = $(e.target).data('spinner-id');
            var params = $(e.target).data('params');
            this.callAmcApi(action, params, spinner, this.apiUrl);
          },
          /**
           * [description]
           * @param  string action  the action to do
           * @param  object params  parameters for the action
           * @param  string spinner id of the spinner to show / hide
           * @param  string url     url of the api
           * @param  string target  id of container to populate with response data
           * @return {[type]}         [description]
           */
          callAmcApi: function (action, params, spinner, url, target) {
              if(!action || !params || !spinner) {
                console.log('one or several mandatory parameter(s) missing.');
                return false;
              }
              console.log(action, params);
              //$('#' + spinner).show();
              //$('#ajax-modal').modal('show');
              var modalpromise = ModalFactory.create({
                title: 'Please wait...',
                body: templates.render('mod_automultiplechoice/ajaxmodal', {})
              })
              .done(function(modal) {
                // Do what you want with your new modal.
                this.modal = modal;
                //console.log('modal', this.modal);

              }.bind(this));

              modalpromise.then(function(){
                if(!url) {
                    this.modal.show();
                    var promises = ajax.call([
                      { methodname: 'mod_automultiplechoice_call_amc', args: { action: action, params:  JSON.stringify(params) } }
                    ]);

                    promises[0].done(function(response) {
                      console.log('response from mod_automultiplechoice_call_api', response);
                      // get object from json response
                      var result = JSON.parse(response);
                      // second param of render method is the template context...
                      // This will be the context for our template. So {{name}} in the template will resolve to "Tweety bird".
                      // var context = { name: 'Tweety bird', intelligence: 2 };
                      // template should be a parameter
                      var templatepromise = templates.render('mod_automultiplechoice/dashboard', {});
                      templatepromise.then(function(html, js){
                        console.log('render done');
                        if (target) {
                           var targetElem = '#' + target;
                           templates.appendNodeContents(targetElem, 'from template render append node contents: ' + result.data, '');
                        }
                        this.modal.hide();
                      }.bind(this)).fail(function(ex){
                        console.log('fail', ex);
                      }.bind(this));
                      //$('#ajax-modal').hide();
                      console.log('should hide modal', this.modal);

                    }.bind(this)).fail(function(ex) {
                       console.log('mod_automultiplechoice_call_api failed', ex);
                       //$('#ajax-modal').hide();
                    }.bind(this));
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
                          if(target){
                            $('#' + target).html(response.data);
                          }
                    }).fail(function(jqXHR, textStatus) {
                          console.log(jqXHR, textStatus);
                          $('#' + spinner).hide();
                    });
                }
              }.bind(this));


          }
      }
});
