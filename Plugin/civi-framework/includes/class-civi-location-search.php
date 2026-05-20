<?php

/**
 * Global Location Search Helper
 * Handles location search for all languages and character sets
 */

defined('ABSPATH') || exit;

class Civi_Location_Search
{
	/**
	 * Global location search function that works with all languages
	 *
	 * @param string $location_name The location name to search for
	 * @param string $taxonomy The taxonomy to search in
	 * @return array|false Term object if found, false otherwise
	 */
	public static function find_location_term($location_name, $taxonomy) {
		if (empty($location_name) || empty($taxonomy)) {
			return false;
		}

		$location_name = trim($location_name);
		$location_term = null;

		// 0. If input looks like a numeric term ID, resolve directly (avoid ctype dependency)
		if (preg_match('/^\d+$/', $location_name) === 1) {
			$term_id = (int) $location_name;
			$direct_term = get_term($term_id, $taxonomy);
			if ($direct_term && !is_wp_error($direct_term)) {
				return $direct_term;
			}
		}

		// 1. Try exact name match first (most accurate)
		$location_term = get_term_by('name', $location_name, $taxonomy);
		if ($location_term && !is_wp_error($location_term)) {
			return $location_term;
		}

		// 2. Try slug match
		$location_slug = sanitize_title($location_name);
		$location_term = get_term_by('slug', $location_slug, $taxonomy);
		if ($location_term && !is_wp_error($location_term)) {
			return $location_term;
		}

		// 3. Try case-insensitive name match
		$terms = get_terms(array(
			'taxonomy' => $taxonomy,
			'name__like' => $location_name,
			'hide_empty' => false,
			'number' => 1
		));
		if (!empty($terms) && !is_wp_error($terms)) {
			return $terms[0];
		}

		// 4. Try with diacritics removed (for Latin-based languages)
		$location_no_diacritics = remove_accents($location_name);
		if ($location_no_diacritics !== $location_name) {
			$location_term = get_term_by('name', $location_no_diacritics, $taxonomy);
			if ($location_term && !is_wp_error($location_term)) {
				return $location_term;
			}
		}

		// 5. Try partial match with LIKE query (for all languages)
		global $wpdb;
		$term_id = $wpdb->get_var($wpdb->prepare(
			"SELECT t.term_id FROM {$wpdb->terms} t
			INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
			WHERE tt.taxonomy = %s
			AND (t.name LIKE %s OR t.slug LIKE %s)
			LIMIT 1",
			$taxonomy,
			'%' . $wpdb->esc_like($location_name) . '%',
			'%' . $wpdb->esc_like($location_slug) . '%'
		));

		if ($term_id) {
			return get_term($term_id, $taxonomy);
		}

		// 6. Try with normalized Unicode (for international characters)
		$normalized_name = self::normalize_unicode($location_name);
		if ($normalized_name !== $location_name) {
			$location_term = get_term_by('name', $normalized_name, $taxonomy);
			if ($location_term && !is_wp_error($location_term)) {
				return $location_term;
			}
		}

		// 7. Try with transliteration (for non-Latin scripts)
		$transliterated_name = self::transliterate_text($location_name);
		if ($transliterated_name !== $location_name) {
			$location_term = get_term_by('name', $transliterated_name, $taxonomy);
			if ($location_term && !is_wp_error($location_term)) {
				return $location_term;
			}
		}

		return false;
	}

	/**
	 * Normalize Unicode characters for better matching
	 *
	 * @param string $text The text to normalize
	 * @return string Normalized text
	 */
    private static function normalize_unicode($text) {
        if (class_exists('Normalizer')) {
            $formD = defined('Normalizer::FORM_D') ? constant('Normalizer::FORM_D') : null;
            $formC = defined('Normalizer::FORM_C') ? constant('Normalizer::FORM_C') : null;

            try {
                if ($formD !== null && $formC !== null) {
                    $text = \Normalizer::normalize($text, $formD);
                    $text = preg_replace('/[\x{0300}-\x{036F}]/u', '', $text);
                    $text = \Normalizer::normalize($text, $formC);
                } else {
                    $text = \Normalizer::normalize($text, 'NFD');
                    $text = preg_replace('/[\x{0300}-\x{036F}]/u', '', $text);
                    $text = \Normalizer::normalize($text, 'NFC');
                }
            } catch (\Throwable $e) {
                $text = remove_accents($text);
            }
        } else {
            $text = remove_accents($text);
        }

        return $text;
    }

