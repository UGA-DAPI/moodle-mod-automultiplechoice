
$(document).ready(function() {
	function asyncLoadComponents() {
		$('.async-load').each(asyncLoadComponent);
	}

	function asyncLoadComponent(){
		var container = $(this);
		var url = $(this).data('url');
		container.children('.async-target').each(function(){
			$(this).load(url, $(this).data('parameters'), function() {
				$('.async-post-load', container).show();
			});
		});
	}

	function asyncReloadComponents() {
		$('.async-load .async-target').html('<span class="loading" />');
		asyncLoadComponents();
	}

	$('.async-target > span').addClass('loading');
	asyncLoadComponents();

	/*$(".checklock").on("click", ':submit', function(){
		console.log('checklock called');
		var checkdata = $(this).closest('.checklock').data('checklock');
		var propagate = false;
        $.ajax({
			url: "ajax/checklock.php",
			data: checkdata, // { "a": quizz->id, "actions": "..." },
			method: 'get',
			async: false,
            success: function(data) {
				console.log(data.lock);
				console.log(data.msg);
				if (data.error) {
					propagate = true;
				}
				if (data.lock) {
					propagate = confirm("Attention aux problèmes suivant :\n - " + data.msg.join("\n - ") + "\n\nVoulez-vous continuer malgré tout ?");
				} else {
					propagate = true;
				}
			}
        });
		return propagate;

	});*/
});

