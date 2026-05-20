(function ($) {
	$(document).on('click', '.jobportal-approve-user-status', function (e) {
		e.preventDefault();
		var $link = $(this);
		var userId = $link.data('user-id');
		var nonce = $link.data('nonce');
		if (!userId || !nonce) return;
		var originalText = $link.text();
		$link.text(civiApproveUser.i18n.processing).prop('disabled', true);
		$.post(civiApproveUser.ajax_url, {
			action: 'jobportal_approve_user_status',
			_ajax_nonce: nonce,
			user_id: userId
		}).done(function (res) {
			if (res && res.success) {
				$link.closest('tr').find('.column-custom_column').html('<span class="label jobportal-label-blue">' + civiApproveUser.i18n.approved + '</span>');
				$link.remove();
			} else {
				$link.text(originalText).prop('disabled', false);
				alert((res && res.data && res.data.message) ? res.data.message : 'Error');
			}
		}).fail(function () {
			$link.text(originalText).prop('disabled', false);
			alert('Error');
		});
	});
})(jQuery);


