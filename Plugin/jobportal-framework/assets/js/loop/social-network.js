(function ($) {
    "use strict";
    jQuery(document).ready(function () {
        $('.add-social').on('click', function (e) {
            e.preventDefault();
            $('.errors-log').text('');
            $('.add-social').addClass('disabled');
            var clone = $('.field-social-clone').html();
            $('.add-social-list').append(clone);

            // Update IDs and labels for accessibility
            var totalClones = $('.add-social-list .clone-wrap').length;
            $('.add-social-list .clone-wrap').each(function (index) {
                $(this).find('.number-network').html(index + 1);

                // Generate unique IDs for accessibility
                var uniqueId = 'social_' + Date.now() + '_' + index;
                $(this).find('input[name="candidate_social_name[]"]').attr('id', 'candidate_social_name_' + uniqueId);
                $(this).find('input[name="candidate_social_url[]"]').attr('id', 'candidate_social_url_' + uniqueId);
                $(this).find('label[for="candidate_social_name_new"]').attr('for', 'candidate_social_name_' + uniqueId);
                $(this).find('label[for="candidate_social_url_new"]').attr('for', 'candidate_social_url_' + uniqueId);
            });
            $('.add-social-list .clone-wrap:last-child').find('.icon-delete').trigger('click');
        });

        $('.add-social-list .clone-wrap').each(function (index) {
            $(this).find('.number-network').html(index + 1);
        });

        $('.add-social-list').on('click', '.remove-social', function (e) {
            e.preventDefault();
            $(this).parents('.clone-wrap').remove();

            // Update numbering and IDs after removal
            $('.add-social-list .clone-wrap').each(function (index) {
                $(this).find('.number-network').html(index + 1);

                // Update IDs for accessibility
                var uniqueId = 'social_' + Date.now() + '_' + index;
                $(this).find('input[name="candidate_social_name[]"]').attr('id', 'candidate_social_name_' + uniqueId);
                $(this).find('input[name="candidate_social_url[]"]').attr('id', 'candidate_social_url_' + uniqueId);
                $(this).find('label').filter('[for*="candidate_social_name_"]').attr('for', 'candidate_social_name_' + uniqueId);
                $(this).find('label').filter('[for*="candidate_social_url_"]').attr('for', 'candidate_social_url_' + uniqueId);
            });
        });
    });
})(jQuery);
