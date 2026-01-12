<?php

/**
 * 抖音预售券预约订单SPI回调处理器
 */
use app\common\server\BaseServer;
use app\common\server\laike\DouyinServer;
use app\common\model\order\OrderLogModel;

class Spi extends BaseServer {


    /**
     * 处理SPI回调请求
     */
    public function handleRequest(): array {
        try {
            // 1. 获取请求信息
            $headers = $this->getRequestHeaders();
            $rawBody = $this->getRawBody();

            // 2. 验证签名
            if (!$this->verifySignature($headers, $rawBody)) {
                throw new Exception('签名验证失败', 400);
            }

            // 3. 解析数据
            $data = $this->parseRequestData($rawBody);

            // 4. 验证数据完整性
            $this->validateData($data);

            // 5. 处理事件
            $result = $this->processEvent($data);

            // 6. 返回响应
            return $this->buildSuccessResponse();
        } catch (Exception $e) {
            return $this->buildErrorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * 验证签名
     */
    private function verifySignature(array $headers, string $body): bool {
        $timestamp = $headers['x-tt-signature-timestamp'] ?? '';
        $nonce = $headers['x-tt-signature-nonce'] ?? '';
        $receivedSignature = $headers['x-tt-signature'] ?? '';

        // 检查必要参数
        if (empty($timestamp) || empty($nonce) || empty($receivedSignature)) {
            return false;
        }

        // 验证时间戳
        if (!$this->validateTimestamp($timestamp)) {
            return false;
        }

        // 生成签名
        $expectedSignature = $this->generateSignature($timestamp, $nonce, $body);

        // 安全比较签名
        return hash_equals($expectedSignature, $receivedSignature);
    }

    /**
     * 生成签名
     */
    private function generateSignature(string $timestamp, string $nonce, string $body): string {
        $stringToSign = $this->buildStringToSign($timestamp, $nonce, $body);
        return hash_hmac('sha256', $stringToSign, $this->token);
    }

    /**
     * 构建待签名字符串
     */
    private function buildStringToSign(string $timestamp, string $nonce, string $body): string {
        // 抖音SPI签名规则：token + timestamp + nonce + body
        return $this->token . $timestamp . $nonce . $body;
    }

    /**
     * 验证时间戳
     */
    private function validateTimestamp(string $timestamp): bool {
        $currentTime = time();
        $requestTime = (int) $timestamp;

        // 检查是否为有效时间戳
        if ($requestTime <= 0) {
            return false;
        }

        // 检查是否在容忍范围内
        return abs($currentTime - $requestTime) <= $this->tolerance;
    }

    /**
     * 解析请求数据
     */
    private function parseRequestData(string $rawBody): array {
        $data = json_decode($rawBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON解析失败: ' . json_last_error_msg(), 400);
        }

        return $data;
    }

    /**
     * 验证数据完整性
     */
    private function validateData(array $data): void {
        // 必需字段检查
        $requiredFields = ['event', 'data', 'msg_id'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("缺少必需字段: {$field}", 400);
            }
        }

        // 验证事件类型
        $validEvents = [
            'presale_coupon.order.create',
            'presale_coupon.order.cancel',
            'presale_coupon.order.pay_success',
            'presale_coupon.order.refund'
        ];

        if (!in_array($data['event'], $validEvents)) {
            throw new Exception('不支持的事件类型: ' . $data['event'], 400);
        }
    }

    /**
     * 处理事件
     */
    private function processEvent(array $data): bool {
        $eventType = $data['event'];
        $eventData = $data['data'];
        $msgId = $data['msg_id'];

        // 防止重复处理
        if ($this->isDuplicateMessage($msgId)) {
            throw new Exception('重复的消息处理', 409);
        }

        // 记录消息ID
        $this->recordMessageId($msgId);

        // 根据事件类型分发处理
        switch ($eventType) {
            case 'presale_coupon.order.create':
                return $this->handleOrderCreate($eventData);

            case 'presale_coupon.order.cancel':
                return $this->handleOrderCancel($eventData);

            case 'presale_coupon.order.pay_success':
                return $this->handleOrderPaySuccess($eventData);

            case 'presale_coupon.order.refund':
                return $this->handleOrderRefund($eventData);

            default:
                throw new Exception('未知事件类型', 400);
        }
    }

    /**
     * 处理预约订单创建
     */
    private function handleOrderCreate(array $orderData): bool {
        try {
            // 验证订单数据
            $this->validateOrderData($orderData);

            // 保存订单到数据库
            $orderId = $this->saveOrderToDatabase($orderData);

            // 异步处理后续逻辑
            $this->asyncProcessOrder($orderId, $orderData);

            // 记录日志
            $this->logOrderCreate($orderData);

            return true;
        } catch (Exception $e) {
            $this->logError('预约订单创建失败', $e, $orderData);
            throw new Exception('订单处理失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 验证订单数据
     */
    private function validateOrderData(array $orderData): void {
        $requiredFields = [
            'out_order_no',
            'order_id',
            'open_id',
            'create_time',
            'sku_id',
            'coupon_code',
            'order_status'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($orderData[$field]) || $orderData[$field] === '') {
                throw new Exception("订单数据缺少字段: {$field}");
            }
        }

        // 验证订单状态
        $validStatuses = ['待支付', '已支付', '已取消', '已退款'];
        if (!in_array($orderData['order_status'], $validStatuses)) {
            throw new Exception('无效的订单状态');
        }
    }

    /**
     * 保存订单到数据库
     */
    private function saveOrderToDatabase(array $orderData): string {
        // 数据库连接（示例使用PDO）
        $pdo = $this->getDatabaseConnection();

        // 检查订单是否已存在
        $stmt = $pdo->prepare("SELECT id FROM presale_orders WHERE out_order_no = ?");
        $stmt->execute([$orderData['out_order_no']]);

        if ($stmt->fetch()) {
            throw new Exception('订单已存在');
        }

        // 插入订单数据
        $sql = "INSERT INTO presale_orders (
            out_order_no, 
            order_id, 
            open_id, 
            create_time, 
            sku_id, 
            coupon_code, 
            order_status, 
            raw_data, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $orderData['out_order_no'],
            $orderData['order_id'],
            $orderData['open_id'],
            date('Y-m-d H:i:s', $orderData['create_time']),
            $orderData['sku_id'],
            $orderData['coupon_code'],
            $orderData['order_status'],
            json_encode($orderData, JSON_UNESCAPED_UNICODE)
        ]);

        return $pdo->lastInsertId();
    }

    /**
     * 异步处理订单
     */
    private function asyncProcessOrder(string $orderId, array $orderData): void {
        // 使用消息队列异步处理
        $this->sendToMessageQueue('presale_order_created', [
            'order_id' => $orderId,
            'order_data' => $orderData,
            'process_time' => time()
        ]);
    }

    /**
     * 检查重复消息
     */
    private function isDuplicateMessage(string $msgId): bool {
        // 使用Redis检查消息ID
        $redis = $this->getRedisConnection();
        $key = "spi_msg_id:" . $msgId;

        if ($redis->exists($key)) {
            return true;
        }

        // 设置24小时过期
        $redis->setex($key, 86400, 1);
        return false;
    }

    /**
     * 记录消息ID
     */
    private function recordMessageId(string $msgId): void {
        // 记录到数据库
        $pdo = $this->getDatabaseConnection();
        $sql = "INSERT INTO spi_message_logs (msg_id, event_type, received_at) VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE updated_at = NOW()";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$msgId, $_POST['event'] ?? 'unknown']);
    }

    /**
     * 构建成功响应
     */
    private function buildSuccessResponse(): array {
        return [
            'code' => 0,
            'message' => 'success',
            'timestamp' => time()
        ];
    }

    /**
     * 构建错误响应
     */
    private function buildErrorResponse(string $message, int $code = 500): array {
        http_response_code($code);

        return [
            'code' => $code,
            'message' => $message,
            'timestamp' => time()
        ];
    }

    /**
     * 获取请求头
     */
    private function getRequestHeaders(): array {
        if (function_exists('getallheaders')) {
            return array_change_key_case(getallheaders(), CASE_LOWER);
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerKey = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[strtolower($headerKey)] = $value;
            }
        }

        return $headers;
    }

    /**
     * 获取原始请求体
     */
    private function getRawBody(): string {
        return file_get_contents('php://input');
    }

    /**
     * 获取数据库连接
     */
    private function getDatabaseConnection(): PDO {
        static $pdo;

        if (!$pdo) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        }

        return $pdo;
    }

    /**
     * 获取Redis连接
     */
    private function getRedisConnection(): Redis {
        static $redis;

        if (!$redis) {
            $redis = new Redis();
            $redis->connect(REDIS_HOST, REDIS_PORT);
            if (defined('REDIS_PASSWORD')) {
                $redis->auth(REDIS_PASSWORD);
            }
        }

        return $redis;
    }

    /**
     * 发送到消息队列
     */
    private function sendToMessageQueue(string $queueName, array $data): void {
        // 使用Redis作为消息队列
        $redis = $this->getRedisConnection();
        $redis->lPush("queue:{$queueName}", json_encode($data));
    }

    /**
     * 记录订单创建日志
     */
    private function logOrderCreate(array $orderData): void {
        $logData = [
            'type' => 'presale_order_create',
            'order_no' => $orderData['out_order_no'],
            'open_id' => $orderData['open_id'],
            'status' => $orderData['order_status'],
            'timestamp' => time()
        ];

        file_put_contents(
                LOG_DIR . '/presale_order_' . date('Y-m-d') . '.log', json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND
        );
    }

    /**
     * 记录错误日志
     */
    private function logError(string $title, Exception $e, array $context = []): void {
        $logData = [
            'title' => $title,
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        error_log(json_encode($logData, JSON_UNESCAPED_UNICODE));

        // 写入文件日志
        file_put_contents(
                LOG_DIR . '/spi_error_' . date('Y-m-d') . '.log', json_encode($logData, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND
        );
    }

    /**
     * 其他事件处理方法
     */
    private function handleOrderCancel(array $orderData): bool {
        // 处理订单取消逻辑
        return $this->updateOrderStatus($orderData['out_order_no'], '已取消');
    }

    private function handleOrderPaySuccess(array $orderData): bool {
        // 处理支付成功逻辑
        return $this->updateOrderStatus($orderData['out_order_no'], '已支付');
    }

    private function handleOrderRefund(array $orderData): bool {
        // 处理退款逻辑
        return $this->updateOrderStatus($orderData['out_order_no'], '已退款');
    }

    private function updateOrderStatus(string $orderNo, string $status): bool {
        $pdo = $this->getDatabaseConnection();
        $sql = "UPDATE presale_orders SET order_status = ?, updated_at = NOW() WHERE out_order_no = ?";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$status, $orderNo]);
    }

}
