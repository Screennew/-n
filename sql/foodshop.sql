--==============================================================
-- TẠO CẤU TRÚC BẢNG
--==============================================================
CREATE DATABASE foodshop;
CREATE TABLE [categories] (
    [id] int NOT NULL IDENTITY(1,1) PRIMARY KEY,
    [name] nvarchar(120) NOT NULL
);
GO

CREATE TABLE [restaurants] (
    [id] int NOT NULL IDENTITY(1,1) PRIMARY KEY,
    [name] nvarchar(160) NOT NULL,
    [address] nvarchar(255) NULL,
    [phone] varchar(30) NULL,
    [created_at] datetime NOT NULL DEFAULT GETDATE()
);
GO

CREATE TABLE [products] (
    [id] int NOT NULL IDENTITY(1,1) PRIMARY KEY,
    [restaurant_id] int NULL,
    [category_id] int NULL,
    [name] nvarchar(160) NOT NULL,
    [description] nvarchar(max) NULL,
    [price] decimal(12,2) NOT NULL,
    [image_url] varchar(300) NULL,
    [created_at] datetime NOT NULL DEFAULT GETDATE()
);
GO

CREATE TABLE [users] (
    [id] int NOT NULL IDENTITY(1,1) PRIMARY KEY,
    [name] nvarchar(120) NOT NULL,
    [email] varchar(160) NOT NULL UNIQUE,
    [password_hash] varchar(255) NOT NULL,
    [role] nvarchar(10) NOT NULL DEFAULT N'customer',
    [created_at] datetime NOT NULL,
    CONSTRAINT [CK_users_role] CHECK ([role] IN (N'customer', N'admin'))
);
GO

CREATE TABLE [orders] (
    [id] int NOT NULL IDENTITY(1,1) PRIMARY KEY,
    [user_id] int NOT NULL,
    [customer_name] nvarchar(120) NULL,
    [phone] varchar(30) NULL,
    [address] nvarchar(255) NOT NULL,
    [note] nvarchar(255) NULL,
    [subtotal] decimal(12,2) NOT NULL,
    [discount] decimal(12,2) NOT NULL DEFAULT 0.00,
    [shipping] decimal(12,2) NOT NULL DEFAULT 0.00,
    [total] decimal(12,2) NOT NULL,
    [coupon_code] varchar(40) NULL,
    [status] nvarchar(10) NOT NULL DEFAULT N'pending',
    [created_at] datetime NOT NULL,
    CONSTRAINT [CK_orders_status] CHECK ([status] IN (N'pending', N'paid', N'cancelled'))
);
GO

CREATE TABLE [order_items] (
    [id] int NOT NULL IDENTITY(1,1) PRIMARY KEY,
    [order_id] int NOT NULL,
    [product_id] int NOT NULL,
    [price] decimal(12,2) NOT NULL,
    [qty] int NOT NULL
);
GO

CREATE TABLE [order_payments] (
    [id] int NOT NULL IDENTITY(1,1) PRIMARY KEY,
    [order_id] int NOT NULL UNIQUE,
    [method] nvarchar(40) NOT NULL,
    [status] nvarchar(10) NOT NULL DEFAULT N'pending',
    [created_at] datetime NOT NULL,
    [updated_at] datetime NOT NULL,
    CONSTRAINT [CK_order_payments_status] CHECK ([status] IN (N'pending', N'paid', N'failed'))
);
GO

CREATE TABLE [coupons] (
    [id] int NOT NULL IDENTITY(1,1) PRIMARY KEY,
    [code] varchar(40) NOT NULL UNIQUE,
    [type] nvarchar(10) NOT NULL DEFAULT N'percent',
    [value] decimal(10,2) NOT NULL,
    [active] bit NOT NULL DEFAULT 1,
    [expires_at] datetime NULL,
    CONSTRAINT [CK_coupons_type] CHECK ([type] IN (N'percent', N'fixed'))
);
GO

