<?php

// Enqueue jQuery and custom JavaScript
function enqueue_jquery_and_sync_js()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('sync-script', plugins_url('js/sync.js', __FILE__), array('jquery'), '1.0', true);

    // Localize script to pass the AJAX URL to JavaScript
    wp_localize_script('sync-script', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('admin_enqueue_scripts', 'enqueue_jquery_and_sync_js');

function upload_image_to_s3($image_url, $awsAccessKeyId, $awsSecretAccessKey, $awsBucketName, $region)
{
    // Convert relative path to absolute URL
    if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
        $image_url = site_url($image_url);
    }

    $file_name = basename($image_url);
    $local_path = sys_get_temp_dir() . '/' . $file_name;

    // Download the image to a temporary local file
    $file_contents = file_get_contents($image_url);
    if ($file_contents === false) {
        return [
            'error' => true,
            'message' => 'Failed to download image from URL.',
            'url' => $image_url
        ];
    }

    file_put_contents($local_path, $file_contents);
    $file_contents = file_get_contents($local_path);

    // Check if the image already exists in the S3 bucket
    $existing_images = wp_remote_get('https://' . $awsBucketName . '.s3.' . $region . '.amazonaws.com/' . rawurlencode($file_name));
    if (!is_wp_error($existing_images) && $existing_images['response']['code'] === 200) {
        // Image already exists, append a suffix to the filename
        $file_name = pathinfo($file_name, PATHINFO_FILENAME) . '_1.' . pathinfo($file_name, PATHINFO_EXTENSION);
    }

    $s3_url = 'https://' . $awsBucketName . '.s3.' . $region . '.amazonaws.com/' . rawurlencode($file_name);

    $amz_date = gmdate('Ymd\THis\Z');
    $date_stamp = gmdate('Ymd');

    $canonical_request = "PUT\n/" . rawurlencode($file_name) . "\n\ncontent-type:" . mime_content_type($local_path) . "\nhost:$awsBucketName.s3.$region.amazonaws.com\nx-amz-acl:public-read\nx-amz-content-sha256:" . hash('sha256', $file_contents) . "\nx-amz-date:$amz_date\n\ncontent-type;host;x-amz-acl;x-amz-content-sha256;x-amz-date\n" . hash('sha256', $file_contents);

    $string_to_sign = "AWS4-HMAC-SHA256\n$amz_date\n$date_stamp/$region/s3/aws4_request\n" . hash('sha256', $canonical_request);

    $kDate = hash_hmac('sha256', $date_stamp, 'AWS4' . $awsSecretAccessKey, true);
    $kRegion = hash_hmac('sha256', $region, $kDate, true);
    $kService = hash_hmac('sha256', 's3', $kRegion, true);
    $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

    $signature = hash_hmac('sha256', $string_to_sign, $kSigning);

    $response = wp_remote_request(
        $s3_url,
        array(
            'method' => 'PUT',
            'body' => $file_contents,
            'headers' => array(
                'Authorization' => 'AWS4-HMAC-SHA256 Credential=' . $awsAccessKeyId . '/' . $date_stamp . '/' . $region . '/s3/aws4_request, SignedHeaders=content-type;host;x-amz-acl;x-amz-content-sha256;x-amz-date, Signature=' . $signature,
                'Content-Type' => mime_content_type($local_path),
                'x-amz-acl' => 'public-read',
                'x-amz-content-sha256' => hash('sha256', $file_contents),
                'x-amz-date' => $amz_date,
                'Content-Length' => strlen($file_contents),
            ),
        )
    );

    // Remove the local temporary file after upload
    unlink($local_path);

    $response_code = wp_remote_retrieve_response_code($response);

    if ($response_code >= 200 && $response_code < 300) {
        return [
            'error' => false,
            'url' => $s3_url
        ];
    } else {
        return [
            'error' => true,
            'message' => 'S3 Upload Failed. Check permissions and credentials.',
            'url' => $image_url
        ];
    }
}

function sync_images_to_s3()
{
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => 'You are not allowed to perform this action.'));
    }

    $access_key = get_option('aws_access_key_id');
    $secret_key = get_option('aws_secret_access_key');
    $bucket_name = get_option('aws_bucket_name');
    $region = get_option('aws_region');

    if (!$access_key || !$secret_key || !$bucket_name || !$region) {
        wp_send_json_error(array('message' => 'Missing AWS configuration.'));
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    global $wpdb;

    // Get all post meta data for the post
    $post_meta_data = get_post_meta($post_id);

    foreach ($post_meta_data as $meta_key => $meta_value) {
        $meta_value = maybe_unserialize($meta_value[0]);

        if (is_string($meta_value) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $meta_value) && strpos($meta_value, $bucket_name) === false) {
            $result = upload_image_to_s3($meta_value, $access_key, $secret_key, $bucket_name, $region);
            if (!$result['error']) {
                update_post_meta($post_id, $meta_key, $result['url'], $meta_value);
            } else {
                error_log('Failed to upload image to S3: ' . $result['url']);
            }
        } elseif (is_array($meta_value)) {
            array_walk_recursive($meta_value, function (&$value) use ($access_key, $secret_key, $bucket_name, $region) {
                if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $value) && strpos($value, $bucket_name) === false) {
                    $result = upload_image_to_s3($value, $access_key, $secret_key, $bucket_name, $region);
                    if (!$result['error']) {
                        $value = $result['url'];
                    }
                }
            });
            // Update post meta with the updated array of URLs
            $updated_meta_value = maybe_serialize($meta_value);
            update_post_meta($post_id, $meta_key, $updated_meta_value);
        }
    }

    // Get image URLs from post content
    $post_content = get_post_field('post_content', $post_id);
    preg_match_all('/\bhttps?:\/\/[^\s]+?\.(?:jpg|jpeg|png|gif|svg)\b/i', $post_content, $matches);
    $content_image_urls = isset($matches[0]) ? $matches[0] : array();

    // Handle relative paths
    preg_match_all('/\b(?:\/[^\s]+?)\.(?:jpg|jpeg|png|gif|svg)\b/i', $post_content, $relative_matches);
    $relative_image_urls = isset($relative_matches[0]) ? $relative_matches[0] : array();
    $content_image_urls = array_merge($content_image_urls, array_map(fn($path) => site_url($path), $relative_image_urls));

    foreach ($content_image_urls as $image_url) {
        if (strpos($image_url, $bucket_name) === false) {
            $result = upload_image_to_s3($image_url, $access_key, $secret_key, $bucket_name, $region);
            if (!$result['error']) {
                $post_content = str_replace($image_url, $result['url'], $post_content);
            } else {
                error_log('Failed to upload image to S3: ' . $result['url']);
            }
        }
    }

    // Update post content with new URLs
    wp_update_post(array('ID' => $post_id, 'post_content' => $post_content));

    wp_send_json_success(array('message' => 'Images synced successfully.'));
}

