<?php
/*
Plugin Name: Staff Manager
Plugin URI: https://github.com/idiv-biodiversity/staff-manager
Description: WordPress plugin for managing staff at <a href="https://idiv.de" target="_blank">iDiv</a>
Version: 1.0.5-alpha
Author: Christian Langer
Author URI: https://github.com/christianlanger
Text Domain: staff-manager
GitHub Plugin URI: https://github.com/idiv-biodiversity/staff-manager
*/

/* ################################################################ */
/* BASIC INIT STUFF */
/* ################################################################ */

//$github_token = getenv('GITHUB_TOKEN'); // Load GitHub token from environment variable
$github_token = file_get_contents('/var/www/github_token'); // Read the GitHub token from the file

// Register "Groups" as CPT
add_action('init', 'register_groups_post_type');
function register_groups_post_type() {
    $labels = array(
        'name'               => _x('Groups', 'post type general name', 'staff-manager'),
        'singular_name'      => _x('Group', 'post type singular name', 'staff-manager'),
        'menu_name'          => _x('Groups', 'admin menu', 'staff-manager'),
        'name_admin_bar'     => _x('Group', 'add new on admin bar', 'staff-manager'),
        'add_new'            => _x('Add New', 'group', 'staff-manager'),
        'add_new_item'       => __('Add New Group', 'staff-manager'),
        'new_item'           => __('New Group', 'staff-manager'),
        'edit_item'          => __('Edit Group', 'staff-manager'),
        'view_item'          => __('View Group', 'staff-manager'),
        'all_items'          => __('All Groups', 'staff-manager'),
        'search_items'       => __('Search Groups', 'staff-manager'),
        'parent_item_colon'  => __('Parent Groups:', 'staff-manager'),
        'not_found'          => __('No groups found.', 'staff-manager'),
        'not_found_in_trash' => __('No groups found in Trash.', 'staff-manager'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false, // Hide from main menu
        'query_var'          => true,
        'rewrite'            => array('slug' => 'group'),
        'capability_type'    => 'page',
        'hierarchical'       => true,
        'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'page-attributes'),
        'show_in_rest'       => true // Ensure REST API support
    );

    register_post_type('groups', $args);
}

// Define translations
function sm_get_translations() {
    return array(
        'affiliations'      => __('Affiliations', 'staff-manager'),
        'postal_address'    => __('Postal address', 'staff-manager'),
        'room'              => __('Room', 'staff-manager'),
        'contact_details'   => __('Contact details', 'staff-manager'),
        'email'             => __('Email', 'staff-manager'),
        'phone'             => __('Phone', 'staff-manager'),
        'mobile'            => __('Mobile', 'staff-manager'),
        'websites'          => __('Websites', 'staff-manager'),
        'full_profile'      => __('Full profile', 'staff-manager'),
        'group_pre'         => __('Team Members of the', 'staff-manager'),
        'group_after'       => __('Research Group', 'staff-manager'),
        'name'              => __('Name', 'staff-manager'),
        'groups'            => __('Groups', 'staff-manager'),
        'contact'           => __('Contact', 'staff-manager'),
        'search_staff'      => __('Search by name', 'staff-manager'),
        'all_groups'        => __('All Groups', 'staff-manager'),
        'no_staff_found'    => __('No staff found', 'staff-manager')
    );
}


// Add "Groups" as a sub-menu item under "Staff Manager"
add_action('admin_menu', 'register_staff_manager_submenu');
function register_staff_manager_submenu() {
    add_submenu_page(
        'edit.php?post_type=staff-manager', // Parent slug
        __('All Groups', 'staff-manager'),      // Page title
        __('All Groups', 'staff-manager'),      // Menu title
        'manage_options',                   // Capability required to see the menu
        'edit.php?post_type=groups'         // Menu slug
    );
}

// Shared function to enqueue Bootstrap, Font Awesome, and Selectpicker
function enqueue_shared_assets() {
    // Ensure jQuery is enqueued
    wp_enqueue_script('jquery');

    // Bootstrap 5
    if ( ! wp_style_is( 'bootstrap-css', 'enqueued' ) ) {
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    }
    if ( ! wp_script_is( 'bootstrap-js', 'enqueued' ) ) {
        wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
    }

    // Selectpicker (Bootstrap 5 compatible)
    wp_enqueue_style('bootstrap-select-css', 'https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css');
    wp_enqueue_script('bootstrap-select-js', 'https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js', array('jquery', 'bootstrap-js'), null, true);

    // Font Awesome CSS
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4', 'all');
}

// Enqueue scripts and styles for Admin
add_action('admin_enqueue_scripts', 'enqueue_backend_styles');
function enqueue_backend_styles() {
    enqueue_shared_assets(); // Load shared assets
    wp_enqueue_style('block-backend', plugin_dir_url(__FILE__) . 'css/block-backend.css', array(), '1.0.0');
}

// Enqueue scripts and styles for Frontend
add_action('wp_enqueue_scripts', 'enqueue_frontend_styles');
function enqueue_frontend_styles() {
    enqueue_shared_assets(); // Load shared assets

    // Bootstrap Table (Bootstrap 5 compatible)
    wp_enqueue_style('bootstrap-table-css', 'https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.css');
    wp_enqueue_script('bootstrap-table-js', 'https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.js', array('jquery', 'bootstrap-js'), null, true);

    wp_enqueue_style('block-frontend', plugin_dir_url(__FILE__) . 'css/block-frontend.css', array(), '1.0.0');
    //wp_enqueue_script('custom-frontend-js', plugin_dir_url(__FILE__) . 'js/custom-frontend.js', array('jquery'), null, true);
}


// Enqueue block scripts
add_action('enqueue_block_editor_assets', 'staff_manager_blocks_enqueue');
function staff_manager_blocks_enqueue() {

    wp_enqueue_script(
        'staff-manager-blocks',
        plugin_dir_url(__FILE__) . 'js/blocks.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-i18n'),
        filemtime(plugin_dir_path(__FILE__) . 'js/blocks.js')
    );
    
}