CREATE TABLE [product_reviews] (
    [id] int NOT NULL IDENTITY(1,1) PRIMARY KEY,
    [product_id] int NOT NULL,
    [user_id] int NOT NULL,
    [rating] tinyint NOT NULL,
    [comment] nvarchar(max) NULL,
    [created_at] datetime NOT NULL,
    [updated_at] datetime NOT NULL,
    CONSTRAINT [UQ_product_user_review] UNIQUE ([product_id], [user_id])
);
GO

CREATE TABLE [promotions] (
    [id] int NOT NULL IDENTITY(1,1) PRIMARY KEY,
    [title] nvarchar(160) NOT NULL,
    [description] nvarchar(255) NULL,
    [active] bit NOT NULL DEFAULT 1,
    [created_at] datetime NOT NULL DEFAULT GETDATE(),
    [image_url] varchar(300) NULL,
    [badge] nvarchar(40) NULL,
    [deeplink] varchar(300) NULL
);
GO

CREATE TABLE [admin_stats] (
    [id] int NOT NULL IDENTITY(1,1) PRIMARY KEY,
    [date] date NOT NULL,
    [total_orders] int NOT NULL,
    [total_revenue] decimal(12,2) NOT NULL,
    [created_at] datetime NOT NULL DEFAULT GETDATE()
);
GO


--==============================================================
-- THÊM DỮ LIỆU VÀO BẢNG
-- Lưu ý: IDENTITY_INSERT được dùng để chèn ID cụ thể
--==============================================================

SET IDENTITY_INSERT [categories] ON;
INSERT INTO [categories] ([id], [name]) VALUES
(1, N'Cơm'),
(2, N'Bún/Phở'),
(3, N'Pizza'),
(4, N'Đồ uống'),
(5, N'Ăn vặt'),
(6, N'Món nước'),
(7, N'Mì');
SET IDENTITY_INSERT [categories] OFF;
GO

SET IDENTITY_INSERT [coupons] ON;
INSERT INTO [coupons] ([id], [code], [type], [value], [active], [expires_at]) VALUES
(1, N'WELCOME10', N'percent', 10.00, 1, '2026-09-26 21:38:41'),
(2, N'GIAM20K', N'fixed', 20000.00, 1, '2026-03-26 21:38:41'),
(3, N'FREESHIP', N'fixed', 15000.00, 1, '2025-12-26 21:38:41');
SET IDENTITY_INSERT [coupons] OFF;
GO

SET IDENTITY_INSERT [users] ON;
INSERT INTO [users] ([id], [name], [email], [password_hash], [role], [created_at]) VALUES
(1, N'Admin', N'admin@foodshop.local', N'$2y$10$8f7ch4t2GvW7v3JYV6N7zuqQxH1m0t3Gv6k9nC7hZf4eQv8J8U6xW', N'admin', '2025-09-26 21:32:37'),
(2, N'Khách A', N'user@example.com', N'$2y$10$8f7ch4t2GvW7v3JYV6N7zuqQxH1m0t3Gv6k9nC7hZf4eQv8J8U6xW', N'customer', '2025-09-26 21:38:41'),
(4, N'Trang chủ', N'admin@gmail.com', N'$2y$10$qmY6uFi.iin70zMsYkj.8uFOdg2BLZkpTJ3uYuC/bUFghLvq6G4vC', N'admin', '2025-09-26 22:27:01'),
(5, N'Trang chủ', N'admin1@gmail.com', N'$2y$10$9vWe4x6NYyxCWs9ujtsCTu/Vdqduj8lKL8BPFlthknCOOeiEMUbN2', N'customer', '2025-09-27 00:01:41');
SET IDENTITY_INSERT [users] OFF;
GO

SET IDENTITY_INSERT [orders] ON;
INSERT INTO [orders] ([id], [user_id], [customer_name], [phone], [address], [note], [subtotal], [discount], [shipping], [total], [coupon_code], [status], [created_at]) VALUES
(3, 4, N'Trang chủ', N'08676656477', N'34', N'4', 282000.00, 0.00, 15000.00, 297000.00, NULL, N'paid', '2025-10-03 20:17:22'),
(4, 4, N'Trang chủ', N'0987565644', N'34', N'4', 116000.00, 0.00, 15000.00, 131000.00, NULL, N'paid', '2025-10-04 12:13:52');
SET IDENTITY_INSERT [orders] OFF;
GO

