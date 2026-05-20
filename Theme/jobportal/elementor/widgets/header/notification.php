<?php

namespace JobPortal_Elementor;

defined('ABSPATH') || exit;

class Widget_Notification extends Base
{

    public function get_name()
    {
        return "jobportal-notification';
    }

    public function get_title()
    {
        return esc_html__('Notification', 'jobportal');
    }

    public function get_icon_part()
    {
        return 'eicon-woocommerce-notices';
    }

    public function get_keywords()
    {
        return ['modern', 'notification'];
    }

    public function get_script_depends()
    {
        return ['jobportalnotification'];
    }

    protected function register_controls()
    {
        $this->add_notification_section();
    }

    private function add_notification_section()
    {
        $this->start_controls_section('notification_section', [
            'label' => esc_html__('Notification', 'jobportal'),
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        global $current_user;
        $data_notification = jobportal_get_data_notification();
        ?>
        <div class="jobportal-notification">
            <?php jobportal_get_template('dashboard/notification/count.php', array(
                'data_notification' => $data_notification,
            )); ?>
            <?php if (in_array('jobportal_user_candidate', (array)$current_user->roles)
                || in_array('jobportal_user_employer', (array)$current_user->roles)) { ?>
                <div class="content-noti custom-scrollbar">
                    <?php jobportal_get_template('dashboard/notification/content.php', array(
                        'data_notification' => $data_notification,
                    )); ?>
                </div>
            <?php } ?>
        </div>

    <?php }
}
