/* 
 * @license http://www.gnu.org/licenses/gpl-2.0.html  GNU GPL v2
 */

$(document).ready(function() {
    $("#questions-list").dataTable();
    $("#questions-selected").sortable();

    var Question = {
        template: null,
        initTemplate: function() {
            this.template = $("#questions-selected > li").first().clone();
            this.template.removeAttr("style");
        },
        add: function(qid, qtitle) {
			console.log("Adding " + qid);
            var chunk = this.template.clone();
			console.log(chunk.find("label"));
            chunk.find("input").first().val(qid);
            chunk.find("label").first().html(qtitle);
            chunk.appendTo($("#questions-selected"));
        },
        remove: function(qid) {
            //
        }
    };
	Question.initTemplate();

    $("#questions-list").on("click", "button", function(e) {
		var bton = $(e.target);
		var qid = $(e.target).data("qid");
        if (bton.data("selected")) {
			bton.text("+");
            bton.removeData("selected");
            Question.remove(qid);
        } else {
			var qtitle = bton.closest('tr').children('td.qtitle').first().text();
			bton.text("-");
            bton.data("selected", true);
            Question.add(qid, qtitle);
        }
    });
    $("#questions-selected").on("click", "button", function(e) {
        //
    });
} );
