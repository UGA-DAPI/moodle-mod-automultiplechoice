/*
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

$(document).ready(function() {
    $("#questions-selected").on("change", "select", function(e) {
        var scoring = $(this).val();
		console.log($(this).find('option:selected').first());
		var score = $(this).find('option:selected').first().data('score');
		if (scoring === '') {
			$(this).closest('td').find('input.qscore').removeAttr('readonly');
		} else {
			$(this).closest('td').find('input.qscore').val(score).attr('readonly', 'readonly');
		}
    });

    //reprise du code de mainform.js
    function updateScoringDescription() {
        var id = $("#params-quizz select").val();
        var myurl = "ajax/scoring.php?scoringsetid=";
        $.ajax({
			url: myurl + id,
			method: 'get',
            success: function(data) {
				$("#scoringset_desc").html(data);
			}
        });
    }
    $("#params-quizz select").on("click", updateScoringDescription);
    updateScoringDescription();

	var expectedTotalScore = parseInt($('#expected-total-score').val());
    $("#questions-selected").on("keyup", "input.qscore", function(e) {
        var total = 0;
		inputs = $("#questions-selected input.qscore").each(function(index) {
			total += parseFloat($(this).val());
		});
		total = Math.round(4*total) / 4;
		$('#computed-total-score').html(total)
				.parent().toggleClass("score-mismatch", total !== Math.floor(expectedTotalScore));
    });
    $("#questions-selected input.qscore").first().keyup();

    $("#params-quizz").on("keyup", "input.qscore", function(e) {
        var res = parseInt($(this).val());
        var total = parseInt($('#computed-total-score').text());
        $('#total-score').html(res)
            .parent().toggleClass("score-mismatch", res !== total);
    });

	function toggleAnswers() {
		$(".question-answers").toggleClass('hide');
	}
	$('#toggle-answers').on('click', toggleAnswers);
	toggleAnswers();

	$(".checklock").on("click", 'input[type=submit]', function(){
		var id = $(this).closest('.checklock').data('checklockid');
		var check = $(this).closest("li[data-check]").data("check");
		var propagate = false;
        $.ajax({
			url: "ajax/checklock.php",
			data: { "id": id, "actions": check },
			method: 'get',
			async: false,
            success: function(data) {
				console.log(data.lock);
				console.log(data.msg);
				if (data.error) {
					propagate = true;
				}
				if (data.lock) {
					propagate = confirm("Attention aux problèmes suivant:\n - " + data.msg.join("\n - ") + "\n\nVoulez-vous continuer malgré tout ?");
				} else {
					propagate = true;
				}
			}
        });
				alert("coucou !");
		return propagate;

	});

    $('#scoring-distribution').on('click', function(){
        var totalScore = parseInt($('#expected-total-score').val());
        var qnumber = parseInt($('#quizz-qnumber').val());
        var valeur = Math.floor(100*(totalScore / qnumber)) / 100;
        var total = qnumber*valeur;
        inputs = $("#questions-selected input.qscore").each(function(index) {
			$(this).val(valeur);
		});
        $('#computed-total-score').html(total)
            .parent().toggleClass("score-mismatch", total !== totalScore);
    });
} );
