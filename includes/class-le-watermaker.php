<?php
/**
 * LeWaterMaker 主类
 * 
 * @package LeWaterMaker
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class LeWaterMaker {
    
    /**
     * 插件实例
     */
    private static $instance = null;
    
    /**
     * 插件选项
     */
    private $options;
    
    /**
     * 获取插件实例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 构造函数
     */
    private function __construct() {
        $this->options = get_option('le_watermaker_options', array());
    }
    
    /**
     * 初始化插件
     */
    public function init() {
        // 检查是否启用了水印功能
        if ($this->is_enabled()) {
            // 添加图片处理钩子
            add_filter('wp_handle_upload', array($this, 'process_uploaded_image'), 10, 2);
            add_filter('wp_generate_attachment_metadata', array($this, 'process_attachment_metadata'), 10, 2);
        }
        
        // 添加管理菜单
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // 添加设置链接
        add_filter('plugin_action_links_' . LE_WATERMAKER_PLUGIN_BASENAME, array($this, 'add_settings_link'));
        
        // 初始化管理界面
        if (is_admin()) {
            $admin = new LeWaterMaker_Admin();
            $admin->init();
        }
    }
    
    /**
     * 检查水印功能是否启用
     */
    public function is_enabled() {
        return isset($this->options['enabled']) && $this->options['enabled'];
    }
    
    /**
     * 获取插件选项
     */
    public function get_options() {
        return $this->options;
    }
    
    /**
     * 获取单个选项值
     */
    public function get_option($key, $default = '') {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }
    
    /**
     * 处理上传的图片
     */
    public function process_uploaded_image($file, $context) {
        // 只处理图片文件
        if (!preg_match('!\.(jpg|jpeg|png|gif)$!i', $file['file'])) {
            return $file;
        }
        
        // 检查图片尺寸是否满足水印条件
        if (!$this->should_add_watermark($file['file'])) {
            return $file;
        }
        
        // 处理图片水印
        $image_processor = new LeWaterMaker_Image_Processor();
        $result = $image_processor->add_watermark($file['file']);
        
        if ($result) {
            $file['file'] = $result;
        }
        
        return $file;
    }
    
    /**
     * 处理附件元数据
     */
    public function process_attachment_metadata($metadata, $attachment_id) {
        // 这里可以添加额外的处理逻辑
        return $metadata;
    }
    
    /**
     * 检查是否应该添加水印
     */
    private function should_add_watermark($file_path) {
        if (!$this->is_enabled()) {
            return false;
        }
        
        // 获取图片信息
        $image_info = getimagesize($file_path);
        if (!$image_info) {
            return false;
        }
        
        $width = $image_info[0];
        $height = $image_info[1];
        
        $min_width = (int) $this->get_option('min_width', 300);
        $min_height = (int) $this->get_option('min_height', 300);
        
        return $width >= $min_width && $height >= $min_height;
    }
    
    /**
     * 添加管理菜单
     */
    public function add_admin_menu() {
        add_options_page(
            'LeWaterMaker 设置',
            'LeWaterMaker',
            'manage_options',
            'le-watermaker',
            array($this, 'admin_page')
        );
    }
    
    /**
     * 管理页面回调
     */
    public function admin_page() {
        $admin = new LeWaterMaker_Admin();
        $admin->display_admin_page();
    }
    
    /**
     * 添加设置链接
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=le-watermaker') . '">设置</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
} 