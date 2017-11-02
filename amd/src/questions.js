define(['jquery', 'jqueryui', 'mod_automultiplechoice/hello', 'mod_automultiplechoice/jquery.dataTables'], function ($, jqui, bot, dataTables) {

    return {
        init: function () {
            bot.say('toto');
            var lang = $('html').first().attr('lang');
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
            $("#questions-selected").sortable();
        
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
            });
        }
        
    }
    
});