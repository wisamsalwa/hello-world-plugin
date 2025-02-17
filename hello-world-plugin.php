<?php
/*
Plugin Name: Hello World
Text Domain: hello-world-plugin
Description: A simple plugin to display "Hello World" in the WordPress admin panel, with plugin check for update.
Version: 4.8
Author: Wisam Essalwa
Author URI: https://github.com/wisamsalwa
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/


require_once plugin_dir_path(__FILE__) . 'hw_Plugin_Updater.php';

new hw_Plugin_Updater(
    'hello-world-plugin', // Plugin slug
    'hello-world-plugin/hello-world-plugin.php', // Plugin file
    'https://raw.githubusercontent.com/wisamsalwa/hello-world-plugin/refs/heads/main/update.json'
);


// Function to display "Hello World" in the admin panel
function hello_world_admin_notice()
{
    // Get plugin data
    $plugin_data = get_plugin_data(__FILE__);
    $plugin_version = $plugin_data['Version'];

    // Display the message with the plugin version
    ?>
    <div class="notice notice-success is-dismissible">
        <p>Hello World! (Plugin Version: <?php echo esc_html($plugin_version); ?>)</p>
    </div>
    <?php
}

// Hook the function to the admin_notices action
add_action('admin_notices', 'hello_world_admin_notice');

function hello_world_plugin_info($false, $action, $args) {
    // Check if the request is for your plugin
    if (isset($args->slug) && $args->slug === 'hello-world-plugin') {
        // Remote URL to fetch plugin information
        $remote_url = 'https://raw.githubusercontent.com/wisamsalwa/hello-world-plugin/refs/heads/main/update.json';

        // Fetch the remote data
        $remote_response = wp_remote_get($remote_url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        ));

        // Check for errors in the HTTP request
        if (is_wp_error($remote_response)) {
            error_log('Error fetching remote data: ' . $remote_response->get_error_message());
            return $false;
        }

        // Get the response body
        $remote_body = wp_remote_retrieve_body($remote_response);

        // Log the raw response body for debugging
        error_log('Remote response body: ' . $remote_body);

        // Decode the JSON response as an array
        $remote_data = json_decode($remote_body, true);

        // Log the decoded data for debugging
        error_log('Decoded remote data: ' . print_r($remote_data, true));

        // Ensure the remote data contains the required sections
        if (empty($remote_data['sections'])) {
            error_log('Remote data is missing sections.');
            return $false;
        }

        // Plugin information array
        $plugin_info = array(
            'name' => 'Hello World Plugin',
            'slug' => 'hello-world-plugin',
            'version' => $remote_data['version'] ?? '1.0', // Use remote version or fallback
            'author' => $remote_data['author'] ?? 'Wisam Essalwa', // Use remote author or fallback
            'author_profile' => $remote_data['author_profile'] ?? 'https://github.com/wisamsalwa',
            'last_updated' => $remote_data['last_updated'] ?? date('Y-m-d'), // Use remote date or fallback
            'sections' => $remote_data['sections'], // Use sections from remote data
            'download_link' => $remote_data['download_link'] ?? 'https://github.com/wisamsalwa/hello-world-plugin/archive/refs/heads/main.zip',
        );

        // Convert the array to an object and return it
        return (object) $plugin_info;
    }

    // Return false if the request is not for your plugin
    return $false;
}
add_filter('plugins_api', 'hello_world_plugin_info', 10, 3);





