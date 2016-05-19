/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

jQuery(function($) {
    function updateScoringDescription() {
        var id = $("#id_amc_scoringset").val();
        $.ajax({
			url: "../mod/automultiplechoice/ajax/scoring.php?scoringsetid=" + id,
			method: 'get',
            success: function(data) {
				$("#scoringset_desc").html(data);
				$("#instructions_scoringset").html(data);
			}
        });
    }
    $("#id_amc_scoringset").on("click", updateScoringDescription);
    updateScoringDescription();

    function updateInstrPrefix() {
        var v = $("#id_instructions").val();
		$("#id_amc_instructionsprefix").html(v);
		tinyMCE.get("id_amc_instructionsprefix").setContent(v);
    }
    $("#id_instructions").on("click", updateInstrPrefix);
    //updateInstrPrefix();

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
