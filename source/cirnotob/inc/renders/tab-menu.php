<?php
class Cirnotob_Tab_Menu
{

    public static function render($params = [], $defaultActive = 0)
    {
        $tabs = [];
        $type = '';
        if (is_array($params)) {
            foreach ($params as $item) {
                if (is_string($item)) {
                    $tabs[] = (object) [
                        "name" => $item,
                        "slug" => $item,
                    ];
                } else if (!empty($item->name)) {
                    $tabs[] = $item;
                } else {
                    $tabs[] = (object)  [
                        "name" => "空",
                    ];
                }
            }
        }

        $type = Cirnotob_Util::pageType();
        // 开始渲染
        ob_start();
?>
        <div class="cirnotob-tab-menu">
            <div class="tab-menu-box">
                <?php if (!empty($tabs)):
                    foreach ($tabs as $index => $tab):
                ?>
                        <div class="tab-menu-item <?= $index === $defaultActive ? 'active' : '' ?>" data-name="<?= esc_attr($tab->slug) ?>"><?= esc_html($tab->name) ?></div>
                <?php endforeach;
                endif; ?>
            </div>
        </div>
<?php
        return ob_get_clean();
    }
}
