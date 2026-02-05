<?php
/**
 * LeWaterMaker 插件卸载文件
 * 
 * 当插件被删除时，此文件会被自动执行
 * 用于清理所有插件相关的数据和设置
 * 
 * @package LeWaterMaker
 * @since 1.0.0
 */

// 如果直接访问此文件，则退出
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// 删除插件选项
delete_option('le_watermaker_options');

// 删除所有插件相关的用户元数据
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'le_watermaker_%'");

// 删除所有插件相关的选项
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'le_watermaker_%'");

// 清理可能存在的临时文件
$upload_dir = wp_upload_dir();
$watermark_dir = $upload_dir['basedir'] . '/le-watermaker-temp';

if (is_dir($watermark_dir)) {
    // 递归删除目录
    function delete_directory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                delete_directory($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    }
    
    delete_directory($watermark_dir);
}

// 记录卸载日志（可选）
if (function_exists('error_log')) {
    error_log('LeWaterMaker plugin has been uninstalled and all data has been cleaned up.');
} 