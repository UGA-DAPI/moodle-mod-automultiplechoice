/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

$(document).ready(function($) {
	//console.log('mod_form', tinyMCE);
	/*function updateScoringDescription() {
		var id = $("#id_amc_scoringset").val();
		console.log('called', id);
        $.ajax({
			url: "../mod/automultiplechoice/ajax/scoring.php?scoringsetid=" + id,
			method: 'get',
            success: function(data) {
				$("#scoringset_desc").html(data);
				$("#instructions_scoringset").html(data);
			}
        });
	}*/
    //$("#id_amc_scoringset").on("click", updateScoringDescription);
    //updateScoringDescription();

    function updateInstrPrefix() {
        var v = $("#id_instructions").val();
		$("#id_amc_instructionsprefix").html(v);
		console.log('updateInstrPrefix');
		tinyMCE.get("id_amc_instructionsprefix").setContent(v);
    }
    $("#id_instructions").on("click", updateInstrPrefix);

	$("#id_anonymous").on("click", function(){
		if ($("#id_anonymous").is(':checked')) {
			$("#id_amc_lstudent").attr('disabled', 'disabled');
			$("#id_amc_lname").val($("#id_amc_lname").data('anon'));
		} else {
			$("#id_amc_lstudent").removeAttr('disabled');
			$("#id_amc_lname").val($("#id_amc_lname").data('std'));
		}
	});
});
