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

    function updateInstrDescr() {
        var v = $("#id_instructions").val();
		$("#id_instructions_descr").html(v);
    }
    $("#id_instructions").on("click", updateInstrDescr);
    updateInstrDescr();

	$("#id_anonymous").on("click", function(){
		if ($("#id_anonymous").is(':checked')) {
			$("#id_amc_lstudent").attr('disabled', 'disabled');
		} else {
			$("#id_amc_lstudent").removeAttr('disabled');
		}
	});
});
