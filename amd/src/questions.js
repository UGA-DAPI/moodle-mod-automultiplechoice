define(['jquery', 'jqueryui', 'mod_automultiplechoice/jquery.dataTables', 'core/templates', 'core/modal_factory', 'mod_automultiplechoice/qbank-modal', 'core/modal_events', 'core/config'], function ($, jqui, dataTables, Templates, ModalFactory, QbankModal, ModalEvents, mdlconfig) {

    return {
        init: function (courseid) {

            this.courseid = courseid;
            /*var lang = $('html').first().attr('lang');
            var oLanguage = {};
            if (lang && lang !== 'en') {
                oLanguage = { "sUrl": "assets/dataTables/i18n/" + lang + ".json" };
            }
            var dataTableConfig = {
                "iDisplayLength": 20,
                "bPaginate": true,
                "aaSorting": [], // no initial sorting
                "aoColumnDefs": [
                    { "aTargets": [2], "sWidth": "12ex" },
                    { "aTargets": [3], "bSortable": false, "sWidth": "5ex" }
                ],
                "oLanguage": oLanguage
            };

            $("#questions-list").dataTable(dataTableConfig);


            var Question = {
                template: null,
                initTemplate: function () {
                    this.template = $("li#template-question").first().clone();
                    this.template.removeAttr("style");
                    this.template.removeAttr("id");
                    this.template.find('input').removeAttr('disabled');
                },
                add: function (qid, qtitle) {
                    var chunk = this.template.clone();
                    chunk.find("input.qid").first().val(qid);
                    chunk.find("label").first().html(qtitle);
                    chunk.attr("id", "qsel-" + qid);
                    chunk.appendTo($("#questions-selected"));
                },
                remove: function (qid) {
                    $("#qsel-" + qid).remove();
                }
            };
            Question.initTemplate();

            // handle add / remove actions click event on each question dataTable row
            $("#questions-list").on("click", "button", function (e) {
                var btn = $(e.target);
                var qid = btn.data("qid");
                console.log('button clicked');
                if (btn.data("selected")) {
                    // remove question from qcm
                    btn.removeClass('btn-danger');
                    btn.addClass('btn-default');
                    btn.html('<span class="fa fa-plus"></span>');
                    btn.removeData("selected");
                    Question.remove(qid);
                } else {
                    // add question to qcm
                    var qtitle = btn.closest('tr').children('td.qtitle').first().text();
                    btn.removeClass('btn-default');
                    btn.addClass('btn-danger');
                    btn.html('<span class="fa fa-trash"></span>');
                    btn.data("selected", true);
                    Question.add(qid, qtitle);
                }
            });

            $("#questions-selected").on("click", "button", function (e) {
                var qid = $(this).closest('li').find('input.qid').first().val();
                if (qid) {
                    Question.remove(qid);
                } else {
                    $(this).closest('li').remove();
                }
            });

            var Section = {
                template: null,
                initTemplate: function () {
                    this.template = $("li#template-section").first().clone();
                    this.template.removeAttr("style");
                    this.template.removeAttr("id");
                    this.template.find(':input').removeAttr('disabled');
                },
                add: function () {
                    var chunk = this.template.clone();
                    chunk.appendTo($("#questions-selected"));
                }
            };
            Section.initTemplate();

            $("#insert-section").on("click", function (e) {
                Section.add();
            });*/

              $("#questions-selected").sortable();
            $('body').on('change', '.amcquestion-checkbox', function(e){
                console.log('row checked', e.target);
            });

            $('body').on('change', '#amc-qbank-categories-select', function(e){
                this.loadQuestions(e.target.value);
            }.bind(this));


            var trigger = $('#qbank');

            ModalFactory.create({type: QbankModal.TYPE}, trigger).done(function(modal) {
                  // on modal open
                  modal.getRoot().on(ModalEvents.shown, function(e) {
                      this.loadCategories();
                  }.bind(this));

                  // on save
                  modal.getRoot().on(ModalEvents.save, function(e) {
                    // Stop the default save button behaviour which is to close the modal.
                    e.preventDefault();
                    // Do your form validation here.
                    // add selected questions to the current section
                    console.log('you clicked add button!');
                    modal.hide();
                  });
            }.bind(this));
        },
        loadCategories(){
            var url = mdlconfig.wwwroot + '/mod/automultiplechoice/ajax/qbank.php?cid=' + this.courseid;
            $.ajax({
                method: 'GET',
                url: url
            }).done(function(response) {
                var requestdata = JSON.parse(response);
                var status = requestdata.status;
                var categories = requestdata.categories;
                // Template.render will not be suitable for what we want... too bad
                this.appendHtml(status, categories);
            }.bind(this)).fail(function(jqXHR, textStatus) {
                console.log(jqXHR, textStatus);
            });
        },
        loadQuestions(catid){
            var url = mdlconfig.wwwroot + '/mod/automultiplechoice/ajax/qbank.php?cid=' + this.courseid + '&catid=' + catid;
            console.log('url', url);
            $.ajax({
                method: 'GET',
                url: url
            }).done(function(response) {
                var requestdata = JSON.parse(response);
                var status = requestdata.status;
                var questions = requestdata.questions;
                this.appendHtml(status, [], questions, catid);
            }.bind(this)).fail(function(jqXHR, textStatus) {
                console.log(jqXHR, textStatus);
            });
        },
        appendHtml(status, categories, questions, selected) {
            if(status === 200){
                if (selected) {
                    $('#amc-qbank-questions').empty();
                    var questionsHtml = this.buildModalQuestionList(questions);
                    $('#amc-qbank-questions').append(questionsHtml);
                } else {
                    var categoriesHtml = this.buildCategoriesOptions(categories);
                    $('#amc-qbank-categories-select').append(categoriesHtml);
                }
            }

        },
        buildCategoriesOptions(categories) {
            var html = '';
            for(var i in categories) {
                html += '<option value="' + categories[i].value + '">';
                html +=  categories[i].label;
                html += '</option>';
            }
            return html;
        },
        buildModalQuestionList(questions) {
            console.log('questions', questions);
            // @TODO get all questions row already in DOM so that we wont add them to the list of "selectable" questions
            var html = '';
            for(var i in questions) {

                html += '<tr class="amcquestion-row" id="' + questions[i].id + '">';
                html += ' <td><input class="amcquestion-checkbox" type="checkbox"></input></td>';
                html += ' <td>' + questions[i].name + '</td>';
                html += ' <td><a target="_blank" href="' + mdlconfig.wwwroot + '/question/preview.php?id=' + questions[i].id + '" title="Preview"><i class="icon fa fa-search-plus fa-fw"></i></a></td>';
                html += '</tr>';

            }
            return html;
        }
    }

});