SET IDENTITY_INSERT [order_items] ON;
INSERT INTO [order_items] ([id], [order_id], [product_id], [price], [qty]) VALUES
(1, 3, 14, 29000.00, 3),
(2, 3, 15, 65000.00, 3),
(3, 4, 14, 29000.00, 4);
SET IDENTITY_INSERT [order_items] OFF;
GO

SET IDENTITY_INSERT [order_payments] ON;
INSERT INTO [order_payments] ([id], [order_id], [method], [status], [created_at], [updated_at]) VALUES
(1, 4, N'momo', N'pending', '2025-10-04 07:13:52', '2025-10-04 07:13:52');
SET IDENTITY_INSERT [order_payments] OFF;
GO

SET IDENTITY_INSERT [restaurants] ON;
INSERT INTO [restaurants] ([id], [name], [address], [phone], [created_at]) VALUES
(1, N'Bún Chả 24', N'Hai Bà Trưng, Hà Nội', N'0123456789', '2025-09-26 21:38:41'),
(2, N'Trà Sữa Panda', N'Cầu Giấy, Hà Nội', N'0987654321', '2025-09-26 21:38:41'),
(3, N'Cơm Tấm A.M', N'Đống Đa, Hà Nội', N'0911002200', '2025-09-26 21:38:41'),
(4, N'Pizza Corner', N'Tây Hồ, Hà Nội', N'0909090909', '2025-09-26 21:38:41'),
(5, N'Phở Bò 79', N'Ba Đình, Hà Nội', N'0988111222', '2025-09-26 21:38:41');
SET IDENTITY_INSERT [restaurants] OFF;
GO

SET IDENTITY_INSERT [products] ON;
INSERT INTO [products] ([id], [restaurant_id], [category_id], [name], [description], [price], [image_url], [created_at]) VALUES
(1, 1, 2, N'Bún chả Hà Nội', N'Đặc sản Hà Nội', 45000.00, N'https://picsum.photos/seed/buncha/600/400', '2025-09-26 21:38:41'),
(2, 1, 2, N'Bún nem thịt nướng', N'Nem rán + thịt nướng', 52000.00, N'https://picsum.photos/seed/bunnem/600/400', '2025-09-26 21:38:41'),
(3, 5, 2, N'Phở bò tái chín', N'Nước dùng đậm đà', 48000.00, N'https://picsum.photos/seed/phobo/600/400', '2025-09-26 21:38:41'),
(4, 5, 6, N'Phở gà', N'Thanh ngọt', 45000.00, N'https://picsum.photos/seed/phoga/600/400', '2025-09-26 21:38:41'),
(5, 3, 1, N'Cơm tấm sườn bì chả', N'Sốt mỡ hành', 55000.00, N'https://picsum.photos/seed/comtam/600/400', '2025-09-26 21:38:41'),
(6, 3, 1, N'Cơm gà xối mỡ', N'Giòn rụm', 50000.00, N'https://picsum.photos/seed/comgaxoimo/600/400', '2025-09-26 21:38:41'),
(7, 4, 3, N'Pizza Margherita 9"', N'Cổ điển Ý', 119000.00, N'https://picsum.photos/seed/pizza1/600/400', '2025-09-26 21:38:41'),
(8, 4, 3, N'Pizza Hải Sản 9"', N'Tôm mực phô mai', 139000.00, N'https://picsum.photos/seed/pizza2/600/400', '2025-09-26 21:38:41'),
(9, 2, 4, N'Trà sữa trân châu', N'Vị ngọt thanh', 35000.00, N'https://picsum.photos/seed/trasua/600/400', '2025-09-26 21:38:41'),
(10, 2, 4, N'Hồng trà kem cheese', N'Béo mặn', 39000.00, N'https://picsum.photos/seed/hongtra/600/400', '2025-09-26 21:38:41'),
(11, 2, 4, N'Trà đào cam sả', N'Mát lạnh', 39000.00, N'https://picsum.photos/seed/tradao/600/400', '2025-09-26 21:38:41'),
(12, 1, 7, N'Mì trộn tóp mỡ', N'Đậm vị', 42000.00, N'https://picsum.photos/seed/mitron/600/400', '2025-09-26 21:38:41'),
(13, 1, 5, N'Nem chua rán', N'Ăn vặt', 32000.00, N'https://picsum.photos/seed/nemchua/600/400', '2025-09-26 21:38:41'),
(14, 4, 5, N'Khoai tây chiên', N'Giòn rụm', 29000.00, N'https://picsum.photos/seed/fry/600/400', '2025-09-26 21:38:41'),
(15, 3, 5, N'Gà rán phần 2 miếng', N'Sốt cay', 65000.00, N'https://picsum.photos/seed/chicken/600/400', '2025-09-26 21:38:41');
SET IDENTITY_INSERT [products] OFF;
GO

