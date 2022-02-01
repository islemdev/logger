$(function() {
    $("#lookup").click(function(){
        alert(url);
        $.ajax({
			type: 'POST',
			url: url+"/"+$("#cin").val(),
			data: {},
		})
		.done(function(response) {
			// Make sure that the formMessages div has the 'success' class.
			console.log(response);
		})
		.fail(function(data) {
			console.log(data);
		});
	});
});