/* ################################################################ */
/*                      UPDATE PROCESS */
/* ################################################################ */

// Force WordPress to check for plugin updates
//delete_site_transient('update_plugins');

// Update Check
add_filter('site_transient_update_plugins', 'plugin_update_check');
function plugin_update_check($transient) {
    
    global $github_token;

    if (empty($transient->checked)) {
        return $transient;
    }

    $plugin_slug = plugin_basename(__FILE__);
    $response = wp_remote_get('https://api.github.com/repos/idiv-biodiversity/staff-manager/releases', array(
        'headers' => array(
            'Authorization' => 'token ' . $github_token,
            'User-Agent'    => 'Staff Manager'
        )
    ));

    if (is_wp_error($response)) {
        error_log('Error: ' . $response->get_error_message());
        return $transient;
    }

    $github_data = json_decode(wp_remote_retrieve_body($response), true);

    // Get the first release, assuming it’s the latest
    $latest_release = $github_data[0];
    
    // Parse the version from GitHub (removing 'v' prefix if present)
    $new_version = str_replace('v', '', $latest_release['name']);

    // Compare the version from GitHub with the plugin version
    if (version_compare($transient->checked[$plugin_slug], $new_version, '<')) {
        $plugin = array(
            'slug'        => $plugin_slug,
            'new_version' => $new_version,
            'url'         => $latest_release['html_url'],
            'package'     => $latest_release['zipball_url'],
        );
        $transient->response[$plugin_slug] = (object) $plugin;
    }

    return $transient;
}

// Rename the plugin folder after the update and keep it active
add_filter('upgrader_post_install', 'rename_plugin_folder', 10, 3);
function rename_plugin_folder($response, $hook_extra, $result) {
    global $wp_filesystem;

    if ($hook_extra['plugin'] !== plugin_basename(__FILE__)) {
        return $response;
    }

    $proper_folder_name = 'staff-manager';
    $destination = WP_PLUGIN_DIR . '/' . $proper_folder_name;

    // Rename the plugin directory if it's different
    if ($result['destination_name'] !== $proper_folder_name) {
        $wp_filesystem->move($result['destination'], $destination);
        $result['destination'] = $destination;
        $result['destination_name'] = $proper_folder_name;
    }

    // Re-activate the plugin after renaming
    activate_plugin($proper_folder_name . '/' . plugin_basename(__FILE__));

    return $response;
}

