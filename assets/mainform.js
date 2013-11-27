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
		$("#instructions_descr").html(v);
    }
    $("#id_instructions").on("click", updateInstrDescr);
    updateInstrDescr();
});
