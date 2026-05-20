var NOTIFICATION = NOTIFICATION || {};
(function ($) {
    "use strict";

    var ajax_url = jobportal_template_vars.ajax_url,
        notification = $('.jobportal-notification'),
        icon = notification.find('.icon-noti'),
        content = notification.find('.content-noti');

    NOTIFICATION = {
        init: function () {
            this.content_active();
            this.refresh_notification();
        },

        content_active: function () {
            icon.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                content.toggleClass('active');
            });

            // Close notification panel when clicking outside
            $(document).off('click.jobportal_noti_panel').on('click.jobportal_noti_panel', function (e) {
                if (!$(e.target).closest('.jobportal-notification').length) {
                    content.removeClass('active');
                    // Also close any open dropdowns
                    $('.jobportal-notification .action-dropdown').removeClass('show');
                }
            });
        },

        refresh_notification: function () {

            // Delete notification handler
            $('body').off('click', '.jobportal-notification .btn-delete').on('click', '.jobportal-notification .btn-delete', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var $btn = $(this);
                var $item = $btn.closest('li');
                var noti_id = $btn.data('noti-id');
                if ($btn.hasClass('is-loading')) return;
                $btn.addClass('is-loading');
                // Add deleting animation to item
                $item.addClass('is-deleting');
                ajax_load(noti_id, "delete", $btn, $item);
            });

            // Mark as Read handler
            $('body').off('click', '.jobportal-notification .btn-mark-read').on('click', '.jobportal-notification .btn-mark-read', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var $btn = $(this);
                var $item = $btn.closest('li');
                var noti_id = $btn.data('noti-id');
                if ($btn.hasClass('is-loading')) return;
                $btn.addClass('is-loading');
                $item.addClass('is-marking-read');
                ajax_load(noti_id, "mark_read", $btn, $item);
            });

            // Mark as Unread handler
            $('body').off('click', '.jobportal-notification .btn-mark-unread').on('click', '.jobportal-notification .btn-mark-unread', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var $btn = $(this);
                var $item = $btn.closest('li');
                var noti_id = $btn.data('noti-id');
                if ($btn.hasClass('is-loading')) return;
                $btn.addClass('is-loading');
                $item.addClass('is-marking-unread');
                ajax_load(noti_id, "mark_unread", $btn, $item);
            });

            // Quick mark as read by clicking on unread dot
            $('body').off('click', '.jobportal-notification .btn-quick-read').on('click', '.jobportal-notification .btn-quick-read', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var $btn = $(this);
                var $item = $btn.closest('li');
                var noti_id = $btn.data('noti-id');
                if (noti_id) {
                    if ($btn.hasClass('is-loading')) return;
                    $btn.addClass('is-loading');
                    $item.addClass('is-marking-read');
                    ajax_load(noti_id, "mark_read", $btn, $item);
                }
            });

            // Clear all notifications
            $('body').off('click', '.jobportal-notification .noti-clear').on('click', '.jobportal-notification .noti-clear', function (e) {
                e.preventDefault();
                ajax_load("", "clear");
            });

            // Refresh notifications
            $('body').off('click', '.jobportal-notification .noti-refresh').on('click', '.jobportal-notification .noti-refresh', function (e) {
                e.preventDefault();
                ajax_load();
            });

            // Toggle dropdown menu on 3-dot icon click
            $('body').off('click', '.jobportal-notification .icon-setting-civi').on('click', '.jobportal-notification .icon-setting-civi', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var $dropdown = $(this).parent().find('.action-dropdown');
                // Close other dropdowns first
                $('.jobportal-notification .action-dropdown').not($dropdown).removeClass('show');
                // Toggle show state
                $dropdown.toggleClass('show');
            });

            // Close dropdown when clicking outside
            $(document).off('click.jobportal_noti_dropdown').on('click.jobportal_noti_dropdown', function (e) {
                if (!$(e.target).closest('.action-setting').length) {
                    $('.jobportal-notification .action-dropdown').removeClass('show');
                }
            });

            // Mark as read when clicking on notification item
            $('body').off('click', '.jobportal-notification .link-page').on('click', '.jobportal-notification .link-page', function (e) {
                var $link = $(this);
                var noti_id = $link.data('noti-id');
                var is_read = $link.data('is-read');
                var href = $link.attr('href');

                // Only mark as read if it's unread and has valid notification ID
                if (noti_id && is_read == '0') {
                    e.preventDefault();

                    // Mark as read via AJAX, then navigate
                    $.ajax({
                        type: "POST",
                        url: ajax_url,
                        dataType: "json",
                        data: {
                            action: "jobportal_refresh_notification",
                            noti_id: noti_id,
                            action_click: "mark_read",
                        },
                        success: function (data) {
                            if (data.success == true) {
                                // Update UI
                                icon.find('span').text(data.count);
                                content.html(data.noti_content);

                                // Navigate to the link if it's not just '#'
                                if (href && href !== '#') {
                                    window.location.href = href;
                                }
                            }
                        },
                        error: function() {
                            // If AJAX fails, still navigate
                            if (href && href !== '#') {
                                window.location.href = href;
                            }
                        }
                    });
                }
                // If already read or href is '#', do nothing (let default behavior happen)
            });

            function close_noti() {
                notification.find('.close-noti').on('click', function (e) {
                    e.preventDefault();
                    content.removeClass('active');
                });
            }
            close_noti();

            function ajax_load(noti_id = "", action_click = "", $btn = null, $item = null) {
                $.ajax({
                    type: "POST",
                    url: ajax_url,
                    dataType: "json",
                    data: {
                        action: "jobportal_refresh_notification",
                        noti_id: noti_id,
                        action_click: action_click,
                    },
                    success: function (data) {
                        if (data.success == true) {
                            // Handle delete with fade out animation
                            if (action_click === "delete" && $item) {
                                $item.addClass('is-deleted');
                                setTimeout(function() {
                                    icon.find('span').text(data.count);
                                    content.html(data.noti_content);
                                    close_noti();
                                }, 300);
                            }
                            // Handle mark read/unread with flash animation
                            else if ((action_click === "mark_read" || action_click === "mark_unread") && $item) {
                                $item.addClass('action-complete');
                                setTimeout(function() {
                                    icon.find('span').text(data.count);
                                    content.html(data.noti_content);
                                    close_noti();
                                }, 250);
                            }
                            else {
                                icon.find('span').text(data.count);
                                content.html(data.noti_content);
                                close_noti();
                            }
                        } else {
                            // Log error for debugging
                            console.error('Notification error:', data.message || 'Unknown error');
                            if (data.message) {
                                alert('Error: ' + data.message);
                            }
                            if ($btn) $btn.removeClass('is-loading');
                            if ($item) $item.removeClass('is-deleting is-marking-read is-marking-unread');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        console.error('Response:', xhr.responseText);
                        if ($btn) $btn.removeClass('is-loading');
                        if ($item) $item.removeClass('is-deleting is-marking-read is-marking-unread');
                    }
                });
            }
        }
    }

    $(document).ready(function () {
        NOTIFICATION.init();
    });

})(jQuery);
