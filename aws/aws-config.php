<?php

// Add AWS settings section and fields
function add_aws_settings_fields()
{
    add_settings_section(
        'aws_settings_section',
        '',
        'aws_settings_section_callback',
        'aws-config'
    );

    add_settings_field(
        'aws_access_key_id',
        'Access Key ID',
        'aws_access_key_id_callback',
        'aws-config',
        'aws_settings_section'
    );

    add_settings_field(
        'aws_secret_access_key',
        'Secret Access Key',
        'aws_secret_access_key_callback',
        'aws-config',
        'aws_settings_section'
    );

    add_settings_field(
        'aws_bucket_name',
        'Bucket Name',
        'aws_bucket_name_callback',
        'aws-config',
        'aws_settings_section'
    );

    add_settings_field(
        'aws_region',
        'Region',
        'aws_region_callback',
        'aws-config',
        'aws_settings_section'
    );

    register_setting('aws_config_group', 'aws_access_key_id', 'sanitize_aws_access_key_id');
    register_setting('aws_config_group', 'aws_secret_access_key', 'sanitize_aws_secret_access_key');
    register_setting('aws_config_group', 'aws_bucket_name', 'sanitize_aws_bucket_name');
    register_setting('aws_config_group', 'aws_region', 'sanitize_aws_region');
}




function aws_access_key_id_callback()
{
    $value = get_option('aws_access_key_id', '');
    echo '<input type="text" name="aws_access_key_id" value="' . esc_attr($value) . '" />';
}

function aws_secret_access_key_callback()
{
    $value = get_option('aws_secret_access_key', '');
    echo '<input type="password" name="aws_secret_access_key" value="' . esc_attr($value) . '" />';
}

function aws_bucket_name_callback()
{
    $value = get_option('aws_bucket_name', '');
    echo '<input type="text" name="aws_bucket_name" value="' . esc_attr($value) . '" />';
}

function aws_region_callback()
{
    $value = get_option('aws_region', '');
    echo '<input type="text" name="aws_region" value="' . esc_attr($value) . '" />';
}

function sanitize_aws_access_key_id($input)
{
    // Validate the access key ID here
    // Example: Check if the input is not empty
    if (empty($input)) {
        add_settings_error('aws_access_key_id', 'empty_access_key_id', 'Access Key ID cannot be empty.', 'error');
        return get_option('aws_access_key_id');
    }

    // Return sanitized input
    return sanitize_text_field($input);
}

function sanitize_aws_secret_access_key($input)
{
    // Validate the secret access key here
    // Example: Check if the input is not empty
    if (empty($input)) {
        add_settings_error('aws_secret_access_key', 'empty_secret_access_key', 'Secret Access Key cannot be empty.', 'error');
        return get_option('aws_secret_access_key');
    }

    // Return sanitized input
    return sanitize_text_field($input);
}

function sanitize_aws_bucket_name($input)
{
    // Validate the bucket name here
    // Example: Check if the input is not empty
    if (empty($input)) {
        add_settings_error('aws_bucket_name', 'empty_bucket_name', 'Bucket Name cannot be empty.', 'error');
        return get_option('aws_bucket_name');
    }

    // Return sanitized input
    return sanitize_text_field($input);
}

function sanitize_aws_region($input)
{
    // Validate the region here
    // Example: Check if the input is not empty
    if (empty($input)) {
        add_settings_error('aws_region', 'empty_region', 'Region cannot be empty.', 'error');
        return get_option('aws_region');
    }

    // Return sanitized input
    return sanitize_text_field($input);
}

add_action('admin_init', 'add_aws_settings_fields');

// Add AWS Config menu
function aws_config_menu()
{
    add_options_page(
        'AWS',
        'AWS',
        'manage_options',
        'aws-config',
        'aws_config_page'
    );
}

function aws_config_page()
{
    ?>
    <div class="wrap">
        <h1>AWS Configuration</h1>
        <p>Configure your AWS credentials. Your settings will not be stored in our database.</p>
        <form method="post" action="options.php">
            <?php
            settings_fields('aws_config_group');
            do_settings_sections('aws-config');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_menu', 'aws_config_menu');

?>