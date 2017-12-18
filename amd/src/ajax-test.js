define(['jquery', 'core/str', 'core/notification', 'core/config', 'core/ajax', 'core/templates', 'core/modal_factory'], function ($, str, notification, mdlcfg, ajax, templates, ModalFactory) {

    return {
          init: function () {
              // will be a constant

              // simulate a call to the api at page load
              this.callAmcApi('hello', {id:4, firstname: 'Mickey', lastname:'Mouse'}, 'hello-ajax-spinner', 'ajax-response');
              $('.btn-call-amc-api').on('click', function(e){
                  this.btnCallAmcApi(e);
              }.bind(this));
          },
          btnCallAmcApi: function(e) {
            // get data from action caller
            var action = $(e.target).data('action');
            var spinner = $(e.target).data('spinner-id');
            var params = $(e.target).data('params');
            this.callAmcApi(action, params, spinner);
          },
          /**
           * [description]
           * @param  string action  the action to do
           * @param  object params  parameters for the action
           * @param  string spinner id of the spinner to show / hide
           * @param  string target  id of container to populate with response data
           * @return {[type]}         [description]
           */
          callAmcApi: function (action, params, spinner, target) {
              if(!action || !params || !spinner) {
                console.log('one or several mandatory parameter(s) missing.');
                return false;
              }
              // console.log(action, params);
              //$('#' + spinner).show();
              //should we use a global modal ? or not ? depending on ajax called via a button or on page load ?
              var modalpromise = ModalFactory.create({
                title: 'Please wait...',
                body: templates.render('mod_automultiplechoice/ajaxmodal', {})
              }).done(function(modal){
                  modal.show();
                  // modal.hide(); // does not work... see modal.js (lib/amd/src/modal.js) and isVisible method... the modal object does not have 'show' class...
                  console.log(modal.getRoot().attr('class')); // does not have 'show' class (modal moodle-has-zindex) ans so does not hide...
                  if (!modal.root.hasClass('show')) {
                      modal.getRoot().addClass('show');
                  }
                  $.ajax({
                      method: 'GET',
                      url: 'https://en.wikipedia.org/api/rest_v1/feed/announcements',
                      data: {}
                  }).done(function(response) {
                      // $('#' + spinner).hide();

                      console.log('done', response.announce[0].text);
                      if(target){
                        $('#' + target).html(response.announce[0].text);
                      }
                      modal.hide();
                  }).fail(function(jqXHR, textStatus) {
                      console.log(jqXHR, textStatus);
                      //$('#' + spinner).hide();
                      modal.hide();
                  })
              });

              /*modalpromise.then(function(modal){
                  console.log('wtf', modal);
                  //console.log('modal', this.modal);
                  modal.show();
                  console.log(modal.root.attr('class'))
                  //this.modal = modal.root.addClass('show');

                /*  window.setTimeout(function(){
                    modal.hide();
                  }, 5000);*/
                  /*$.ajax({
                      method: 'GET', // 'POST'
                      url: 'https://en.wikipedia.org/api/rest_v1/feed/announcements',
                      //data: { action: action, data: params }
                      data: {}
                  }).done(function(response) {
                      //$('#' + spinner).hide();
                      // does not work... but WHY ???

                    //  modal.getRoot().hide();
                      console.log('done', response.announce[0].text);
                      if(target){
                        $('#' + target).html(response.announce[0].text);
                      }
                  }).fail(function(jqXHR, textStatus) {
                      console.log(jqXHR, textStatus);
                      //$('#' + spinner).hide();
                      modal.hide();
                  }).then(function(dd){
                      console.log('then??', dd);
                      console.log(modal);
                      modal.hide();
                  }.bind(this));


              });*/

    /*
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

                      console.log('should hide modal', this.modal);

                    }.bind(this)).fail(function(ex) {
                       console.log('mod_automultiplechoice_call_api failed', ex);
                    }.bind(this));
                } else {
                    //var testurl = 'https://en.wikipedia.org/api/rest_v1/feed/announcements';

                }
              }.bind(this));
*/

          }
      }
});