SET IDENTITY_INSERT [product_reviews] ON;
INSERT INTO [product_reviews] ([id], [product_id], [user_id], [rating], [comment], [created_at], [updated_at]) VALUES
(1, 15, 4, 5, NULL, '2025-10-03 16:25:59', '2025-10-03 16:25:59');
SET IDENTITY_INSERT [product_reviews] OFF;
GO

SET IDENTITY_INSERT [promotions] ON;
INSERT INTO [promotions] ([id], [title], [description], [active], [created_at], [image_url], [badge], [deeplink]) VALUES
(1, N'Miễn phí ship đơn từ 99k', N'Áp dụng nội thành', 1, '2025-09-26 21:38:41', NULL, NULL, NULL),
(2, N'Combo trưa tiết kiệm', N'Giảm đến 30%', 1, '2025-09-26 21:38:41', NULL, NULL, NULL),
(3, N'Giảm 30% đơn trưa', N'Áp dụng 11:00–14:00', 1, '2025-09-26 23:08:10', N'https://picsum.photos/seed/promo1/640/360', N'Hot', N'?r=home'),
(4, N'Freeship 15k', N'ĐH từ 99k nội thành', 1, '2025-09-26 23:08:10', N'https://picsum.photos/seed/promo2/640/360', N'Free Ship', N'?r=home'),
(5, N'Combo tiết kiệm', N'Pizza + nước', 1, '2025-09-26 23:08:10', N'https://picsum.photos/seed/promo3/640/360', N'Combo', N'?r=home');
SET IDENTITY_INSERT [promotions] OFF;
GO


--==============================================================
-- TẠO CÁC RÀNG BUỘC KHÓA NGOẠI (FOREIGN KEY)
--==============================================================

ALTER TABLE [orders] ADD CONSTRAINT [FK_orders_users] FOREIGN KEY ([user_id]) REFERENCES [users] ([id]);
GO

ALTER TABLE [order_items] ADD CONSTRAINT [FK_order_items_orders] FOREIGN KEY ([order_id]) REFERENCES [orders] ([id]) ON DELETE CASCADE;
GO

ALTER TABLE [order_payments] ADD CONSTRAINT [FK_order_payments_order] FOREIGN KEY ([order_id]) REFERENCES [orders] ([id]) ON DELETE CASCADE;
GO

ALTER TABLE [products] ADD CONSTRAINT [FK_products_restaurants] FOREIGN KEY ([restaurant_id]) REFERENCES [restaurants] ([id]) ON DELETE SET NULL;
GO

ALTER TABLE [products] ADD CONSTRAINT [FK_products_categories] FOREIGN KEY ([category_id]) REFERENCES [categories] ([id]) ON DELETE SET NULL;
GO

ALTER TABLE [product_reviews] ADD CONSTRAINT [FK_reviews_product] FOREIGN KEY ([product_id]) REFERENCES [products] ([id]) ON DELETE CASCADE;
GO

ALTER TABLE [product_reviews] ADD CONSTRAINT [FK_reviews_user] FOREIGN KEY ([user_id]) REFERENCES [users] ([id]) ON DELETE CASCADE;
GO