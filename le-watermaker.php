<?php
/**
 * Plugin Name: LeWaterMaker
 * Plugin URI:  https://www.laojiang.me/6351.html
 * Description: 为WordPress文章中的图片自动添加水印效果，支持平铺和单个居中两种模式，可自定义文字、字体、颜色、透明度等设置
 * Version: 1.0.1
 * Author: 老蒋和他的伙伴们
 * Author URI: https://www.laojiang.me
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('LE_WATERMAKER_VERSION', '1.0.1');
define('LE_WATERMAKER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LE_WATERMAKER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LE_WATERMAKER_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('LE_WATERMAKER_PLUGIN_PATH', plugin_dir_path(__FILE__));

// 包含必要的文件
require_once LE_WATERMAKER_PLUGIN_DIR . 'includes/class-le-watermaker.php';
require_once LE_WATERMAKER_PLUGIN_DIR . 'includes/class-le-watermaker-admin.php';
require_once LE_WATERMAKER_PLUGIN_DIR . 'includes/class-le-watermaker-image-processor.php';

// 初始化插件
function le_watermaker_init() {
    $plugin = LeWaterMaker::get_instance();
    $plugin->init();
}
add_action('plugins_loaded', 'le_watermaker_init');

// 激活插件时的钩子
register_activation_hook(__FILE__, 'le_watermaker_activate');
function le_watermaker_activate() {
    // 设置默认选项
    $default_options = array(
        'enabled' => 0,
        'watermark_text' => 'LeWaterMaker',
        'watermark_mode' => 'tiling',
        'font_family' => 'DingTalkJinBuTi-Regular',
        'font_size' => 16,
        'opacity' => 50,
        'rotation' => -45,
        'spacing' => 100,
        'text_color' => '#000000',
        'min_width' => 300,
        'min_height' => 300
    );
    
    add_option('le_watermaker_options', $default_options);
}

// 停用插件时的钩子
register_deactivation_hook(__FILE__, 'le_watermaker_deactivate');
function le_watermaker_deactivate() {
    // 清理定时任务等
}

// 卸载插件时的钩子
register_uninstall_hook(__FILE__, 'le_watermaker_uninstall');
function le_watermaker_uninstall() {
    // 删除所有选项和设置
    delete_option('le_watermaker_options');
} 