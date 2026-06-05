<?php
function php_screw_encrypt($source, $key = 'WordPress@2025_StrongKey')
{
    $source = php_strip_whitespace($source);
    $source = str_replace('<?php', '', $source);
    $source = str_replace('?>', '', $source);

    $len = strlen($source);
    $key_len = strlen($key);
    $code = '';

    for ($i = 0; $i < $len; $i++) {
        $code .= $source[$i] ^ $key[$i % $key_len];
    }

    $code = base64_encode($code);
    return $code;
}

function php_screw_decrypt($code, $key = 'WordPress@2025_StrongKey')
{
    $code = base64_decode($code);
    $len = strlen($code);
    $key_len = strlen($key);
    $source = '';

    for ($i = 0; $i < $len; $i++) {
        $source .= $code[$i] ^ $key[$i % $key_len];
    }

    return $source;
}
