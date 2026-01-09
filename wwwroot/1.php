<?php
$encrypted = "AS1RMvfcR3Z4HEzIQOkUig==";
$key_hex = "3773f9a3c6da0e2e8175d62af43afa3d";

echo "加密字符串: " . $encrypted . "\n";
echo "密钥(hex): " . $key_hex . "\n";

// 将十六进制密钥转换为二进制
$key = hex2bin($key_hex);

// IV 为密钥的前16字节（因为密钥本身就是16字节，所以IV等于整个密钥）
$iv = $key; // 或者 $iv = substr($key, 0, 16);

echo "密钥长度: " . strlen($key) . " 字节\n";
echo "IV长度: " . strlen($iv) . " 字节\n\n";

// 使用 AES-128-CBC 解密
$plaintext = openssl_decrypt($encrypted, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

if ($plaintext === false) {
    echo "解密失败！错误: " . openssl_error_string() . "\n";
} else {
    echo "解密成功！\n";
    echo "解密结果(十六进制): " . bin2hex($plaintext) . "\n";
    echo "解密结果(原始): '" . $plaintext . "'\n";
    
    // 尝试检测是否为手机号
    $cleaned = trim($plaintext);
    if (preg_match('/^1[3-9]\d{9}$/', $cleaned)) {
        echo "检测到手机号: " . $cleaned . "\n";
    } else {
        // 尝试移除可能的填充
        $unpadded = rtrim($plaintext, "\x00..\x1F");
        if (preg_match('/^1[3-9]\d{9}$/', $unpadded)) {
            echo "移除填充后检测到手机号: " . $unpadded . "\n";
        }
    }
}

// 为了验证，我们也尝试加密一个已知的手机号看看是否匹配
echo "\n=== 验证加密过程 ===\n";
$test_phone = "13800138000";
$encrypted_test = openssl_encrypt($test_phone, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
echo "手机号 '{$test_phone}' 加密后(base64): " . base64_encode($encrypted_test) . "\n";
echo "是否与提供的密文相同? " . (base64_encode($encrypted_test) === $encrypted ? "是" : "否") . "\n";

// 尝试不同填充方式
echo "\n=== 尝试不同填充方式 ===\n";
$options = [
    'OPENSSL_RAW_DATA',
    'OPENSSL_ZERO_PADDING',
    'OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING'
];

foreach ($options as $option) {
    $plaintext = openssl_decrypt($encrypted, 'AES-128-CBC', $key, constant($option), $iv);
    if ($plaintext !== false) {
        echo "选项 {$option}: '" . $plaintext . "'\n";
    }
}
?>