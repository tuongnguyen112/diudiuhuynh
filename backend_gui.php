<?php

if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}
if (!is_dir('uploads/images')) {
    mkdir('uploads/images', 0777, true);
}
if (!is_dir('uploads/music')) {
    mkdir('uploads/music', 0777, true);
}

if ($_GET['action'] === 'create') {
    // Kiểm tra các trường bắt buộc
    if (!isset($_POST['senderId'], $_POST['senderPassword'], $_POST['message'], $_FILES['imageFiles'])) {
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc: ID người gửi, mật khẩu, lời chúc hoặc ảnh']);
        exit;
    }

    // Đọc thông tin người gửi
    $senderId = $_POST['senderId'];
    $senderPassword = $_POST['senderPassword'];
    $message = $_POST['message'];  // Lời chúc có thể rỗng nhưng phải có trường này

    // Kiểm tra xem senderId đã tồn tại chưa (trong file data.json)
    if (file_exists('data.json')) {
        // Đọc dữ liệu từ file JSON và giải mã thành mảng
        $existingData = json_decode(file_get_contents('data.json'), true);

        // Kiểm tra nếu file JSON không hợp lệ
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu trong file JSON không hợp lệ']);
            exit;
        }

        // Kiểm tra nếu senderId đã tồn tại trong dữ liệu
        if (is_array($existingData)) {
            foreach ($existingData as $userData) {
                if (isset($userData['senderId']) && $userData['senderId'] === $senderId) {
                    echo json_encode(['success' => false, 'message' => 'ID người gửi đã tồn tại. Vui lòng chọn một ID khác.']);
                    exit;
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ trong file JSON']);
            exit;
        }
    }

    // Xử lý ảnh
    $imageUrls = [];
    if (isset($_FILES['imageFiles']) && count($_FILES['imageFiles']['tmp_name']) > 0) {
        $sttImage = 1; // Đặt số thứ tự ảnh bắt đầu từ 1
        foreach ($_FILES['imageFiles']['tmp_name'] as $key => $tmp_name) {
            $fileExtension = pathinfo($_FILES['imageFiles']['name'][$key], PATHINFO_EXTENSION);
            // Đặt tên file theo định dạng img_$sttImage_$senderId.$fileExtension
            $fileName = 'uploads/images/img_' . $sttImage . '_' . $senderId . '.' . $fileExtension;

            // Kiểm tra nếu file đã tồn tại, thêm số ngẫu nhiên vào tên
            while (file_exists($fileName)) {
                $fileName = 'uploads/images/img_' . $sttImage . '_' . $senderId . '_' . rand(1000, 9999) . '.' . $fileExtension;
            }

            // Di chuyển ảnh vào thư mục
            if (move_uploaded_file($tmp_name, $fileName)) {
                $imageUrls[] = $fileName;
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi tải ảnh lên']);
                exit;
            }

            $sttImage++; // Tăng số thứ tự ảnh sau mỗi lần lặp
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Cần tải lên ít nhất một ảnh']);
        exit;
    }

    // Xử lý nhạc
    $musicUrls = [];
    if (isset($_FILES['musicFiles']) && count($_FILES['musicFiles']['tmp_name']) > 0) {
        if (count($_FILES['musicFiles']['tmp_name']) > 1) {
            echo json_encode(['success' => false, 'message' => 'Chỉ cho phép tải lên một file nhạc']);
            exit;
        }

        $sttMusic = 1; // Đặt số thứ tự nhạc bắt đầu từ 1
        foreach ($_FILES['musicFiles']['tmp_name'] as $key => $tmp_name) {
            $fileExtension = pathinfo($_FILES['musicFiles']['name'][$key], PATHINFO_EXTENSION);
            // Đặt tên file theo định dạng music_$sttMusic_$senderId.$fileExtension
            $fileName = 'uploads/music/music_' . $sttMusic . '_' . $senderId . '.' . $fileExtension;

            // Kiểm tra nếu file đã tồn tại, thêm số ngẫu nhiên vào tên
            while (file_exists($fileName)) {
                $fileName = 'uploads/music/music_' . $sttMusic . '_' . $senderId . '_' . rand(1000, 9999) . '.' . $fileExtension;
            }

            // Di chuyển file nhạc vào thư mục
            if (move_uploaded_file($tmp_name, $fileName)) {
                $musicUrls[] = $fileName;
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi tải file nhạc lên']);
                exit;
            }

            $sttMusic++; // Tăng số thứ tự nhạc sau mỗi lần lặp
        }
    }

    // Lưu thông tin vào file JSON
    $data = [
        'senderId' => $senderId,
        'senderPassword' => $senderPassword,
        'message' => $message,
        'imageUrls' => $imageUrls,
        'musicFiles' => $musicUrls
    ];

    // Đọc dữ liệu hiện có từ file data.json (nếu có)
    if (file_exists('data.json')) {
        $existingData = json_decode(file_get_contents('data.json'), true);
    } else {
        $existingData = [];
    }

    // Thêm dữ liệu mới vào mảng dữ liệu
    $existingData[] = $data;

    // Lưu lại dữ liệu vào file
    file_put_contents('data.json', json_encode($existingData));

    // Trả về kết quả thành công
    echo json_encode(['success' => true]);
} else {
    // Nếu action không phải là 'create'
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

?>
