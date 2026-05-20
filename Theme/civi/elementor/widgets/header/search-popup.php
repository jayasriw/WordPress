<?php

namespace Civi_Elementor;

defined('ABSPATH') || exit;

class Widget_Search_Popup extends Base
{

    public function get_name()
    {
        return 'civi-search-popup';
    }

    public function get_title()
    {
        return esc_html__('Search Popup', 'civi');
    }

    public function get_icon_part()
    {
        return 'eicon-search';
    }

    public function get_keywords()
    {
        return ['modern', 'search-popup'];
    }


    protected function register_controls()
    {
        $this->add_search_popup_section();
    }

    private function add_search_popup_section()
    {
        $this->start_controls_section('search_popup_section', [
            'label' => esc_html__('Search Popup', 'civi'),
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        ?>
        <div class="block-search search-icon civi-ajax-search">
            <div class="icon-search">
                <i class="far fa-search"></i>
            </div>
        </div>
    <?php }
}
