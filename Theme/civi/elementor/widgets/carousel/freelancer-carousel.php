<?php

namespace Civi_Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Image_Size;
use Elementor\Utils;

defined('ABSPATH') || exit;

class Widget_Freelancer_Carousel extends Carousel_Base
{

	public function get_name()
	{
		return 'civi-freelancer-carousel';
	}

	public function get_title()
	{
		return esc_html__('Freelancer Carousel', 'civi');
	}

	public function get_icon_part()
	{
		return 'eicon-posts-carousel';
	}

	public function get_keywords()
	{
		return ['freelancer', 'carousel'];
	}

	public function get_style_depends()
	{
		return ['civi-el-widget-freelancer-carousel'];
	}

	public function get_script_depends()
	{
		return ['civi-group-widget-carousel'];
	}

	protected function register_controls()
	{
		$this->add_content_section();

		parent::register_controls();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute('wrapper', 'class', 'civi-modern-carousel');
?>
		<div <?php $this->print_attributes_string('wrapper'); ?>>
			<?php $this->print_slider($settings); ?>
		</div>
		<?php
	}

	private function add_content_section()
	{
		$this->start_controls_section('content_section', [
			'label' => esc_html__('Content', 'civi'),
		]);

		$this->add_responsive_control('height', [
			'label'          => esc_html__('Height', 'civi'),
			'type'           => Controls_Manager::SLIDER,
			'default'        => [
				'size' => 700,
				'unit' => 'px',
			],
			'tablet_default' => [
				'unit' => 'px',
			],
			'mobile_default' => [
				'unit' => 'px',
			],
			'size_units'     => ['px', '%', 'vh'],
			'range'          => [
				'%'  => [
					'min' => 1,
					'max' => 100,
				],
				'px' => [
					'min' => 1,
					'max' => 1600,
				],
				'vh' => [
					'min' => 1,
					'max' => 100,
				],
			],
			'selectors'      => [
				'{{WRAPPER}} .swiper-slide' => 'height: {{SIZE}}{{UNIT}};',
			],
			'render_type'    => 'template',
		]);

		$repeater = new Repeater();

		$repeater->add_group_control(Group_Control_Background::get_type(), [
			'name'      => 'background',
			'types'     => ['classic', 'gradient'],
			'selector'  => '{{WRAPPER}} {{CURRENT_ITEM}}',
		]);

		$repeater->add_control('image', [
			'label'   => esc_html__('Image', 'civi'),
			'type'    => Controls_Manager::MEDIA,
			'default' => [
				'url' => Utils::get_placeholder_image_src(),
			],
		]);

		$repeater->add_control('rate', [
			'label'       => esc_html__('Rate', 'civi'),
			'label_block' => true,
			'type'        => Controls_Manager::SELECT,
			'default'     => '0',
			'options'     => [
				'0' => esc_html__('None', 'civi'),
				'1' => esc_html__('1', 'civi'),
				'2' => esc_html__('2', 'civi'),
				'3' => esc_html__('3', 'civi'),
				'4' => esc_html__('4', 'civi'),
				'5' => esc_html__('5', 'civi'),
			],
		]);

		$repeater->add_control('name', [
			'label'       => esc_html__('Name', 'civi'),
			'type'        => Controls_Manager::TEXTAREA,
			'dynamic'     => [
				'active' => true,
			],
			'placeholder' => esc_html__('Enter your name', 'civi'),
		]);

		$repeater->add_control('position', [
			'label'       => esc_html__('Position', 'civi'),
			'type'        => Controls_Manager::TEXTAREA,
			'dynamic'     => [
				'active' => true,
			],
			'placeholder' => esc_html__('Enter your position', 'civi'),
		]);

		$this->add_control('slides', [
			'label'       => esc_html__('Slides', 'civi'),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[
					'name'       => 'Automatic Updates',
				],
				[
					'name'       => 'Flexible Options',
				],
				[
					'name'       => 'Lifetime Use',
				],
			],
			'title_field' => '{{{ name }}}',
		]);

		$this->end_controls_section();
	}

	protected function print_slides(array $settings)
	{

		foreach ($settings['slides'] as $slide) :
			$slide_id = $slide['_id'];
			$item_key = 'item_' . $slide_id;
			$box_key = 'box_' . $slide_id;
			$box_tag = 'div';

			$this->add_render_attribute($item_key, 'class', [
				'swiper-slide',
				'elementor-repeater-item-' . $slide_id,
			]);

			$this->add_render_attribute($box_key, 'class', 'civi-box slide-wrapper');

			if (!empty($slide['link']['url'])) {
				$box_tag = 'a';
				$this->add_render_attribute($box_key, 'class', 'link-secret');
				$this->add_link_attributes($box_key, $slide['link']);
			}
		?>
			<div <?php $this->print_attributes_string($item_key); ?>>
				<?php printf('<%1$s %2$s>', $box_tag, $this->get_render_attribute_string($box_key)); ?>

				<div class="slide-image civi-image">
					<?php echo \Civi_Image::get_elementor_attachment([
						'settings'      => $slide,
						'size_settings' => $settings,
					]); ?>
				</div>

				<div class="slide-content">
					<div class="slide-layers">
						<?php
							if (!empty($slide['rate'])) :
						?>
						<div class="rating">
						<?php
							switch ($slide['rate']) {
								case '1':
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									break;
								case '2':
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									break;
								case '3':
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									break;
								case '4':
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									break;
								case '5':
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									echo '<span class="star"><i class="fas fa-star"></i></span>';
									break;

								default:
									echo '<span class="star"></span>';
									break;
							}
						?>
						</div>
						<?php endif; ?>
						<h3 class="title">
							<?php if (!empty($slide['name'])) : ?><?php echo wp_kses($slide['name'], 'civi'); ?><?php endif; ?>
							<?php if (!empty($slide['position'])) : ?><span><?php echo wp_kses($slide['position'], 'civi'); ?></span><?php endif; ?>
						</h3>
					</div>
				</div>

				<?php printf('</%1$s>', $box_tag); ?>
			</div>
<?php endforeach;
	}
}
