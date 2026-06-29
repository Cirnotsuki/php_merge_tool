<?php
if (!defined('ABSPATH')) exit;

class Cirnotob_Search_Result
{
    /**
     * 渲染搜索结果
     *
     * @param array{
     *     className?: string,
     *     query?: string,
     *     type?: int|string,
     *     search?: int|bool,
     *     category?: string|int,
     *     hideHeader?: bool,
     *     showBack?: bool,
     *     catPos?: 'top'|'bottom'|'none',
     *     viewType?: 'row'|'grid'|'card'|'picture-row'|'picture-grid'|'picture-card',
     *     showCount?: int,
     *     middleRange?: int,
     *     frontThreshold?: int,
     *     backThreshold?: int,
     *     result?: array
     * } $opt
     *
     * @return string
     */
    public static function render($opt = [])
    {
        $uuid = wp_generate_uuid4();
        $route_base = $GLOBALS['wp']->query_vars['route_base'] ?? '';
        $isSearch = $route_base === 'search';

        $className = $opt['className'] ?? '';

        $query = $opt['query'] ?? '';
        $type =  $opt['type'] ?? '';
        $category = $opt['category'] ?? '';
        $search = intval($opt['search'] ?? 0);

        $hideHeader = (bool)($opt['hideHeader'] ?? false);
        $showBack = (bool)($opt['showBack'] ?? false);

        $catePos = $opt['catPos'] ?? 'top';
        $viewType = $opt['viewType'] ?? 'row';

        $showCount = intval($opt['showCount'] ?? 6);
        $middleRange = intval($opt['middleRange'] ?? 2);
        $frontThreshold = intval($opt['frontThreshold'] ?? 5);
        $backThreshold = intval($opt['backThreshold'] ?? 4);

        $template = '';
        $homeUrl = home_url('/');

        $cateTop = <<<HTML
            <div class="row-category">{{CATEGORY}}</div> 
        HTML;
        $cateBottom = '';

        if ($catePos === 'bottom') {
            $cateBottom = $cateTop;
            $cateTop = '';
        }
        if ($catePos === 'none') {
            $cateTop = '';
            $cateBottom = '';
        }

        $pictureRow = '';
        $addClassName = '';
        if (str_contains($viewType, 'picture')) {
            $addClassName = $viewType;
        }
        if (str_contains($viewType, 'picture') || $viewType === 'grid') {
            $pictureRow = <<<HTML
                <div class="row-image">
                    <img width="240" height="150" src="{{THUMBNAIL}}" class="attachment-medium size-medium wp-post-image" alt="" decoding="async">
                    <div class="no-image" src="{{THUMBNAIL}}"><span>NO IMAGE</span></div>
                </div>
            HTML;
        }

        switch ($viewType) {
            case 'grid':
            case 'picture-grid':
                $gridWidth = 3;
                if ($viewType === 'picture-grid') {
                    $gridWidth = 6;
                }
                $template = <<<HTML
                    <div class="col-md-{$gridWidth} result-grid {$addClassName}">
                        <a href="{$homeUrl}{{LINK}}">
                            {$pictureRow}
                            <div class="row-detail">
                                {$cateTop}
                                <div class="row-title">{{TITLE}}</div>
                                <div class="row-bottom">
                                    {$cateBottom}
                                    <div class="row-date">{{DATE}}</div>
                                </div>
                            </div>
                        </a>
                    </div>
                    HTML;
                break;
            case 'card':
            case 'picture-card':
                $template = <<<HTML
                    <div class="col-md-6 result-card {$addClassName}">
                        <a href="{$homeUrl}{{LINK}}">
                            {$pictureRow}
                            <div class="row-detail">
                                {$cateTop}
                                <div class="row-title">{{TITLE}}</div>
                                <div class="row-excerpt">{{EXCERPT}}</div>
                                <div class="row-bottom">
                                    {$cateBottom}
                                    <div class="row-date">{{DATE}}</div>
                                </div>
                            </div>
                        </a>
                    </div>
                    HTML;
                break;
            case 'picture-row':
            default:
                $rowWidth = $viewType === 'picture-row' ? 6 : 12;
                $rowBottom = $viewType === 'picture-row'
                    ? <<<HTML
                        <div class="row-date">{{DATE}}</div>
                        HTML
                    : <<<HTML
                        <div class="row-url">{$homeUrl}{{LINK}}</div>
                        HTML;
                $template = <<<HTML
                        <div class="col-md-{$rowWidth} result-row {$addClassName}">
                            <a href="{$homeUrl}{{LINK}}">
                                {$pictureRow}
                                <div class="row-detail">
                                    {$cateTop}
                                    <div class="row-title">{{TITLE}}</div>
                                    <div class="row-excerpt">{{EXCERPT}}</div>
                                    <div class="row-bottom">
                                        {$cateBottom}
                                        {$rowBottom}
                                    </div>
                                </div>
                            </a>
                        </div>
                    HTML;
                break;
        }

        $search_result = $opt['result'] ?? [
            'list' => [],
            'page' => 1,
            'total' => 1,
            'total_pages' => 1,
        ];

        if ($search > 0) {
            $search_result = Cirnotob_Post_Util::getPostList(Cirnotob_Util::getSearchType($type), $query, $category, 1, $search);
            logger($search_result);
        }
        // 开始渲染
        ob_start();
?>
        <div class="cirnotob-search-result <?= $className ?>"
            data-query="<?= esc_attr($query) ?>"
            data-type="<?= esc_attr($type) ?>"
            data-category="<?= esc_attr($category) ?>"
            data-page="1"
            data-total="<?= $search_result['total_pages'] ?>"
            data-limit="<?= esc_attr(max(1, $search)) ?>"
            id="<?= $uuid ?>">
            <div class="result-empty cirnotob-empty" style="<?= $search > 0 && empty($search_result['list']) ? '' : 'display: none;' ?>">
                <span alt="no result"></span>
                <p>No Result Found</p>
                <p>Try a different search,or browse the</p>
                <p>help center</p>
            </div>
            <div class="result-searching cirnotob-loading" style="display: none;">
                <span alt="searching"></span>
                <p><?= __('Searching...', 'cirnotob') ?></p>
            </div>
            <div class="result-valid" style="<?= !empty($search_result['list']) ? '' : 'display: none;' ?>">
                <?php if (!$hideHeader): ?>
                    <div class="result-header <?= $isSearch ? 'is-search' : '' ?>">
                        <?php if ($showBack): ?>
                            <span class="result-button"><i class="fa fa-arrow-left"></i><span>Back</span></span>
                        <?php endif; ?>

                        <?php if ($isSearch): ?>
                            <span class="result-count"><?= $search_result['total'] ?></span>
                            <span><?= __('results found', 'cirnotob') ?></span>
                        <?php else: ?>
                            <span class="result-count"><?= $search_result['total'] ?></span>
                            <span><?= __('results for', 'cirnotob') ?></span>
                            <span>"</span><span class="result-query"><?= $query ?></span><span>"</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="row result-content">
                    <?php self::renderTemplate($template, $search_result) ?>
                </div>
                <template id="result-view-template">
                    <?= $template ?>
                </template>

            </div>
            <div class="result-footer">
                <div class="cirnotob-paging"
                    data-show-count="<?= esc_attr($showCount) ?>"
                    data-middle-range="<?= esc_attr($middleRange) ?>"
                    data-front-threshold="<?= esc_attr($frontThreshold) ?>"
                    data-back-threshold="<?= esc_attr($backThreshold) ?>"></div>
            </div>
        </div>
<?php
        return ob_get_clean();
    }

    private static function renderTemplate(string $template, $search_result)
    {
        if (empty($search_result['list'])) return;

        foreach ($search_result['list'] as $post) {
            $cateName = '';
            if (!empty($post['categories'])) {
                $cateName = $post['categories'][0]['name'];
            }

            $replacement = [
                '{{TITLE}}' => $post['title'],
                '{{EXCERPT}}' => $post['except'] ?? $post['content'] ?? '',
                '{{THUMBNAIL}}' => $post['thumbnail'],
                '{{LINK}}' => $post['link'],
                '{{CATEGORY}}' => $cateName,
                '{{DATE}}' => date('M d, Y', strtotime($post['last_modified'])),
            ];

            echo str_replace(
                array_keys($replacement),
                array_values($replacement),
                $template
            );
        }
    }
}
