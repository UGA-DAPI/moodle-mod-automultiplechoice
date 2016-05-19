/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */
$(document).ready(function() {
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
			{ "aTargets": [ 2 ], "sWidth": "12ex" },
			{ "aTargets": [ 3 ], "bSortable": false, "sWidth": "5ex" }
		],
		"oLanguage": oLanguage
	};
    $("#questions-list").dataTable(dataTableConfig);

    $("#questions-selected").sortable();

    var Question = {
        template: null,
        initTemplate: function() {
            this.template = $("li#template-question").first().clone();
            this.template.removeAttr("style");
            this.template.removeAttr("id");
			this.template.find('input').removeAttr('disabled');
        },
        add: function(qid, qtitle) {
            var chunk = this.template.clone();
            chunk.find("input.qid").first().val(qid);
            chunk.find("label").first().html(qtitle);
			chunk.attr("id", "qsel-" + qid);
            chunk.appendTo($("#questions-selected"));
        },
        remove: function(qid) {
            $("#qsel-" + qid).remove();
			$("#q-" + qid + " button").data("selected", false).text("+");
        }
    };
	Question.initTemplate();

    $("#questions-list").on("click", "button", function(e) {
		var bton = $(e.target);
		var qid = $(e.target).data("qid");
        if (bton.data("selected")) {
			bton.text(">>");
            bton.removeData("selected");
            Question.remove(qid);
        } else {
			var qtitle = bton.closest('tr').children('td.qtitle').first().text();
			bton.html("&#x2A2F;");
            bton.data("selected", true);
            Question.add(qid, qtitle);
        }
    });
    $("#questions-selected").on("click", "button", function(e) {
        var qid = $(this).closest('li').find('input.qid').first().val();
		if (qid) {
			Question.remove(qid);
		} else {
			$(this).closest('li').remove();
		}
    });

    var Section = {
        template: null,
        initTemplate: function() {
            this.template = $("li#template-section").first().clone();
            this.template.removeAttr("style");
            this.template.removeAttr("id");
			this.template.find(':input').removeAttr('disabled');
        },
        add: function() {
			var chunk = this.template.clone();
            chunk.appendTo($("#questions-selected"));
        }
    };
	Section.initTemplate();

	$("#insert-section").on("click", function(e) {
        Section.add();
    });
} );