	/**
	 * Transliterate text to Latin characters
	 *
	 * @param string $text The text to transliterate
	 * @return string Transliterated text
	 */
	private static function transliterate_text($text) {
		$transliteration_map = array(
			// Arabic
			'ا' => 'a', 'ب' => 'b', 'ت' => 't', 'ث' => 'th', 'ج' => 'j',
			'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'dh', 'ر' => 'r',
			'ز' => 'z', 'س' => 's', 'ش' => 'sh', 'ص' => 's', 'ض' => 'd',
			'ط' => 't', 'ظ' => 'z', 'ع' => 'a', 'غ' => 'gh', 'ف' => 'f',
			'ق' => 'q', 'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n',
			'ه' => 'h', 'و' => 'w', 'ي' => 'y',

			// Chinese (Pinyin approximations)
			'北京' => 'Beijing', '上海' => 'Shanghai', '广州' => 'Guangzhou',
			'深圳' => 'Shenzhen', '杭州' => 'Hangzhou', '南京' => 'Nanjing',

			// Japanese (Romaji)
			'東京' => 'Tokyo', '大阪' => 'Osaka', '京都' => 'Kyoto',
			'横浜' => 'Yokohama', '名古屋' => 'Nagoya',

			// Korean (Romanization)
			'서울' => 'Seoul', '부산' => 'Busan', '대구' => 'Daegu',
			'인천' => 'Incheon', '광주' => 'Gwangju',

			// Thai
			'กรุงเทพ' => 'Bangkok', 'เชียงใหม่' => 'Chiang Mai',
			'พัทยา' => 'Pattaya', 'ภูเก็ต' => 'Phuket',

			// Russian (Cyrillic to Latin)
			'Москва' => 'Moscow', 'Санкт-Петербург' => 'Saint Petersburg',
			'Новосибирск' => 'Novosibirsk', 'Екатеринбург' => 'Yekaterinburg',

			// Greek
			'Αθήνα' => 'Athens', 'Θεσσαλονίκη' => 'Thessaloniki',
			'Πάτρα' => 'Patras', 'Ηράκλειο' => 'Heraklion',
		);

		$transliterated = strtr($text, $transliteration_map);

		if ($transliterated === $text && function_exists('iconv')) {
			$transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
			if ($transliterated === false) {
				$transliterated = $text;
			}
		}

		if ($transliterated === $text) {
			$transliterated = remove_accents($text);
		}

		return $transliterated;
	}

	/**
	 * Get location search suggestions for autocomplete
	 *
	 * @param string $query The search query
	 * @param string $taxonomy The taxonomy to search in
	 * @param int $limit Maximum number of results
	 * @return array Array of location suggestions
	 */
	public static function get_location_suggestions($query, $taxonomy, $limit = 10) {
		if (empty($query) || empty($taxonomy)) {
			return array();
		}

		global $wpdb;

		$query = trim($query);
		$like_query = '%' . $wpdb->esc_like($query) . '%';

		$results = $wpdb->get_results($wpdb->prepare(
			"SELECT t.term_id, t.name, t.slug, tt.count
			FROM {$wpdb->terms} t
			INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
			WHERE tt.taxonomy = %s
			AND (t.name LIKE %s OR t.slug LIKE %s)
			ORDER BY tt.count DESC, t.name ASC
			LIMIT %d",
			$taxonomy,
			$like_query,
			$like_query,
			$limit
		));

		$suggestions = array();
		foreach ($results as $result) {
			$suggestions[] = array(
				'id' => $result->term_id,
				'name' => $result->name,
				'slug' => $result->slug,
				'count' => $result->count
			);
		}

		return $suggestions;
	}
}
