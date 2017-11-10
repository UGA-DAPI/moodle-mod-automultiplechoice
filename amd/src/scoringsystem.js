define(['jquery'], function ($) {
    return {
        init: function () {

            $('#params-quizz select[name="amc[scoringset]"]').on('change', function() {
                console.log('should update');
                // update socring strategy description field
                this.updateScoringDescription();
            }.bind(this));

            this.updateScoringDescription();

            // Listen to scores input changes in order to warn the user if inconsistent results.
            $('#questions-selected').on('change', 'input.qscore', function () {                
                this.checkScoreConsistency();
            }.bind(this));

            // Show / Hide questions answers.
            $('#btn-toggle-answers').on('click', function() {
                this.toggleAnswers();
            }.bind(this));
            this.toggleAnswers();

            // Automatically allocate score to each question.
            $('#scoring-distribution').on('click', function () {
                var totalScore = parseInt($('#expected-total-score').val());
                var nbQuestions = parseInt($('#quizz-qnumber').val());
                var questionScore = Math.floor(100 * (totalScore / nbQuestions)) / 100;
                var total = nbQuestions * questionScore;
                $('form table#questions-selected input.qscore').each(function () {
                    $(this).val(questionScore);
                });
                this.checkScoreConsistency();
            }.bind(this));

            // If grademax is empty at page load, copy from totalpoints.
            if ($('#amc-grademax').val().toString() === '') {
                var totalpoints = $('#expected-total-score').val().toString();
                $('#amc-grademax').val(totalpoints);
            }

            // Listen to score total changes
            $('#expected-total-score').on('change', function (e) {
                // change values and check score consistency
                $('#total-score').html(e.target.value.toString());
                this.checkScoreConsistency();
            }.bind(this));
        },
        updateScoringDescription: function () {
            var id = $('#params-quizz select[name="amc[scoringset]"]').val();
            var myurl = 'ajax/scoring.php?scoringsetid=';
            $.ajax({
                url: myurl + id,
                method: 'get',
                success: function (data) {
                    $('#scoringset_desc').html(data);
                }
            });
        },
        toggleAnswers: function () {
            $('.question-answers').toggleClass('hide');
        },
        checkScoreConsistency: function () {
            var expectedTotalScore = parseInt($('#expected-total-score').val());
            var total = 0;
            $('#questions-selected input.qscore').each(function () {
                if ($(this).val()) {
                    total += parseFloat($(this).val());
                }
            });
            $('#computed-total-score').text(total.toString());
            var invalid = Math.abs(total - expectedTotalScore) > 0.01;
            $('#computed-total-score').closest('td').toggleClass('score-mismatch',invalid);
        }
    }
})