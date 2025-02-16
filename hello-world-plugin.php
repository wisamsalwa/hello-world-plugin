<?php
/*
Plugin Name: Hello World
Text Domain: hello-world-plugin
Description: A simple plugin to display "Hello World" in the WordPress admin panel, with plugin check for update.
Version: 2.0
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


function hello_world_plugin_info($false, $action, $args)
{
    if ($args->slug === 'hello-world-plugin') {
        // Get the remote update.json file
        $remote = wp_remote_get('https://raw.githubusercontent.com/wisamsalwa/hello-world-plugin/refs/heads/main/update.json', array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/json'
            )
        ));

        if (!is_wp_error($remote) && isset($remote['response']['code']) && $remote['response']['code'] == 200 && !empty($remote['body'])) {
            $remote_data = json_decode($remote['body']);
            return (object) array(
                'name' => 'Hello World',
                'slug' => 'hello-world-plugin',
                'version' => $remote_data->version,
                'last_updated' => $remote_data->last_updated,
                'download_link' => $remote_data->download_url,
                'sections' => $remote_data->sections,
                'requires' => $remote_data->requires,
                'tested' => $remote_data->tested
            );
        }
    }

    return $false;
}
add_filter('plugins_api', 'hello_world_plugin_info', 10, 3);

 

