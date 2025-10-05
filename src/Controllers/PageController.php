<?php
require_once __DIR__.'/../helpers.php';

function page_render(array $data): void
{
  $pageTitle = $data['title'] ?? 'FoodShop';
  $pageLead = $data['lead'] ?? '';
  $sections = $data['sections'] ?? [];
  include __DIR__.'/../Views/page/layout.php';
}

function page_about(): void
{
  page_render([
    'title' => 'Giới thiệu FoodShop',
    'lead' => 'FoodShop là nền tảng giao đồ ăn giúp kết nối thực khách với những quán ngon quanh bạn chỉ trong vài cú chạm.',
    'sections' => [
      [
        'heading' => 'Sứ mệnh',
        'body' => 'Mang đến trải nghiệm đặt món nhanh, chuẩn, tận tâm; đồng hành cùng nhà hàng trong hành trình chuyển đổi số.',
      ],
      [
        'heading' => 'Câu chuyện thương hiệu',
        'body' => 'Ra đời từ 2020, FoodShop phát triển từ một nhóm nhỏ yêu ẩm thực đến cộng đồng hàng nghìn đối tác. Chúng tôi tin rằng mỗi món ăn đều có câu chuyện riêng đáng được kể.',
      ],
      [
        'heading' => 'Giá trị cốt lõi',
        'body' => '<ul><li>Khách hàng là trung tâm</li><li>Minh bạch và nhanh chóng</li><li>Sáng tạo không ngừng</li></ul>',
      ],
    ],
  ]);
}

function page_careers(): void
{
  page_render([
    'title' => 'Tuyển dụng',
    'lead' => 'Gia nhập đội ngũ FoodShop để cùng xây dựng trải nghiệm giao nhận ẩm thực xuất sắc.',
    'sections' => [
      [
        'heading' => 'Văn hoá làm việc',
        'body' => 'Chúng tôi đề cao tinh thần chủ động, hợp tác và học hỏi. Mỗi thành viên đều có tiếng nói và cơ hội tạo khác biệt.',
      ],
      [
        'heading' => 'Vị trí đang mở',
        'body' => '<ul><li>Product Manager – Nền tảng đặt món</li><li>Chuyên viên Marketing nội dung</li><li>Nhân viên chăm sóc đối tác nhà hàng</li></ul><p>Gửi CV về <a href="mailto:talent@foodshop.local">talent@foodshop.local</a>.</p>',
      ],
    ],
  ]);
}

function page_terms(): void
{
  page_render([
    'title' => 'Điều khoản sử dụng',
    'lead' => 'Vui lòng đọc kỹ các điều khoản dưới đây trước khi sử dụng dịch vụ FoodShop.',
    'sections' => [
      [
        'heading' => 'Cam kết của người dùng',
        'body' => '<ul><li>Cung cấp thông tin chính xác khi đặt hàng.</li><li>Không lạm dụng hệ thống để gây ảnh hưởng xấu đến đối tác.</li><li>Tuân thủ hướng dẫn thanh toán và nhận hàng.</li></ul>',
      ],
      [
        'heading' => 'Trách nhiệm của FoodShop',
        'body' => 'FoodShop đảm bảo bảo mật dữ liệu, hỗ trợ xử lý sự cố trong vòng 24 giờ và hoàn tiền khi đơn lỗi từ phía hệ thống.',
      ],
    ],
  ]);
}

function page_privacy(): void
{
  page_render([
    'title' => 'Chính sách bảo mật',
    'lead' => 'Chúng tôi tôn trọng và bảo vệ dữ liệu cá nhân của khách hàng.',
    'sections' => [
      [
        'heading' => 'Dữ liệu thu thập',
        'body' => 'Thông tin tài khoản, địa chỉ giao hàng, lịch sử đơn để cá nhân hoá trải nghiệm.',
      ],
      [
        'heading' => 'Mục đích sử dụng',
        'body' => 'Phục vụ đặt món, chăm sóc khách hàng, nâng cấp dịch vụ và tuân thủ pháp luật.',
      ],
      [
        'heading' => 'Quyền của bạn',
        'body' => 'Bạn có thể yêu cầu chỉnh sửa hoặc xoá dữ liệu bằng cách liên hệ support@foodshop.local.',
      ],
    ],
  ]);
}

function page_press(): void
{
  page_render([
    'title' => 'Press kit',
    'lead' => 'Tài nguyên dành cho báo chí và đối tác truyền thông.',
    'sections' => [
      [
        'heading' => 'Logo & nhận diện',
        'body' => 'Tải bộ nhận diện thương hiệu chuẩn định dạng PNG/SVG <a href="#">tại đây</a>.',
      ],
      [
        'heading' => 'Liên hệ truyền thông',
        'body' => 'Email: <a href="mailto:press@foodshop.local">press@foodshop.local</a> – Hotline: 0901 234 567.',
      ],
    ],
  ]);
}

function page_shipping(): void
{
  page_render([
    'title' => 'Chính sách giao hàng',
    'lead' => 'FoodShop phối hợp cùng đội ngũ tài xế đảm bảo món đến tay bạn nhanh nhất.',
    'sections' => [
      [
        'heading' => 'Phạm vi giao',
        'body' => 'Hiện hỗ trợ các quận trung tâm tại Hà Nội và TP.HCM, mở rộng liên tục theo quý.',
      ],
      [
        'heading' => 'Phí & thời gian',
        'body' => '<ul><li>Phí giao từ 15.000đ, miễn phí với đơn từ 99.000đ.</li><li>Thời gian dự kiến 25-40 phút tuỳ khoảng cách và khung giờ.</li></ul>',
      ],
      [
        'heading' => 'Hỗ trợ sự cố',
        'body' => 'Nếu đơn giao trễ hơn 20 phút so với dự kiến, liên hệ hotline để được hỗ trợ voucher bù.',
      ],
    ],
  ]);
}
