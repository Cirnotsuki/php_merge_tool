<?php
if (!defined('ABSPATH')) exit;

class Cirnotob_Search_Form
{
    /**
     * 渲染搜索表单
     * @param array{
     *     tag?: 'form'|'div',
     *     placeholder?: string,
     *     useIcon?: bool
     *     plain?: bool
     * } $opt
     * @param string $defaultValue
     */
    public static function render($opt = [], $defaultValue = '')
    {
        $tag = $opt['tag'] ?? 'form';
        $placeholder = $opt['placeholder'] ?? '';
        $useIcon = (bool)($opt['useIcon'] ?? false);
        $plain = (bool)($opt['plain'] ?? false);


        if (empty($placeholder)) {
            $placeholder = 'Search Chenda Glass';
        }
        // 开始渲染
        ob_start();
?>
        <div class="cirnotob-search-form">
            <?php if ($tag === 'form'): ?>
                <form method="get" class="search-default search-bg" action="<?= esc_url(home_url('/search/')); ?>">
                <?php else: ?>
                    <<?= $tag ?> class="search-default search-bg">
                    <?php endif ?>
                    <div class="search-input <?= $plain ? 'plain' : '' ?>">
                        <div class="input-box">
                            <span class="screen-reader-text">
                                <?php esc_html_e('Search for:', 'cirnotob'); ?>
                            </span>
                            <input type="search" class="search-field header-search-field"
                                placeholder="<?php esc_attr_e($placeholder, 'cirnotob'); ?>" value="<?= esc_attr($defaultValue); ?>" name="q" id="s" />
                            <div role="button" class="clear">
                                <i class="fa fa-times-circle-o" aria-hidden="true"></i>
                            </div>
                        </div>
                        <button role="button" type="submit" class="submit"
                            aria-label="<?php esc_attr_e('Search', 'cirnotob'); ?>">
                            <?php if ($useIcon): ?>
                                <i class="fa fa-search" aria-hidden="true"></i>
                            <?php else: ?>
                                <?php esc_attr_e('Search', 'cirnotob'); ?>
                            <?php endif ?>
                        </button>
                    </div>
                    <?php if ($tag === 'form'): ?>
                </form>
            <?php else: ?>
                </<?= $tag ?>>
            <?php endif ?>
        </div>
<?php
        return ob_get_clean();
    }
}
