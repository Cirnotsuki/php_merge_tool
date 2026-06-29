<?php
class Cirnotob_Breadcrumb
{
    /**
     * @param array<[name => route]> $items
     * @param bool $hideCurrent
     * @return string
     */
    public static function render($items = [], $hideCurrent = false)
    {
        ob_start();
?>
        <div class="cirnotob-breadcrumb">
            <?php if (!empty($items)): ?>
                <?php $total = count($items);
                $index = 0; ?>
                <?php foreach ($items as $name => $route): ?>
                    <div class="breadcrumb-item">
                        <?php if (!empty($route)): ?>
                            <a href="<?= esc_url(home_url($route)); ?>">
                                <?= $name; ?>
                            </a>
                            <?php if ($index < $total - 1): ?>
                            <span class="breadcrumb-separator">/</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="breadcrumb-current">
                                <?= $name; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php $index++; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
<?php
        return ob_get_clean();
    }
}
