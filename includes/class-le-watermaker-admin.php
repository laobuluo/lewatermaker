<?php
/**
 * LeWaterMaker 管理界面类
 * 
 * @package LeWaterMaker
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class LeWaterMaker_Admin {
    
    /**
     * 初始化管理界面
     */
    public function init() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * 注册设置
     */
    public function register_settings() {
        register_setting(
            'le_watermaker_options',
            'le_watermaker_options',
            array($this, 'sanitize_options')
        );
        
        add_settings_section(
            'le_watermaker_general',
            '基本设置',
            array($this, 'general_section_callback'),
            'le-watermaker'
        );
        
        // 启用水印选项
        add_settings_field(
            'enabled',
            '启用水印',
            array($this, 'enabled_field_callback'),
            'le-watermaker',
            'le_watermaker_general'
        );
        
        // 水印文字
        add_settings_field(
            'watermark_text',
            '水印文字',
            array($this, 'watermark_text_field_callback'),
            'le-watermaker',
            'le_watermaker_general'
        );
        
        // 水印模式
        add_settings_field(
            'watermark_mode',
            '水印模式',
            array($this, 'watermark_mode_field_callback'),
            'le-watermaker',
            'le_watermaker_general'
        );
        
        // 字体设置
        add_settings_field(
            'font_family',
            '字体',
            array($this, 'font_family_field_callback'),
            'le-watermaker',
            'le_watermaker_general'
        );
        
        // 字体大小
        add_settings_field(
            'font_size',
            '字体大小',
            array($this, 'font_size_field_callback'),
            'le-watermaker',
            'le_watermaker_general'
        );
        
        // 透明度
        add_settings_field(
            'opacity',
            '透明度',
            array($this, 'opacity_field_callback'),
            'le-watermaker',
            'le_watermaker_general'
        );
        
        // 旋转角度
        add_settings_field(
            'rotation',
            '旋转角度',
            array($this, 'rotation_field_callback'),
            'le-watermaker',
            'le_watermaker_general'
        );
        
        // 文本间距
        add_settings_field(
            'spacing',
            '文本间距',
            array($this, 'spacing_field_callback'),
            'le-watermaker',
            'le_watermaker_general'
        );
        
        // 文字颜色
        add_settings_field(
            'text_color',
            '文字颜色',
            array($this, 'text_color_field_callback'),
            'le-watermaker',
            'le_watermaker_general'
        );
        
        // 最小宽度
        add_settings_field(
            'min_width',
            '最小图片宽度',
            array($this, 'min_width_field_callback'),
            'le-watermaker',
            'le_watermaker_general'
        );
        
        // 最小高度
        add_settings_field(
            'min_height',
            '最小图片高度',
            array($this, 'min_height_field_callback'),
            'le-watermaker',
            'le_watermaker_general'
        );
    }
    
    /**
     * 加载管理脚本
     */
    public function enqueue_admin_scripts($hook) {
        if ('settings_page_le-watermaker' !== $hook) {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery');
        
        // 加载自定义CSS
        wp_enqueue_style(
            'le-watermaker-admin',
            LE_WATERMAKER_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            LE_WATERMAKER_VERSION
        );
        
        wp_enqueue_script(
            'le-watermaker-admin',
            LE_WATERMAKER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            LE_WATERMAKER_VERSION,
            true
        );
    }
    
    /**
     * 显示管理页面
     */
    public function display_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $options = get_option('le_watermaker_options', array());
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('le_watermaker_options');
                do_settings_sections('le-watermaker');
                submit_button('保存设置');
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * 基本设置区域回调
     */
    public function general_section_callback() {
        echo '<p>配置水印的基本参数，启用后上传的图片将自动添加水印效果。</p>';
    }
    
    /**
     * 启用水印字段回调
     */
    public function enabled_field_callback() {
        $options = get_option('le_watermaker_options', array());
        $enabled = isset($options['enabled']) ? $options['enabled'] : 0;
        ?>
        <label>
            <input type="checkbox" name="le_watermaker_options[enabled]" value="1" <?php checked(1, $enabled); ?> />
            启用图片水印功能
        </label>
        <?php
    }
    
    /**
     * 水印文字字段回调
     */
    public function watermark_text_field_callback() {
        $options = get_option('le_watermaker_options', array());
        $text = isset($options['watermark_text']) ? $options['watermark_text'] : 'LeWaterMaker';
        ?>
        <input type="text" name="le_watermaker_options[watermark_text]" value="<?php echo esc_attr($text); ?>" class="regular-text" />
        <p class="description">设置要显示的水印文字内容</p>
        <?php
    }
    
    /**
     * 水印模式字段回调
     */
    public function watermark_mode_field_callback() {
        $options = get_option('le_watermaker_options', array());
        $mode = isset($options['watermark_mode']) ? $options['watermark_mode'] : 'tiling';
        ?>
        <select name="le_watermaker_options[watermark_mode]">
                    <option value="tiling" <?php selected($mode, 'tiling'); ?>>平铺模式</option>
        <option value="single" <?php selected($mode, 'single'); ?>>单个居中</option>
        </select>
        <p class="description">选择水印应用模式：平铺模式将在整个图片上重复水印，单个居中模式将在图片中央添加一个水印</p>
        <?php
    }
    
    /**
     * 字体字段回调
     */
    public function font_family_field_callback() {
        $options = get_option('le_watermaker_options', array());
        $font = isset($options['font_family']) ? $options['font_family'] : 'DingTalkJinBuTi-Regular';
        
        // 获取字体目录中的字体文件
        $fonts = $this->get_available_fonts();
        
        ?>
        <select name="le_watermaker_options[font_family]">
            <?php foreach ($fonts as $font_option): ?>
                <option value="<?php echo esc_attr($font_option); ?>" <?php selected($font, $font_option); ?>>
                    <?php echo esc_html($font_option); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">字体文件位于 /assets/fonts 目录中</p>
        <?php
    }
    
    /**
     * 获取可用的字体列表
     */
    private function get_available_fonts() {
        $fonts_dir = LE_WATERMAKER_PLUGIN_PATH . 'assets/fonts/';
        $fonts = array();
        
        if (is_dir($fonts_dir)) {
            $files = scandir($fonts_dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, array('ttf', 'otf', 'woff', 'woff2'))) {
                        $fonts[] = $file;
                    }
                }
            }
        }
        
        // 如果没有找到字体文件，返回默认字体
        if (empty($fonts)) {
            $fonts = array('Arial', 'Helvetica', 'Times New Roman');
        }
        
        return $fonts;
    }
    
    /**
     * 字体大小字段回调
     */
    public function font_size_field_callback() {
        $options = get_option('le_watermaker_options', array());
        $size = isset($options['font_size']) ? $options['font_size'] : 16;
        ?>
        <input type="number" name="le_watermaker_options[font_size]" value="<?php echo esc_attr($size); ?>" min="8" max="72" step="1" />
        <span>px</span>
        <?php
    }
    
    /**
     * 透明度字段回调
     */
    public function opacity_field_callback() {
        $options = get_option('le_watermaker_options', array());
        $opacity = isset($options['opacity']) ? $options['opacity'] : 50;
        ?>
        <input type="range" name="le_watermaker_options[opacity]" value="<?php echo esc_attr($opacity); ?>" min="10" max="100" step="5" />
        <span id="opacity-value"><?php echo esc_html($opacity); ?>%</span>
        <?php
    }
    
    /**
     * 旋转角度字段回调
     */
    public function rotation_field_callback() {
        $options = get_option('le_watermaker_options', array());
        $rotation = isset($options['rotation']) ? $options['rotation'] : 0;
        ?>
        <input type="number" name="le_watermaker_options[rotation]" value="<?php echo esc_attr($rotation); ?>" min="-180" max="180" step="5" />
        <span>度</span>
        <?php
    }
    
    /**
     * 文本间距字段回调
     */
    public function spacing_field_callback() {
        $options = get_option('le_watermaker_options', array());
        $spacing = isset($options['spacing']) ? $options['spacing'] : 100;
        ?>
        <input type="number" name="le_watermaker_options[spacing]" value="<?php echo esc_attr($spacing); ?>" min="50" max="300" step="10" />
        <span>px</span>
        <?php
    }
    
    /**
     * 文字颜色字段回调
     */
    public function text_color_field_callback() {
        $options = get_option('le_watermaker_options', array());
        $color = isset($options['text_color']) ? $options['text_color'] : '#000000';
        ?>
        <input type="text" name="le_watermaker_options[text_color]" value="<?php echo esc_attr($color); ?>" class="color-picker" />
        <?php
    }
    
    /**
     * 最小宽度字段回调
     */
    public function min_width_field_callback() {
        $options = get_option('le_watermaker_options', array());
        $width = isset($options['min_width']) ? $options['min_width'] : 300;
        ?>
        <input type="number" name="le_watermaker_options[min_width]" value="<?php echo esc_attr($width); ?>" min="100" max="2000" step="50" />
        <span>px</span>
        <p class="description">只有超过此宽度的图片才会添加水印</p>
        <?php
    }
    
    /**
     * 最小高度字段回调
     */
    public function min_height_field_callback() {
        $options = get_option('le_watermaker_options', array());
        $height = isset($options['min_height']) ? $options['min_height'] : 300;
        ?>
        <input type="number" name="le_watermaker_options[min_height]" value="<?php echo esc_attr($height); ?>" min="100" max="2000" step="50" />
        <span>px</span>
        <p class="description">只有超过此高度的图片才会添加水印</p>
        <?php
    }
    
    /**
     * 清理和验证选项
     */
    public function sanitize_options($options) {
        $sanitized = array();
        
        $sanitized['enabled'] = isset($options['enabled']) ? 1 : 0;
        $sanitized['watermark_text'] = sanitize_text_field($options['watermark_text']);
        $sanitized['watermark_mode'] = sanitize_text_field($options['watermark_mode']);
        $sanitized['font_family'] = sanitize_text_field($options['font_family']);
        $sanitized['font_size'] = absint($options['font_size']);
        $sanitized['opacity'] = absint($options['opacity']);
        $sanitized['rotation'] = intval($options['rotation']);
        $sanitized['spacing'] = absint($options['spacing']);
        $sanitized['text_color'] = sanitize_hex_color($options['text_color']);
        $sanitized['min_width'] = absint($options['min_width']);
        $sanitized['min_height'] = absint($options['min_height']);
        
        // 确保数值在合理范围内
        $sanitized['font_size'] = max(8, min(72, $sanitized['font_size']));
        $sanitized['opacity'] = max(10, min(100, $sanitized['opacity']));
        $sanitized['rotation'] = max(-180, min(180, $sanitized['rotation']));
        $sanitized['spacing'] = max(50, min(300, $sanitized['spacing']));
        $sanitized['min_width'] = max(100, min(2000, $sanitized['min_width']));
        $sanitized['min_height'] = max(100, min(2000, $sanitized['min_height']));
        
        // 验证水印模式
        if (!in_array($sanitized['watermark_mode'], array('tiling', 'single'))) {
            $sanitized['watermark_mode'] = 'tiling';
        }
        
        return $sanitized;
    }
} 