// View details window for new release
add_filter('plugins_api', 'plugin_update_details', 10, 3);
function plugin_update_details($false, $action, $args) {
    
    global $github_token;
    
    // Check if the action is for plugin information
    if ($action !== 'plugin_information') {
        return $false;
    }

    $plugin_slug = plugin_basename(__FILE__); 

    if ($args->slug !== $plugin_slug) {
        return $false;
    }

    // Fetch release details from GitHub
    $response = wp_remote_get('https://api.github.com/repos/idiv-biodiversity/staff-manager/releases', array(
        'headers' => array(
            'Authorization' => 'token ' . $github_token,
            'User-Agent'    => 'Staff Manager'
        )
    ));

    if (is_wp_error($response)) {
        error_log('Error: ' . $response->get_error_message());
        return $false; // Return false if the request failed
    }

    $github_data = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($github_data) || !is_array($github_data)) {
        return $false; // Return false if there is no release data
    }

    // Get the first release, assuming it’s the latest
    $latest_release = $github_data[0];

    $plugin_info = new stdClass();
    $plugin_info->name = 'Staff Manager';
    $plugin_info->slug = $plugin_slug;
    $plugin_info->version = str_replace('v', '', $latest_release['name']);
    $plugin_info->author = '<a href="https://github.com/ChristianLanger">Christian Langer</a>';
    $plugin_info->homepage = 'https://github.com/idiv-biodiversity/staff-manager';
    $plugin_info->download_link = $latest_release['zipball_url'];
    $plugin_info->sections = array(
        'description' => 'WordPress plugin for managing staff at <a href="https://idiv.de" target="_blank">iDiv</a>',
        'changelog' => '<h4>Version ' . str_replace('v', '', $latest_release['name']) . '</h4>'
    );
    $plugin_info->banners = array(
        'low' => 'https://home.uni-leipzig.de/idiv/main-page/banner-low-res.jpg',
        'high' => 'https://home.uni-leipzig.de/idiv/main-page/banner-high-res.jpg'
    );

    return $plugin_info;
}


/* ################################################################ */
/*                              FUNCTIONS */
/* ################################################################ */

/* ################################################################ */
/* Basic Information Profile Block */
/* ################################################################ */

add_action('init', 'register_basic_info_block');
function register_basic_info_block() {
    register_block_type('staff-manager/basic-information', array(
        'render_callback' => 'render_basic_info_block'
    ));
}
function render_basic_info_block($attributes) {
    global $post;
    $translations = sm_get_translations();
    $post_id = $post->ID;

    // Get the featured image URL
    $featured_image_url = get_the_post_thumbnail($post_id, 'full', ['class' => 'img-fluid rounded-circle']);

    // Translate the position using WPML
    //$position = icl_translate('staff-manager', 'position_' . md5($attributes['position']), $attributes['position']);


    ob_start();
    ?>
    <div class="profile-info">
        <div class="row">
            <div class="col-md-8">
                <div class="basic-info">
                    <h2>
                        <?php echo esc_html(isset($attributes['title']) ? $attributes['title'] : ''); ?> 
                        <?php echo esc_html(isset($attributes['firstname']) ? $attributes['firstname'] : ''); ?> 
                        <?php echo esc_html(isset($attributes['lastname']) ? $attributes['lastname'] : ''); ?>
                    </h2>
                    <p><?php echo esc_html($attributes['position']); ?></p>
                    <?php if (!empty($attributes['groups'])):
                        $group_links = array(); ?>
                        <div class="groups">
                            <?php foreach ($attributes['groups'] as $group):
                                // Find the group ID based on the group title
                                $group_post = get_page_by_title($group, OBJECT, 'groups');

                                // If the post is found, retrieve its ID
                                if ($group_post) {
                                    $group_id = $group_post->ID;

                                    // Retrieve the custom URL set by the "Page Links To" plugin
                                    $group_link = get_post_meta($group_id, '_links_to', true);

                                    // Fallback to permalink if custom URL is not set
                                    if (!$group_link) {
                                        $group_link = get_permalink($group_id);
                                    }

                                    // Get the title of the group
                                    $group_title = get_the_title($group_id);

                                    // Create the link and store it in an array
                                    $group_links[] = '<a href="' . esc_url($group_link) . '" target="_blank">' . esc_html($group_title) . '</a>';
                                }
                            endforeach;

                            // Output the links, separated by commas
                            echo implode(', ', $group_links); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="affiliations">
                    <h4 class="affiliations-title"><?php echo $translations['affiliations']; ?></h4>
                    <p class="affiliations-body"><?php echo wp_kses_post($attributes['affiliations']); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <?php echo $featured_image_url; ?>
            </div>
            <div class="col-md-4">
                <div class="address">
                    <h4 class="address-title"><?php echo $translations['postal_address']; ?></h4>
                    <p class="address-body">
                        <div><?php echo wp_kses_post($attributes['address']); ?></div>
                        <?php if (!empty($attributes['room'])): ?>
                            <div><?php echo $translations['room']; ?>: <?php echo esc_html($attributes['room']); ?></div>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact">
                    <h4 class="contact-title"><?php echo $translations['contact_details']; ?></h4>
                    <p class="contact-body">
                        <?php if (!empty($attributes['email'])): ?>
                            <div><?php echo $translations['email']; ?>: <a href='mailto:<?php echo esc_html($attributes['email']); ?>'><?php echo esc_html($attributes['email']); ?></a></div>
                        <?php endif; ?>
                        <?php if (!empty($attributes['phone'])): ?>
                            <div><?php echo $translations['phone']; ?>: <?php echo esc_html($attributes['phone']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($attributes['mobile'])): ?>
                            <div><?php echo $translations['mobile']; ?>: <?php echo esc_html($attributes['mobile']); ?></div>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="websites">
                    <h4 class="websites-title"><?php echo $translations['websites']; ?></h4>
                    <p class="websites-body">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if (!empty($attributes['link' . $i]) && !empty($attributes['link' . $i . 'text'])): ?>
                                <div><a href="<?php echo esc_url($attributes['link' . $i]); ?>" target="_blank"><?php echo esc_html($attributes['link' . $i . 'text']); ?></a></div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}


