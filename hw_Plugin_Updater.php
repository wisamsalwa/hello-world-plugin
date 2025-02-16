<?php
/**
 * Plugin Updater
 * A generic update functionality for WordPress plugins.
 * Version: 1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class hw_Plugin_Updater
{
    private $plugin_slug;
    private $plugin_file;
    private $update_url;

    public function __construct($plugin_slug, $plugin_file, $update_url)
    {
        $this->plugin_slug = $plugin_slug;
        $this->plugin_file = $plugin_file;
        $this->update_url = $update_url;

        // Hook into the update process **** both line that i deleted to upload plugin to wordpress.org
        add_filter('site_transient_update_plugins', array($this, 'check_for_updates'));
        add_filter('upgrader_post_install', array($this, 'rename_update_folder'), 10, 3);
    }

    // Check for updates
    public function check_for_updates($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Get the remote update.json file
        $remote = wp_remote_get($this->update_url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/json'
            )
        ));

        // Check if the request was successful
        if (!is_wp_error($remote) && isset($remote['response']['code']) && $remote['response']['code'] == 200 && !empty($remote['body'])) {
            $remote_data = json_decode($remote['body']);

            // Ensure the remote data is valid
            if (is_object($remote_data) && isset($remote_data->version)) {
                // Check if a new version is available
                if (version_compare($transient->checked[$this->plugin_file], $remote_data->version, '<')) {
                    $transient->response[$this->plugin_file] = (object) array(
                        'slug' => $this->plugin_slug,
                        'plugin' => $this->plugin_file,
                        'new_version' => $remote_data->version,
                        'url' => $remote_data->download_url,
                        'package' => $remote_data->download_url,
                        'tested' => $remote_data->tested,
                        'requires' => $remote_data->requires
                    );
                }
            }
        }

        return $transient;
    }

    // Rename the folder after the update
    public function rename_update_folder($true, $hook_extra, $result)
    {
        global $wp_filesystem;

        // Get the plugin directory
        $plugin_dir = trailingslashit(WP_PLUGIN_DIR) . $this->plugin_slug . '-main';

        // Check if the folder exists
        if ($wp_filesystem->exists($plugin_dir)) {
            // Rename the folder
            $new_plugin_dir = trailingslashit(WP_PLUGIN_DIR) . $this->plugin_slug;
            $wp_filesystem->move($plugin_dir, $new_plugin_dir);
        }

        return $result;
    }
}