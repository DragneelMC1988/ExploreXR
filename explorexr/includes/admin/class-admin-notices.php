<?php
/**
 * ExploreXR Admin Notices Manager
 * 
 * Centralized system for displaying admin notices with:
 * - Consistent placement (always above .explorexr-admin-header)
 * - Deduplication (prevent duplicate notices)
 * - Priority-based rendering
 * - Standard WordPress notice classes
 * 
 * @package ExploreXR
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Notices Manager
 * Singleton class for centralized notice management
 */
class ExploreXR_Admin_Notices {
    
    /**
     * Singleton instance
     * 
     * @var ExploreXR_Admin_Notices
     */
    private static $instance = null;
    
    /**
     * Notice queue
     * 
     * @var array
     */
    private $notices = array();
    
    /**
     * Notice IDs to prevent duplicates
     * 
     * @var array
     */
    private $notice_ids = array();
    
    /**
     * Get singleton instance
     * 
     * @return ExploreXR_Admin_Notices
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - Private to enforce singleton
     */
    private function __construct() {
        // Hook admin_notices at priority 1 to render BEFORE header
        add_action('admin_notices', array($this, 'render_notices'), 1);
    }
    
    /**
     * Add a notice to the queue
     * 
     * @param string $message Notice message (HTML allowed)
     * @param string $type Notice type: 'success', 'error', 'warning', 'info'
     * @param bool $dismissible Whether notice is dismissible
     * @param string $id Unique identifier (prevents duplicates)
     * @return bool True if added, false if duplicate
     */
    public function add($message, $type = 'info', $dismissible = true, $id = null) {
        // Generate ID if not provided
        if (null === $id) {
            $id = md5($message . $type);
        }
        
        // Check for duplicates
        if (in_array($id, $this->notice_ids, true)) {
            return false; // Already exists
        }
        
        // Validate type
        $valid_types = array('success', 'error', 'warning', 'info');
        if (!in_array($type, $valid_types, true)) {
            $type = 'info';
        }
        
        // Add to queue
        $this->notices[] = array(
            'id' => $id,
            'message' => $message,
            'type' => $type,
            'dismissible' => $dismissible,
        );
        
        // Track ID
        $this->notice_ids[] = $id;
        
        return true;
    }
    
    /**
     * Add success notice
     * 
     * @param string $message Notice message
     * @param bool $dismissible Whether dismissible
     * @param string $id Unique identifier
     * @return bool
     */
    public function success($message, $dismissible = true, $id = null) {
        return $this->add($message, 'success', $dismissible, $id);
    }
    
    /**
     * Add error notice
     * 
     * @param string $message Notice message
     * @param bool $dismissible Whether dismissible
     * @param string $id Unique identifier
     * @return bool
     */
    public function error($message, $dismissible = true, $id = null) {
        return $this->add($message, 'error', $dismissible, $id);
    }
    
    /**
     * Add warning notice
     * 
     * @param string $message Notice message
     * @param bool $dismissible Whether dismissible
     * @param string $id Unique identifier
     * @return bool
     */
    public function warning($message, $dismissible = true, $id = null) {
        return $this->add($message, 'warning', $dismissible, $id);
    }
    
    /**
     * Add info notice
     * 
     * @param string $message Notice message
     * @param bool $dismissible Whether dismissible
     * @param string $id Unique identifier
     * @return bool
     */
    public function info($message, $dismissible = true, $id = null) {
        return $this->add($message, 'info', $dismissible, $id);
    }
    
    /**
     * Clear all notices
     */
    public function clear() {
        $this->notices = array();
        $this->notice_ids = array();
    }
    
    /**
     * Render all notices
     * Hooked to admin_notices at priority 1
     */
    public function render_notices() {
        // Only render on ExploreXR admin pages
        if (!$this->is_explorexr_admin_page()) {
            return;
        }
        
        // Render each notice
        foreach ($this->notices as $notice) {
            $this->render_notice($notice);
        }
        
        // Clear after rendering (notices are single-use per page load)
        $this->clear();
    }
    
    /**
     * Render a single notice
     * 
     * @param array $notice Notice data
     */
    private function render_notice($notice) {
        // Build CSS classes
        $classes = array('notice', 'explorexr-admin-notice');
        
        // Add type class
        switch ($notice['type']) {
            case 'success':
                $classes[] = 'notice-success';
                break;
            case 'error':
                $classes[] = 'notice-error';
                break;
            case 'warning':
                $classes[] = 'notice-warning';
                break;
            case 'info':
            default:
                $classes[] = 'notice-info';
                break;
        }
        
        // Add dismissible class
        if ($notice['dismissible']) {
            $classes[] = 'is-dismissible';
        }
        
        // Render notice HTML
        printf(
            '<div class="%s" data-notice-id="%s"><p>%s</p></div>',
            esc_attr(implode(' ', $classes)),
            esc_attr($notice['id']),
            wp_kses_post($notice['message'])
        );
    }
    
    /**
     * Check if current page is an ExploreXR admin page
     * 
     * @return bool
     */
    private function is_explorexr_admin_page() {
        global $pagenow;
        
        // Check for post type pages
        $screen = get_current_screen();
        if ($screen && isset($screen->post_type) && $screen->post_type === 'explorexr_premium_model') {
            return true;
        }
        
        // Check for ExploreXR settings pages
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Page parameter check only, no data processing
        if (isset($_GET['page']) && strpos(sanitize_key(wp_unslash($_GET['page'])), 'explorexr') !== false) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get count of queued notices
     * 
     * @return int
     */
    public function count() {
        return count($this->notices);
    }
    
    /**
     * Check if a notice ID exists
     * 
     * @param string $id Notice ID
     * @return bool
     */
    public function has_notice($id) {
        return in_array($id, $this->notice_ids, true);
    }
}

/**
 * Get admin notices manager instance
 * Helper function for global access
 * 
 * @return ExploreXR_Admin_Notices
 */
function explorexr_admin_notices() {
    return ExploreXR_Admin_Notices::get_instance();
}
