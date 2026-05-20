<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class JobPortal_SVG_Sanitizer
 * Sanitizes SVG uploads to prevent XSS attacks.
 */
class JobPortal_SVG_Sanitizer
{
    /**
     * Sanitize SVG content.
     *
     * @param string $dirty_svg Raw SVG content.
     * @return string|false Sanitized SVG content, or false on failure.
     */
    public static function sanitize($dirty_svg)
    {
        if (empty($dirty_svg)) {
            return false;
        }

        // Disallow PHP processing instructions
        if (strpos($dirty_svg, '<?php') !== false || strpos($dirty_svg, '<?=') !== false) {
            return false;
        }

        // Load as DOMDocument
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadXML($dirty_svg, LIBXML_NONET);
        libxml_use_internal_errors(false);

        if (!$dom) {
            return false;
        }

        // Dangerous elements to strip
        $dangerous_elements = [
            'script', 'embed', 'object', 'iframe', 'applet',
            'meta', 'link', 'base', 'form', 'input', 'button',
        ];

        // Dangerous attributes (event handlers and javascript: hrefs)
        $dangerous_attributes = [
            'onload', 'onclick', 'onmouseover', 'onerror', 'onfocus',
            'onblur', 'onchange', 'onsubmit', 'onkeydown', 'onkeyup',
            'onkeypress', 'onmouseout', 'onmousedown', 'onmouseup',
            'onmousemove', 'onscroll', 'onresize', 'onunload',
        ];

        // Remove dangerous elements
        foreach ($dangerous_elements as $tag) {
            $nodes = $dom->getElementsByTagName($tag);
            for ($i = $nodes->length - 1; $i >= 0; $i--) {
                $node = $nodes->item($i);
                $node->parentNode->removeChild($node);
            }
        }

        // Remove dangerous attributes and href/xlink:href with javascript:
        $all_elements = $dom->getElementsByTagName('*');
        for ($i = $all_elements->length - 1; $i >= 0; $i--) {
            $element = $all_elements->item($i);
            if (!$element instanceof DOMElement) {
                continue;
            }

            foreach ($dangerous_attributes as $attr) {
                $element->removeAttribute($attr);
            }

            // Strip javascript: from href and similar attributes
            foreach (['href', 'xlink:href', 'src', 'action'] as $attr) {
                if ($element->hasAttribute($attr)) {
                    $value = $element->getAttribute($attr);
                    if (stripos(trim($value), 'javascript:') === 0 || stripos(trim($value), 'data:') === 0) {
                        $element->removeAttribute($attr);
                    }
                }
            }
        }

        return $dom->saveXML($dom->documentElement);
    }
}
