<?php
/**
 * Plugin Name: AWS Image Sync
 * Plugin URI: https://vallarasuk.com/plugins/aws-image-sync
 * Description: Sync Images to Amazon S3 Bucket and auto-replace image links.
 * Version: 1.0.0
 * Author: Vallarasu K
 * Author URI: https://vallarasuk.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: aws_image_sync
 * Domain Path: /languages
 * 
 * @package Aws_image_sync
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'aws/aws-s3-sync.php';
include_once plugin_dir_path(__FILE__) . 'aws/aws-config.php';

// Define plugin version
define('AWS_IMAGE_SYNC_VERSION', '1.0.0');

// Activation and deactivation hooks
function activate_aws_image_sync()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-aws_image_sync-activator.php';
    Aws_image_sync_Activator::activate();
}

function deactivate_aws_image_sync()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-aws_image_sync-deactivator.php';
    Aws_image_sync_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_aws_image_sync');
register_deactivation_hook(__FILE__, 'deactivate_aws_image_sync');

// Include core plugin class
require plugin_dir_path(__FILE__) . 'includes/class-aws_image_sync.php';

// Initialize the plugin
function run_aws_image_sync()
{
    $plugin = new Aws_image_sync();
    $plugin->run();
}
run_aws_image_sync();
