<?php

class GitHub_Updater {
    private $slug;
    private $plugin_data;
    private $github_api_result;
    private $plugin_file;
    private $username;
    private $repo;

    public function __construct($plugin_file, $username, $repo) {
        add_filter("pre_set_site_transient_update_plugins", array($this, "set_transient"));
        add_filter("plugins_api", array($this, "set_plugin_info"), 10, 3);
        add_filter("upgrader_post_install", array($this, "post_install"), 10, 3);

        $this->plugin_file = $plugin_file;
        $this->username = $username;
        $this->repo = $repo;
    }

    private function init_plugin_data() {
        $this->slug = plugin_basename($this->plugin_file);
        $this->plugin_data = get_plugin_data($this->plugin_file);
    }

    private function get_repo_release_info() {
        if (!empty($this->github_api_result)) {
            return;
        }

        $url = "https://api.github.com/repos/{$this->username}/{$this->repo}/releases";
        $this->github_api_result = wp_remote_retrieve_body(wp_remote_get($url));
        if (!empty($this->github_api_result)) {
            $this->github_api_result = @json_decode($this->github_api_result);
        }

        if (is_array($this->github_api_result)) {
            $this->github_api_result = $this->github_api_result[0];
        }
    }

    public function set_transient($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $this->init_plugin_data();
        $this->get_repo_release_info();

        $do_update = version_compare($this->github_api_result->tag_name, $transient->checked[$this->slug]);

        if ($do_update == 1) {
            $package = $this->github_api_result->zipball_url;
            $transient->response[$this->slug] = (object) array(
                'new_version' => $this->github_api_result->tag_name,
                'slug' => $this->slug,
                'url' => $this->plugin_data["PluginURI"],
                'package' => $package,
            );
        }

        return $transient;
    }

    public function set_plugin_info($false, $action, $response) {
        $this->init_plugin_data();
        $this->get_repo_release_info();

        if (empty($response->slug) || $response->slug != $this->slug) {
            return false;
        }

        $response->last_updated = $this->github_api_result->published_at;
        $response->slug = $this->slug;
        $response->plugin_name = $this->plugin_data["Name"];
        $response->version = $this->github_api_result->tag_name;
        $response->author = $this->plugin_data["AuthorName"];
        $response->homepage = $this->plugin_data["PluginURI"];
        $response->download_link = $this->github_api_result->zipball_url;

        $response->sections = array(
            'description' => $this->plugin_data["Description"],
            'changelog' => $this->github_api_result->body,
        );

        return $response;
    }

    public function post_install($true, $hook_extra, $result) {
        global $wp_filesystem;
        $plugin_folder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($this->slug);
        $wp_filesystem->move($result['destination'], $plugin_folder);
        $result['destination'] = $plugin_folder;
        $activate = activate_plugin(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->slug);
        return $result;
    }
}
