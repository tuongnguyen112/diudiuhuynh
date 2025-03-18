<?php
// Bật hiển thị lỗi để dễ dàng debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Đặt header trả về JSON
header('Content-Type: application/json');

// Kiểm tra yêu cầu action
if (isset($_GET['action']) && $_GET['action'] === 'getInfo') {
    // Kiểm tra các tham số
    if (!isset($_POST['receiverId'], $_POST['receiverPassword'])) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
        exit;
    }

    $receiverId = $_POST['receiverId'];
    $receiverPassword = $_POST['receiverPassword'];

    // Đọc dữ liệu từ data.json
    if (file_exists('data.json')) {
        $data = json_decode(file_get_contents('data.json'), true);

        // Kiểm tra lỗi phân tích cú pháp JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Lỗi phân tích cú pháp JSON']);
            exit;
        }

        // Tìm thông tin người nhận
        $found = false;
        foreach ($data as $key => $userData) {
            if ($userData['senderId'] === $receiverId && $userData['senderPassword'] === $receiverPassword) {
                // Trả về thông tin khi tìm thấy
                echo json_encode([
                    'success' => true,
                    'message' => $userData['message'],
                    'images' => $userData['imageUrls'],
                    'musicFiles' => $userData['musicFiles']
                ]);
                $found = true;
                exit;
            }
        }

        // Nếu không tìm thấy ID hoặc mật khẩu
        if (!$found) {
            echo json_encode(['success' => false, 'message' => 'ID hoặc mật khẩu không đúng']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy file dữ liệu']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
