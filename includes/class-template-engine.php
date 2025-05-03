<?php

class CPIG_Template_Engine
{
    /**
     * Generate and attach the image based on a saved template and product ID.
     */
    public static function generate_from_template($template_id, $product_id)
    {
        // Load template meta
        $base_id      = get_post_meta($template_id, 'base_image_id', true);
        $fabric_json  = get_post_meta($template_id, 'fabric_json', true);
        $placeholders = get_post_meta($template_id, 'placeholders', true) ?: [];

        // Load base image path
        $base_src = wp_get_attachment_image_src($base_id, 'full');
        if (! $base_src) {
            return; // no base image
        }
        $base_path = get_attached_file($base_id);

        // Decode JSON
        $data = json_decode($fabric_json, true);
        if (! isset($data['objects'])) {
            return;
        }

        // Choose renderer: Imagick if available, else GD
        if (class_exists('Imagick')) {
            $img = new Imagick();
            $img->setSize($data['objects'][0]['width'], $data['objects'][0]['height']);
            $img->readImage($base_path);
            $draw = new ImagickDraw();

            foreach ($data['objects'] as $obj) {
                // Image objects (e.g., logos)
                if (! empty($obj['type']) && $obj['type'] === 'image' && ! empty($obj['src'])) {
                    $att_id = attachment_url_to_postid($obj['src']);
                    $path   = $att_id ? get_attached_file($att_id) : wp_normalize_path(ABSPATH . str_replace(home_url('/'), '', $obj['src']));
                    if (file_exists($path)) {
                        $overlay = new Imagick();
                        $overlay->readImage($path);
                        $scaleX = isset($obj['scaleX']) ? floatval($obj['scaleX']) : 1;
                        $scaleY = isset($obj['scaleY']) ? floatval($obj['scaleY']) : 1;
                        $overlay->scaleImage(
                            $overlay->getImageWidth() * $scaleX,
                            $overlay->getImageHeight() * $scaleY
                        );
                        $left = isset($obj['left']) ? intval($obj['left']) : 0;
                        $top  = isset($obj['top']) ? intval($obj['top']) : 0;
                        $img->compositeImage($overlay, Imagick::COMPOSITE_DEFAULT, $left, $top);
                        $overlay->destroy();
                    }
                }
                // Text objects
                if (! empty($obj['type']) && in_array($obj['type'], ['i-text','textbox'])) {
                    $text = $obj['text'];
                    $text = self::replace_placeholders($text, $product_id, $placeholders);

                    $font   = ! empty($obj['fontFamily']) ? self::getFontPathsforImagick($obj['fontFamily']) : self::getFontPathsforImagick('Arial');
                    $size   = ! empty($obj['fontSize']) ? intval($obj['fontSize']) : 24;
                    $color  = ! empty($obj['fill']) ? $obj['fill'] : '#000000';
                    $left   = isset($obj['left']) ? intval($obj['left']) : 0;
                    $top    = isset($obj['top']) ? intval($obj['top'] + $size) : 0;

                    $draw->setFont($font);
                    $draw->setFontSize($size);
                    $draw->setFillColor($color);
                    $draw->setTextAlignment(Imagick::ALIGN_CENTER);

                    $img->annotateImage($draw, $left, $top, 0, $text);
                }
            }

            $img->setImageFormat('png');
            $png_data = $img->getImageBlob();
        } else {
            $base = imagecreatefromstring(file_get_contents($base_path));
            if (! $base) {
                return;
            }

            foreach ($data['objects'] as $obj) {
                if (! empty($obj['type']) && $obj['type'] === 'image' && ! empty($obj['src'])) {
                    $att_id = attachment_url_to_postid($obj['src']);
                    $path   = $att_id ? get_attached_file($att_id) : wp_normalize_path(ABSPATH . str_replace(home_url('/'), '', $obj['src']));
                    echo $path;
                    if (file_exists($path)) {
                        $overlay = imagecreatefromstring(file_get_contents($path));
                        $w = imagesx($overlay);
                        $h = imagesy($overlay);
                        $scaleX = isset($obj['scaleX']) ? floatval($obj['scaleX']) : 1;
                        $scaleY = isset($obj['scaleY']) ? floatval($obj['scaleY']) : 1;
                        $nw = intval($w * $scaleX);
                        $nh = intval($h * $scaleY);
                        $tmp = imagecreatetruecolor($nw, $nh);
                        imagealphablending($tmp, false);
                        imagesavealpha($tmp, true);
                        imagecopyresampled($tmp, $overlay, 0, 0, 0, 0, $nw, $nh, $w, $h);
                        imagedestroy($overlay);
                        $left = isset($obj['left']) ? intval($obj['left']) : 0;
                        $top  = isset($obj['top']) ? intval($obj['top']) : 0;
                        imagecopy($base, $tmp, $left, $top, 0, 0, $nw, $nh);
                        imagedestroy($tmp);
                    }
                }
                if (! empty($obj['type']) && in_array($obj['type'], ['i-text','textbox'])) {
                    $text = $obj['text'];
                    $text = self::replace_placeholders($text, $product_id, $placeholders);
                    $size   = ! empty($obj['fontSize']) ? intval($obj['fontSize']) : 24;
                    $color  = ! empty($obj['fill']) ? $obj['fill'] : '#000000';
                    $left   = isset($obj['left']) ? intval($obj['left']) : 0;
                    $top    = isset($obj['top']) ? intval($obj['top']) : 0;
                    list($r, $g, $b) = sscanf($color, "#%02x%02x%02x");
                    $col = imagecolorallocate($base, $r, $g, $b);
                    imagestring($base, 5, $left, $top, $text, $col);
                }
            }
            ob_start();
            imagepng($base);
            $png_data = ob_get_clean();
            imagedestroy($base);
        }

        // Save to uploads, attach to product (unchanged)
        $upload = wp_upload_bits("cpig-gen-{$product_id}.png", null, $png_data);
        if ($upload['error']) {
            return;
        }
        $filetype   = wp_check_filetype($upload['file'], null);
        $attachment = [ 'post_mime_type' => $filetype['type'], 'post_title' => sanitize_file_name(basename($upload['file'])), 'post_status' => 'inherit' ];
        $attach_id  = wp_insert_attachment($attachment, $upload['file'], $product_id);
        require_once ABSPATH.'wp-admin/includes/image.php';
        $meta = wp_generate_attachment_metadata($attach_id, $upload['file']);
        wp_update_attachment_metadata($attach_id, $meta);
        set_post_thumbnail($product_id, $attach_id);
    }

    /**
     * Replace placeholders in a string based on product data
     */
    private static function replace_placeholders($text, $product_id, $placeholders)
    {
        $product = wc_get_product($product_id);
        $map = [ '{product_name}' => $product->get_name() ];
        foreach ($placeholders as $ph) {
            if (preg_match('/^{attribute_(.+)}$/', $ph, $m)) {
                $map[ $ph ] = $product->get_attribute($m[1]);
            }
        }
        return strtr($text, $map);
    }

    public static function getFontPathsforImagick($fontName)
    {
        return plugin_dir_path(__FILE__).'../fonts/Arial.ttf';
    }

}
