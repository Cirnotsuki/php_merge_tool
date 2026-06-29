<?php
define('ROUTE_VIEWS_FOLDER', 'views/');

define('ROUTE_CATE_PAGES', [
    'help_center' => 'help',
    'product_page' => 'product-page',
    'post_page' => 'post-page',
    'blog_category' => 'blog-categories'
]);
define('ROUTE_SINGLE_PAGES', [
    'search' => 'search',
    'help' => 'help-center',
    'product' => 'product-showcase',
    'blog' => 'blog-categories',
    'news' => 'newsroom'
]);

define('ROUTE_REDIRECT', [
    'help' => 'help/category',
]);

// 所有合法 route_base 白名单
define('ROUTE_ALLOWED_BASES', array_merge(
    array_values(ROUTE_CATE_PAGES),
    array_values(ROUTE_SINGLE_PAGES)
));

define(
    'ROUTE_TEMPLATE_CACHE',
    (function () {
        $folder = get_stylesheet_directory() . '/';
        $templates = [];

        foreach (ROUTE_CATE_PAGES as $name => $route) {
            $templates[$route] = $folder . ROUTE_VIEWS_FOLDER . $route . '.php';
        }

        foreach (ROUTE_SINGLE_PAGES as $name => $route) {
            $templates[$route] = $folder . ROUTE_VIEWS_FOLDER . $route . '.php';
        }

        $templates['error_404'] = $folder  . '404.php';

        return $templates;
    })()
);


// 注册查询变量
add_filter('query_vars', function ($vars) {
    $vars[] = 'route_base';
    $vars[] = 'route_type';
    $vars[] = 'route_name';
    return $vars;
});

// 注册自定义路由（最高优先级）
add_action('init', function () {
    add_rewrite_rule(
        '^blog/category/([^/]+)/?$',
        'index.php?route_base=' . ROUTE_CATE_PAGES['blog_category'] . '&route_type=category&route_name=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^(blog|news)/([^/]+)/?$',
        'index.php?route_base=' . ROUTE_CATE_PAGES['post_page'] . '&route_type=$matches[1]&route_name=$matches[2]',
        'top'
    );

    // help/分类/文章
    add_rewrite_rule(
        '^help/([^/]+)(/([^/]+))?/?$',
        'index.php?route_base=' . ROUTE_CATE_PAGES['help_center'] . '&route_type=$matches[1]&route_name=$matches[3]',
        'top'
    );

    // product/ID/别名
    add_rewrite_rule(
        '^product/([^/]+)?/?$',
        'index.php?route_base=' . ROUTE_CATE_PAGES['product_page'] . '&route_name=$matches[1]',
        'top'
    );

    // 单页路由
    foreach (ROUTE_SINGLE_PAGES as $key => $value) {
        add_rewrite_rule(
            '^' . $key . '/?$',
            'index.php?route_base=' . $value,
            'top'
        );
    }
}, 999);

// 主题激活刷新路由
add_action('after_switch_theme', function () {
    flush_rewrite_rules();
});

// =========================================================================
// 🔥 核心：基于你的 pageType() 工具实现严格404拦截
// =========================================================================
add_filter('parse_request', 'cirnotob_redirect_router');
function cirnotob_redirect_router($wp)
{
    $route_base = $wp->query_vars['route_base'] ?? '';
    $page_type = Cirnotob_Util::pageType();
    // ------------------------------
    // 第一步：永远允许访问的页面（白名单）
    // ------------------------------
    $allowed_page_types = ['home', 'admin', 'login'];
    if (in_array($page_type, $allowed_page_types) || empty($route_base)) {
        return $wp;
    }

    // ------------------------------
    // 第二步：如果匹配到我们的自定义路由
    // ------------------------------

    if (!empty(ROUTE_REDIRECT[$route_base])) {
        wp_redirect(home_url('/' . ROUTE_REDIRECT[$route_base]), 301);
        exit;
    }
    // 1. 非法 route_base → 404
    if (!in_array($route_base, ROUTE_ALLOWED_BASES)) return cirnotob_go_to_404($wp);


    // 2. 模板不存在 → 404
    $template = ROUTE_TEMPLATE_CACHE[$route_base] ?? '';
    if (empty($template)) return cirnotob_go_to_404($wp);

    // 3. 正常加载自定义模板
    unset($wp->query_vars['error']);
    $wp->query_vars['is_404'] = false;
    $wp->query_vars['is_home'] = false;
    $wp->query_vars['is_front_page'] = false;
    $wp->query_vars['post_type'] = null;
    $wp->query_vars['name'] = null;
    $wp->query_vars['pagename'] = null;
    $wp->query_vars['is_custom_page'] = true;
    $wp->query_vars['cirnotob_template'] = $route_base;

    return $wp;
}

function cirnotob_go_to_404($wp)
{
    $wp->query_vars['is_404'] = true;
    $wp->query_vars['error'] = '404';
    $wp->query_vars['cirnotob_template'] = 'error_404';
    return $wp;
}

add_filter('template_include', 'cirnotob_template_loader', 999);
function cirnotob_template_loader($template)
{
    $template_name = get_query_var('cirnotob_template');
    if (empty($template_name)) return $template;

    $template_locate =  ROUTE_TEMPLATE_CACHE[$template_name] ?? ROUTE_TEMPLATE_CACHE['error_404'] ?? '';

    if (!empty($template_locate) && file_exists($template_locate)) {
        return $template_locate;
    }
    return locate_template('404.php');
}

// 修复自定义路由状态
add_action('template_redirect', 'cirnotob_template_redirect');
function cirnotob_template_redirect()
{
    // 禁用默认搜索
    if (Cirnotob_Util::pageType() === 'search') {
        wp_redirect(home_url('/'), 301);
        exit;
    }

    // 确保自定义路由不是首页/404
    if (get_query_var('route_base')) {
        global $wp_query;
        $wp_query->is_404 = false;
        $wp_query->is_home = false;
        $wp_query->is_page = true;
    }
}
