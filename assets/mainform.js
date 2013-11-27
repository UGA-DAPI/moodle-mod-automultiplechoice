/* 
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

jQuery(function($) {
    function updateDescription() {
        var id = $("#id_amc_scoringset").val();
        $.ajax({
			url: "../mod/automultiplechoice/ajax/scoring.php?scoringsetid=" + id,
			method: 'get',
            success: function(data) {
				$("#scoringset_desc").html(data);
			}
        });
    }
    $("#id_amc_scoringset").on("click", updateDescription);
    updateDescription();
});
