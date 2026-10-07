<?php
declare(strict_types=1);
// Presentation fixtures only. Prices and book details are illustrative.
$rows = [
    [1, 'Chiến binh cầu vồng', 'Andrea Hirata', 'van-hoc', 89000, 110000, 'Bán chạy', 4.8, 1250, '9780374533330'],
    [2, 'Cha giàu cha nghèo', 'Robert T. Kiyosaki', 'kinh-te', 125000, 125000, 'Bán chạy', 4.7, 890, '9781612680194'],
    [3, 'Dune – Xứ cát', 'Frank Herbert', 'khoa-hoc', 150000, 180000, 'Bán chạy', 4.9, 2340, '9780441172719'],
    [4, 'Truyện kể cho bé', 'Nhiều tác giả', 'thieu-nhi', 65000, 65000, 'Mới', 4.6, 450, ''],
    [5, 'Sapiens – Lược sử loài người', 'Yuval Noah Harari', 'lich-su', 145000, 145000, 'Bán chạy', 4.8, 1890, '9780062316097'],
    [6, 'Atomic Habits – Thay đổi tí hon', 'James Clear', 'ky-nang', 135000, 160000, 'Mới', 4.9, 2100, '9780735211292'],
    [7, 'Steve Jobs', 'Walter Isaacson', 'tieu-su', 165000, 190000, '', 4.7, 780, '9781451648539'],
    [8, 'Tâm lý học về tiền', 'Morgan Housel', 'kinh-te', 115000, 115000, 'Bán chạy', 4.8, 1340, '9780857197689'],
    [9, 'Nhà giả kim', 'Paulo Coelho', 'van-hoc', 79000, 99000, 'Bán chạy', 4.8, 1530, '9780061122415'],
    [10, 'Hoàng tử bé', 'Antoine de Saint-Exupéry', 'thieu-nhi', 68000, 85000, '', 4.9, 840, '9780156012195'],
    [11, 'One Piece – Tập 1', 'Eiichiro Oda', 'truyen-tranh', 25000, 25000, 'Bán chạy', 4.9, 970, '9781569319017'],
    [12, 'Thám tử lừng danh Conan – Tập 1', 'Gosho Aoyama', 'truyen-tranh', 25000, 25000, '', 4.8, 680, '9781591163275'],
    [13, 'Lược sử thời gian', 'Stephen Hawking', 'khoa-hoc', 120000, 150000, '', 4.7, 610, '9780553380163'],
    [14, 'Đắc nhân tâm', 'Dale Carnegie', 'ky-nang', 86000, 108000, 'Bán chạy', 4.8, 2200, '9780671027032'],
    [15, 'Totto-chan bên cửa sổ', 'Tetsuko Kuroyanagi', 'thieu-nhi', 92000, 115000, '', 4.9, 720, '9781568363912'],
    [16, 'Shoe Dog – Gã nghiện giày', 'Phil Knight', 'tieu-su', 159000, 199000, '', 4.8, 540, '9781501135927'],
    [17, 'Những người khốn khổ', 'Victor Hugo', 'van-hoc', 210000, 250000, '', 4.8, 320, '9780451419439'],
    [18, 'Nghĩ giàu và làm giàu', 'Napoleon Hill', 'kinh-te', 98000, 120000, '', 4.6, 690, '9781585424337'],
    [19, 'Homo Deus – Lược sử tương lai', 'Yuval Noah Harari', 'lich-su', 179000, 219000, 'Mới', 4.7, 470, '9780062464316'],
    [20, 'Dragon Ball – Tập 1', 'Akira Toriyama', 'truyen-tranh', 28000, 28000, '', 4.9, 920, '9781569319208'],
    [21, 'Vũ trụ', 'Carl Sagan', 'khoa-hoc', 175000, 210000, '', 4.8, 380, '9780345539434'],
    [22, 'Không gia đình', 'Hector Malot', 'thieu-nhi', 95000, 120000, '', 4.7, 520, ''],
    [23, 'Đi tìm lẽ sống', 'Viktor E. Frankl', 'ky-nang', 78000, 98000, '', 4.9, 1130, '9780807014271'],
    [24, 'Nhật ký Anne Frank', 'Anne Frank', 'tieu-su', 110000, 135000, 'Mới', 4.8, 680, '9780553296983'],
];
$categories = array_column(require dirname(__DIR__) . '/config/categories.php', null, 'slug');
$descriptions = [
    'van-hoc' => 'Một hành trình qua những số phận và câu chuyện giàu cảm xúc. Từng trang sách mở ra một góc nhìn mới về cuộc sống, tình yêu và những điều giản dị quanh ta.',
    'kinh-te' => 'Khám phá những góc nhìn về kinh doanh, tài chính và cách đưa ra quyết định. Cuốn sách mang đến những câu chuyện gần gũi để bạn suy ngẫm và học hỏi.',
    'khoa-hoc' => 'Dành cho những tâm hồn tò mò, cuốn sách đưa bạn khám phá những thế giới mới, mở rộng trí tưởng tượng và đặt câu hỏi về vũ trụ quanh mình.',
    'truyen-tranh' => 'Cùng những nhân vật yêu thích bước vào một hành trình đầy bất ngờ. Nét vẽ sinh động, câu chuyện hấp dẫn và những khoảnh khắc đáng nhớ đang chờ bạn.',
    'thieu-nhi' => 'Một món quà nhỏ cho trí tưởng tượng. Những câu chuyện trong trẻo giúp bạn đọc nhỏ tuổi khám phá cuộc sống, nuôi dưỡng sự đồng cảm và niềm vui đọc sách.',
    'lich-su' => 'Nhìn lại những bước ngoặt trong hành trình của con người. Một góc nhìn rộng mở giúp kết nối câu chuyện của quá khứ với thế giới hôm nay.',
    'tieu-su' => 'Theo dấu một cuộc đời, những lựa chọn và những bước ngoặt đáng nhớ. Những trang sách mang đến cảm hứng từ trải nghiệm và hành trình của con người.',
    'ky-nang' => 'Dành một khoảng lặng để hiểu mình hơn. Cuốn sách gợi mở những ý tưởng và thói quen nhỏ để bạn xây dựng một cuộc sống có ý nghĩa mỗi ngày.',
];
return array_map(function ($row) use ($categories, $descriptions) {
    [$id, $title, $author, $category, $price, $oldPrice, $badge, $rating, $reviews, $isbn] = $row;
    $photos = ['van-hoc' => 'books-stack', 'kinh-te' => 'notebook', 'khoa-hoc' => 'shelves', 'truyen-tranh' => 'children', 'thieu-nhi' => 'children', 'lich-su' => 'classics', 'tieu-su' => 'notebook', 'ky-nang' => 'books-stack'];
    $photo = 'images/' . ($id === 8 ? 'classics' : $photos[$category]) . '.jpg';
    return [
        'id' => $id, 'title' => $title, 'author' => $author, 'category' => $category,
        'category_name' => $categories[$category]['name'], 'price' => $price, 'old_price' => $oldPrice,
        'badge' => $badge, 'rating' => $rating, 'reviews' => $reviews, 'isbn' => $isbn,
        'image' => $photo,
        'description' => $descriptions[$category], 'pages' => $category === 'truyen-tranh' ? 192 : 256 + ($id % 5) * 48,
        'publisher' => $category === 'truyen-tranh' ? 'NXB Kim Đồng' : 'NXB Thế Giới',
        'year' => 2024, 'stock' => 20,
    ];
}, $rows);