add_action('wp_ajax_sync_images_to_s3', 'sync_images_to_s3');

function add_sync_to_s3_meta_box()
{
    add_meta_box(
        'sync-to-s3-meta-box',
        'Sync to S3',
        'render_sync_to_s3_meta_box',
        array('post', 'page'),
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'add_sync_to_s3_meta_box');

function render_sync_to_s3_meta_box($post)
{
    global $wpdb;
    $post_id = $post->ID;
    $bucket_name = get_option('aws_bucket_name');

    // Get image URLs from post content
    preg_match_all('/\bhttps?:\/\/[^\s]+?\.(?:jpg|jpeg|png|gif|svg)\b/i', $post->post_content, $matches);
    $content_image_urls = isset($matches[0]) ? $matches[0] : array();

    // Handle relative paths
    preg_match_all('/\b(?:\/[^\s]+?)\.(?:jpg|jpeg|png|gif|svg)\b/i', $post->post_content, $relative_matches);
    $relative_image_urls = isset($relative_matches[0]) ? $relative_matches[0] : array();
    $content_image_urls = array_merge($content_image_urls, array_map(fn($path) => site_url($path), $relative_image_urls));

    // Get all post meta data for the post
    $meta_image_urls = array();
    $post_meta = get_post_meta($post_id);
    foreach ($post_meta as $meta_key => $meta_value) {
        $meta_value = maybe_unserialize($meta_value[0]);
        if (is_string($meta_value)) {
            preg_match_all('/\bhttps?:\/\/[^\s]+?\.(?:jpg|jpeg|png|gif|svg)\b/i', $meta_value, $matches);
            $meta_image_urls = array_merge($meta_image_urls, isset($matches[0]) ? $matches[0] : array());

            // Handle relative paths in meta values
            preg_match_all('/\b(?:\/[^\s]+?)\.(?:jpg|jpeg|png|gif|svg)\b/i', $meta_value, $relative_matches);
            $relative_meta_image_urls = isset($relative_matches[0]) ? $relative_matches[0] : array();
            $meta_image_urls = array_merge($meta_image_urls, array_map(fn($path) => site_url($path), $relative_meta_image_urls));
        } elseif (is_array($meta_value)) {
            array_walk_recursive($meta_value, function ($value) use (&$meta_image_urls) {
                if (is_string($value)) {
                    preg_match_all('/\bhttps?:\/\/[^\s]+?\.(?:jpg|jpeg|png|gif|svg)\b/i', $value, $matches);
                    $meta_image_urls = array_merge($meta_image_urls, isset ($matches[0]) ? $matches[0] : array ());

                    // Handle relative paths in meta values
                    preg_match_all('/\b(?:\/[^\s]+?)\.(?:jpg|jpeg|png|gif|svg)\b/i', $value, $relative_matches);
                    $relative_meta_image_urls = isset ($relative_matches[0]) ? $relative_matches[0] : array ();
                    $meta_image_urls = array_merge($meta_image_urls, array_map(fn($path) => site_url($path), $relative_meta_image_urls));
                }
            });
        }
    }

    // Merge and count unique image URLs
    $image_urls = array_unique(array_merge($content_image_urls, $meta_image_urls));
    $total_image_count = count($image_urls);
    $same_bucket_image_count = count(array_filter($image_urls, fn($url) => strpos($url, $bucket_name) !== false));
    $unique_image_count = $total_image_count - $same_bucket_image_count;

    // Output the meta box content
    echo '<button id="sync-to-s3-btn-edit-page" class="button" ' . ($unique_image_count <= 0 ? 'disabled' : '') . '>Sync to S3</button>';
    echo '<p>Total Images: ' . $total_image_count . '</p>';
    echo '<p>Uploaded Images: ' . $same_bucket_image_count . '</p>';
    echo '<p>Images to Upload: ' . $unique_image_count . '</p>';
    wp_nonce_field('sync_images_to_s3_nonce', 'sync_images_to_s3_nonce_field');
}