/* ################################################################ */
/* Profilliste (vollständig) */
/* ################################################################ */

add_action('init', 'register_all_staff_block');
function register_all_staff_block() {
    register_block_type('staff-manager/all-staff', array(
        'render_callback' => 'render_all_staff_block',
    ));
}
function find_custom_block($blocks, $block_name) {
    foreach ($blocks as $block) {
        // Check if the block is the one we're looking for
        if ($block['blockName'] === $block_name) {
            return $block;
        }
        // If the block has inner blocks, search those recursively
        if (isset($block['innerBlocks']) && !empty($block['innerBlocks'])) {
            $found_block = find_custom_block($block['innerBlocks'], $block_name);
            if ($found_block) {
                return $found_block;
            }
        }
    }
    return null;
}

function render_all_staff_block($attributes) {
    $translations = sm_get_translations();
    // Query for all staff_manager posts
    $query = new WP_Query(array(
        'post_type' => 'staff-manager',
        'posts_per_page' => -1 // all posts of the specified post type without any limit, any pagination
    ));

    // Start output buffering
    ob_start();

    // Check if there are any posts to display
    if ($query->have_posts()) {
        // Output search and filter controls
        ?>
        <div class="filters row">
            <div class="col-md-8 has-search ps-0">
                <span class="fa fa-search form-control-feedback"></span>
                <input type="text" id="search-input" class="form-control" placeholder="<?php echo esc_attr($translations['search_staff']); ?>" onkeyup="filterStaff()">
            </div>
            <div class="col-md-4 pe-0">
                <select id="group-select" class="form-control" onchange="filterByGroup()">
                    <option value=""><?php echo esc_attr($translations['all_groups']); ?></option>
                    <?php
                    // Output unique groups for filtering
                    $groups = []; // Collect unique groups from staff posts
                    while ($query->have_posts()) {
                        $query->the_post();
                        $post_content = get_the_content();
                        $blocks = parse_blocks($post_content);
                        $block = find_custom_block($blocks, 'staff-manager/basic-information');
                        if ($block && isset($block['attrs']['groups']) && is_array($block['attrs']['groups'])) {
                            $groups = array_merge($groups, $block['attrs']['groups']);
                        }
                    }
                    $groups = array_unique($groups);
                    foreach ($groups as $group) {
                        echo '<option value="' . esc_attr($group) . '">' . esc_html($group) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div id="no-staff-message" style="display: none;">
            <?php echo esc_attr($translations['no_staff_found']); ?>
        </div>

        <div id="staff-list">
            <table id="staffTable" data-toggle='table' class="table table-striped profile-table">
                <thead>
                    <tr>
                        <th data-sortable='true'><?php echo esc_html($translations['name']); ?></th>
                        <th data-sortable='true'><?php echo esc_html($translations['groups']); ?></th>
                        <th data-sortable='true'><?php echo esc_html($translations['contact']); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Reset query and loop again to output rows
                    $query->rewind_posts();
                    while ($query->have_posts()) {
                        $query->the_post();
                        $post_id = get_the_ID();
                        $post_content = get_the_content();
                        $profile_url = get_permalink($post_id);

                        // Parse the post content to extract the block attributes
                        $blocks = parse_blocks($post_content);
                        $block = find_custom_block($blocks, 'staff-manager/basic-information');

                        if ($block) {
                            $firstname = isset($block['attrs']['firstname']) ? $block['attrs']['firstname'] : '';
                            $lastname = isset($block['attrs']['lastname']) ? $block['attrs']['lastname'] : '';
                            $position = isset($block['attrs']['position']) ? $block['attrs']['position'] : '';
                            $groups = isset($block['attrs']['groups']) && is_array($block['attrs']['groups']) ? $block['attrs']['groups'] : [];
                            $email = isset($block['attrs']['email']) ? $block['attrs']['email'] : '';
                            $phone = isset($block['attrs']['phone']) ? $block['attrs']['phone'] : '';

                            $groups_string = implode(', ', $groups);

                            echo "<tr class='staff-entry' data-name='" . esc_attr($firstname . ' ' . $lastname) . "' data-groups='" . esc_attr(implode(', ', $groups)) . "'>";
                            echo "<td><a href='" . $profile_url . "' target='_blank'>" . esc_html($firstname) . " " . esc_html($lastname) . "</a><div>" . esc_html($position) . "</div></td>";
                            echo "<td>" . esc_html($groups_string) . "</td>";
                            echo "<td><div>{$translations['phone']}: " . esc_html($phone) . "</div><div>{$translations['email']}: <a href='mailto:" . esc_html($email) . "' target='_blank'>" . esc_html($email) . "</a></div></td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <script>
        function filterStaff() {
            var input = document.getElementById('search-input').value.toLowerCase();
            var staffEntries = document.querySelectorAll('.staff-entry');
            var noStaffMessage = document.getElementById('no-staff-message');
            var tableHead = document.querySelector('#staffTable thead');

            var visible = false;
            staffEntries.forEach(function(entry) {
                var name = entry.getAttribute('data-name').toLowerCase();
                var matches = name.includes(input);
                entry.style.display = matches ? '' : 'none';
                if (matches) visible = true;
            });

            noStaffMessage.style.display = visible ? 'none' : 'block';
            tableHead.style.display = visible ? '' : 'none'; // Hide thead if no results
        }

        function filterByGroup() {
            var selectedGroup = document.getElementById('group-select').value.toLowerCase();
            var staffEntries = document.querySelectorAll('.staff-entry');
            var noStaffMessage = document.getElementById('no-staff-message');
            var tableHead = document.querySelector('#staffTable thead');

            var visible = false;
            staffEntries.forEach(function(entry) {
                var groups = entry.getAttribute('data-groups').toLowerCase();
                var matches = selectedGroup === '' || groups.includes(selectedGroup);
                entry.style.display = matches ? '' : 'none';
                if (matches) visible = true;
            });

            noStaffMessage.style.display = visible ? 'none' : 'block';
            tableHead.style.display = visible ? '' : 'none'; // Hide thead if no results
        }
        </script>

        <?php
    } else {
        echo "<p>No staff found</p>";
    }

    // Reset post data
    wp_reset_postdata();

    // Get the buffered content
    return ob_get_clean();
}



/* ################################################################ */
/* Kurzprofil (Arbeitsgruppe) */
/* ################################################################ */

add_action('init', 'register_group_staff_block');
function register_group_staff_block() {
    register_block_type('staff-manager/group-staff', array(
        'render_callback' => 'render_group_staff_block',
    ));
}
function render_group_staff_block($attributes) {
    $translations = sm_get_translations();
    $selected_group_id = isset($attributes['id']) ? intval($attributes['id']) : 0;

    if ($selected_group_id === 0) {
        return '<p>No group selected.</p>';
    }

    $group = get_post($selected_group_id);

    if (!$group || $group->post_type !== 'groups') {
        return '<p>Invalid group.</p>';
    }

    $group_title = get_the_title($group);

    // Query all staff-manager posts
    $staff_query = new WP_Query(array(
        'post_type' => 'staff-manager',
        'posts_per_page' => -1 // all posts of the specified post type without any limit, any pagination
    ));

    $staff_found = false;
    $output = "<div class='row mb-5'>
                <div class='col-md-12 text-center'>
                    <h4>{$translations['group_pre']} {$group_title} {$translations['group_after']}</h4>
                </div>
           </div>";


    if ($staff_query->have_posts()) {
        $count = 0;
        $output .= "<div class='row mb-5'>";

        while ($staff_query->have_posts()) {
            $staff_query->the_post();
            $post_id = get_the_ID();
            $post_content = get_post_field('post_content', $post_id);

            // Extract JSON data from block comment
            if (preg_match_all('/<!-- wp:staff-manager\/basic-information (.+?) \/-->/s', $post_content, $matches)) {
                foreach ($matches[1] as $match) {
                    $basic_info = json_decode($match, true);

                    if (isset($basic_info['groups']) && in_array($group_title, $basic_info['groups'])) {
                        $staff_found = true;
                        $title = esc_html($basic_info['title']);
                        $firstname = esc_html($basic_info['firstname']);
                        $lastname = esc_html($basic_info['lastname']);
                        $position = esc_html($basic_info['position']);
                        $phone = esc_html($basic_info['phone']);
                        $email = esc_html($basic_info['email']);
                        $profile_url = get_permalink($post_id);

                        if ($count % 2 == 0 && $count != 0) {
                            $output .= "</div><div class='row mb-5'>";
                        }

                        $output .= "<div class='col-md-2'>";
                        $output .= get_the_post_thumbnail($post_id, 'profile image', ['class' => 'img-fluid rounded-circle']);
                        $output .= "</div>
                                    <div class='col-md-4'>
                                        <div class='mb-1'><strong>{$title} {$firstname} {$lastname}</strong></div>
                                        <div>{$position}</div>
                                        <div>{$translations['phone']}: {$phone}</div>
                                        <div>{$translations['email']}: <a href='mailto:{$email}'>{$email}</a></div>
                                        <div><a href='{$profile_url}'>{$translations['full_profile']} ›</a></div>
                                    </div>";

                        $count++;
                    }
                }
            }
        }

        $output .= "</div>"; // Close the last row

        wp_reset_postdata(); // Restore original Post Data

        if (!$staff_found) {
            $output .= "<p>No staff found for this group.</p>";
        }

        return $output;
    } else {
        return "<p>No staff found for this group.</p>";
    }
}

/* ################################################################ */
/* Kurzprofil (individuell) */
/* ################################################################ */

add_action('init', 'register_single_staff_block');
function register_single_staff_block() {
    register_block_type('staff-manager/single-staff', array(
        'render_callback' => 'render_single_staff_block',
    ));
}
function render_single_staff_block($attributes) {
    $translations = sm_get_translations();
    if (isset($attributes['id'])) {
        $post_id = intval($attributes['id']);

        if ($post_id > 0 && get_post_type($post_id) === 'staff-manager') {
            $post = get_post($post_id);
            if (!empty($post->post_content)) {
                // Extract the JSON from the block comment
                preg_match('/<!-- wp:staff-manager\/basic-information (.+?) \/-->/s', $post->post_content, $matches);
                if (!empty($matches[1])) {
                    $basic_info = json_decode($matches[1], true);

                    if ($basic_info) {
                        $title = esc_html($basic_info['title']);
                        $firstname = esc_html($basic_info['firstname']);
                        $lastname = esc_html($basic_info['lastname']);
                        $position = esc_html($basic_info['position']);
                        $phone = esc_html($basic_info['phone']);
                        $email = esc_html($basic_info['email']);
                        $profile_url = get_permalink($post_id);
                        $profile_html = '<div class="mb-3">
                                            <div class="row">
                                                <div class="col-md-2">';
                        $profile_html .= get_the_post_thumbnail($post_id, 'profile image', ['class' => 'img-fluid rounded-circle']);
                        $profile_html .= '</div>
                                            <div class="col-md-10">';
                        $profile_html .= "<div class='mb-1'><strong>{$title} {$firstname} {$lastname}</strong></div>";
                        $profile_html .= "<div>{$position}</div><div>{$translations['phone']}: {$phone}</div><div>{$translations['email']}: <a href='mailto:{$email}'>{$email}</a></div><div><a href='{$profile_url}'>{$translations['full_profile']} ›</a></div>";
                        $profile_html .= '</div>
                                        </div>
                                    </div>';

                        return $profile_html;
                    } else {
                        return '<p>Invalid staff information format.</p>';
                    }
                } else {
                    return '<p>No basic information found in the post content.</p>';
                }
            } else {
                return '<p>No content found for ID: ' . esc_html($post_id) . '</p>';
            }
        } else {
            return '<p>No staff found for ID: ' . esc_html($post_id) . '</p>';
        }
    } else {
        return '<p>No ID provided</p>';
    }
}


