<?php
/*
Plugin Name: NewsAPI Shortcode
Plugin URI: https://example.com
Description: A WordPress plugin to fetch and display news articles using NewsAPI.
Version: 1.0
Author: Sam Woods
License: GPL-2.0+
*/

// Function to Fetch News from API
function fetch_news_from_api() {
    // Fetch the API key from the WordPress options table
    $options = get_option('newsapi_settings');
    $api_key = isset($options['api_key']) ? $options['api_key'] : '';

    if (empty($api_key)) {
        return 'API key is not set. Please go to the NewsAPI Settings page to enter your API key.';
    }

    // Make the API request using the API key for authentication
    $endpoint = 'https://newsapi.org/v2/everything?q=bitcoin&apiKey=' . $api_key;

    // Add custom headers to the request
    $args = array(
        'headers' => array(
            'User-Agent' => 'PHP'
        )
    );

    $response = wp_remote_get($endpoint, $args);

    if (is_wp_error($response)) {
        return 'Failed to fetch articles. Please check your API key and network connection.';
    }

    $response_body = wp_remote_retrieve_body($response);
    $articles = json_decode($response_body, true)['articles'];

    if (!$articles) {
        return 'No articles found.';
    }

    // Limit the articles to the first 6
    $articles = array_slice($articles, 0, 6);

    // Generate the HTML output for the articles
    $html_output = '<ul>';
    foreach ($articles as $article) {
        $html_output .= '<li>';
        // Include the article image
        if (!empty($article['urlToImage'])) {
            $html_output .= '<img src="' . esc_url($article['urlToImage']) . '" alt="Article Image">';
        }
        $html_output .= '<strong>' . esc_html($article['title']) . '</strong>';
        $html_output .= '<p>' . esc_html($article['description']) . '</p>';
        $html_output .= '<a href="' . esc_url($article['url']) . '" target="_blank">Read more</a>';
        $html_output .= '</li>';
    }
    $html_output .= '</ul>';

    return $html_output;
}

// Function to display news articles using the shortcode
function display_newsapi_shortcode() {
    return fetch_news_from_api();
}

// Register the shortcode
add_shortcode('newsapi', 'display_newsapi_shortcode');



add_action('admin_menu', 'newsapi_options_menu');
function newsapi_options_menu() {
    add_options_page('NewsAPI Settings', 'NewsAPI Settings', 'manage_options', 'newsapi_settings_page', 'newsapi_settings_page_display');
}
function newsapi_settings_page_display() {
    echo '<div class="wrap">';
    echo '<h1>NewsAPI Settings</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('newsapi_settings_group');
    do_settings_sections('newsapi_settings_page');
    submit_button();
    echo '</form>';
    echo '</div>';
}
add_action('admin_init', 'newsapi_settings_init');
function newsapi_settings_init() {
    register_setting('newsapi_settings_group', 'newsapi_settings');
    add_settings_section('newsapi_settings_section', 'API Settings', 'newsapi_settings_section_display', 'newsapi_settings_page');
    add_settings_field('api_key', 'API Key', 'newsapi_api_key_display', 'newsapi_settings_page', 'newsapi_settings_section');
}
function newsapi_settings_section_display() {
    echo 'Enter your NewsAPI.org API key below.';
}
function newsapi_api_key_display() {
    $options = get_option('newsapi_settings');
    $api_key = isset($options['api_key']) ? $options['api_key'] : '';
    echo '<input type="text" name="newsapi_settings[api_key]" value="' . esc_attr($api_key) . '" />';
}


// JavaScript code for console logging
add_action('wp_footer', 'console_log_api_response');
function console_log_api_response() {
    ?>
    <script type="text/javascript">
        console.log('API Response:', <?php echo json_encode(get_option('newsapi_last_api_response')); ?>);
    </script>
    <?php
}
