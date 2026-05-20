(function ($) {
	$(document).on('click', '.jobportal-approve-candidate', function (e) {
		e.preventDefault();
		var $link = $(this);
		var postId = $link.data('post-id');
		var nonce = $link.data('nonce');
		if (!postId || !nonce) return;
		var originalText = $link.text();
		$link.text(civiApproveCandidate.i18n.processing).prop('disabled', true);
		$.post(civiApproveCandidate.ajax_url, {
			action: 'jobportal_approve_candidate',
			_ajax_nonce: nonce,
			post_id: postId
		}).done(function (res) {
			if (res && res.success) {
				$link.closest('tr').find('.column-title .row-title').after(' <span class="label jobportal-label-blue">' + civiApproveCandidate.i18n.approved + '</span>');
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


