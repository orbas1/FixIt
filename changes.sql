INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (180, 'backend.theme_option.index', 'web', NULL, NULL), (181, 'backend.theme_option.edit', 'web', NULL, NULL);

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('180', '1'), ('181', '1');

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (182, 'backend.home_page.index', 'web', NULL, NULL), (183, 'backend.home_page.edit', 'web', NULL, NULL);


INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('182', '1'), ('183', '1');


INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES ('184', 'backend.testimonial.index', 'web', NULL, NULL), ('185', 'backend.testimonial.create', 'web', NULL, NULL), ('186', 'backend.testimonial.edit', 'web', NULL, NULL), ('187', 'backend.testimonial.destroy', 'web', NULL, NULL);

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('184', '1'), ('185', '1'), ('186', '1'),('187', '1');

CREATE TABLE `home_pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content`)),
  `slug` varchar(191) DEFAULT NULL,
  `status` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `home_pages`
--

INSERT INTO `home_pages` (`id`, `content`, `slug`, `status`, `created_at`, `updated_at`) VALUES
(1, '{\"home_banner\":{\"status\":true,\"main_banner\":{\"title\":\"Modern Themes & Website Template for any project\",\"sub_title\":\"Discover thousands of digital product & downloads\",\"search_enable\":true,\"service_ids\":[]}},\"categories_icon_list\":{\"status\":true,\"category_ids\":[]},\"value_banners\":{\"title\":\"Best Valuable Deals\",\"status\":true,\"banners\":[{\"image_url\":\"http:\\/\\/localhost:8001\\/frontend\\/images\\/offer\\/1.png\",\"status\":true,\"redirect_link\":{\"link\":\"\",\"link_type\":\"collection\"}},{\"image_url\":\"http:\\/\\/localhost:8001\\/frontend\\/images\\/offer\\/2.png\",\"status\":true,\"redirect_link\":{\"link\":\"\",\"link_type\":\"collection\"}},{\"image_url\":\"http:\\/\\/localhost:8001\\/frontend\\/images\\/offer\\/3.png\",\"status\":true,\"redirect_link\":{\"link\":\"\",\"link_type\":\"collection\"}}]},\"service_list_1\":{\"title\":\"Featured Services\",\"product_ids\":[],\"status\":true},\"download\":{\"status\":true,\"image_url\":\"http:\\/\\/localhost:8001\\/frontend\\/images\\/gif\\/app-gif.gif\",\"title\":\"FixitCustomer, Provider, Servicemen & Admin application for iOS & Android\",\"description\":\"Buyers can discover local services in a click! through our Google Map integration which enhances top level buyer experiences using their GPS locations\",\"points\":[\"Buyers can discover local services in a click.\",\"Buyers can discover local.\",\"Buyers can discover local services.\"],\"app_store_url\":\"#\",\"google_play_store_url\":\"#\"},\"providers_list\":{\"status\":true,\"title\":\"Expert provider by rating\",\"provider_ids\":[]},\"service_packages_list\":{\"status\":true,\"title\":\"Expert provider by rating\",\"provider_ids\":[]},\"blogs_list\":{\"title\":\"Latest blog\",\"description\":null,\"status\":true,\"blog_ids\":[]},\"become_a_provider\":{\"status\":true,\"image_url\":\"http:\\/\\/localhost:8001\\/frontend\\/frontend\\/images\\/girl.png\",\"float_image_1_url\":\"http:\\/\\/localhost:8001\\/frontend\\/images\\/chart.png\",\"float_image_2_url\":\"http:\\/\\/localhost:8001\\/frontend\\/images\\/avatars.png\",\"title\":\"Earn more and deliver your service to worldwide by become a Service Provider\",\"description\":\"Buyers can discover local services in a click! through our Google Map integration which.\",\"points\":[\"Buyers can discover local services in a click.\",\"Buyers can discover local.\",\"Buyers can discover local services.\"],\"button_text\":\"Become a Provider\",\"button_url\":\"#\"},\"review_slider\":{\"status\":true,\"title\":\"What our user have to say about us ?\",\"review_ids\":[]},\"news_letter\":{\"status\":true,\"title\":\"SUBSCRIBE TO OUR NEWSLETTER\",\"sub_title\":\"We promise not to spam you.\",\"button_text\":\"Subscribe Now\",\"bg_image_url\":\"\"}}', 'default', 1, '2024-09-08 02:27:19', '2024-09-08 02:27:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `home_pages`
--
ALTER TABLE `home_pages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `home_pages`
--
ALTER TABLE `home_pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;


CREATE TABLE `theme_options` (
  `id` bigint UNSIGNED NOT NULL,
  `options` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `theme_options`
--
ALTER TABLE `theme_options`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `theme_options`
--
ALTER TABLE `theme_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2024 at 06:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fixit_dev`
--


--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


--
-- Table structure for table `subscribes`
--

CREATE TABLE `subscribes` (
  `id` bigint NOT NULL,
  `email` longtext,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `subscribes`
--
ALTER TABLE `subscribes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `subscribes`
--
ALTER TABLE `subscribes`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

ALTER TABLE `payment_gateways_transactions` ADD `request_type` ENUM('web','api') NULL DEFAULT 'api' AFTER `item_id`;


--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` bigint NOT NULL,
  `message` longtext,
  `user_id` int DEFAULT NULL,
  `blog_id` int DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tabless
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;
COMMIT;


INSERT INTO `permissions`(`id`,`name`, `guard_name`, `created_at`, `updated_at`) VALUES ('188', 'backend.news_letter.index', 'web', NULL, NULL),('189', 'backend.news_letter.create', 'web', NULL, NULL),('190', 'backend.news_letter.edit', 'web', NULL, NULL),('191', 'backend.news_letter.destroy', 'web', NULL, NULL);

INSERT INTO `role_has_permissions` (`permission_id`,`role_id`) VALUES ('188', '1'),('189', '1'),('190', '1'),('191', '1');

INSERT INTO `modules` (`id`,`name`, `actions`, `created_at`, `updated_at`) VALUES (NULL, 'news_letters', '{\"index\":\"backend.news_letter.index\",\"create\":\"backend.news_letter.create\",\"edit\":\"backend.news_letter.edit\",\"destroy\":\"backend.news_letter.destroy\"}', NULL, NULL);



ALTER TABLE `users` ADD `slug` LONGTEXT NULL AFTER `name`;


ALTER TABLE `service_packages` ADD `bg_color` VARCHAR(255) NULL DEFAULT 'primary' AFTER `hexa_code`;

ALTER TABLE `services` ADD `content` LONGTEXT NULL AFTER `description`;


UPDATE `home_pages` SET `content` = '{\n  \"home_banner\": {\n    \"title\": \"One-Stop Solution For Your\",\n    \"animate_text\": \"home service\",\n    \"description\": \"We connect you with trusted servicemen for all your home and business needs! 🏠💼 From repairs to installations, we’ve got you covered. 🔧✅ Easy booking, clear pricing, and stress-free service! 😊.\",\n    \"search_enable\": \"1\",\n    \"service_ids\": [\n      \"8\",\n      \"10\",\n      \"15\"\n    ],\n    \"status\": \"1\"\n  },\n  \"categories_icon_list\": {\n    \"title\": \"Top Categories\",\n    \"category_ids\": [\n      \"51\",\n      \"52\",\n      \"53\",\n      \"54\"\n    ],\n    \"status\": \"1\"\n  },\n  \"value_banners\": {\n    \"title\": \"Best Valuable Deals\",\n    \"status\": \"1\",\n    \"banners\": [\n      {\n        \"title\": \"Electrical service\",\n        \"description\": \"If you want to have stunning look of your house.\",\n        \"sale_tag\": \"Sale 40%\",\n        \"button_text\": \"Book Now\",\n        \"redirect_type\": \"service\",\n        \"redirect_id\": \"3\",\n        \"button_url\": \"#\",\n        \"image_url\": \"/frontend/images/offer/1.png\"\n      },\n      {\n        \"title\": \"Furniture service\",\n        \"description\": \"If you want to have stunning look of your house.\",\n        \"sale_tag\": \"Sale 50%\",\n        \"button_text\": \"Book Now\",\n        \"redirect_type\": \"package\",\n        \"redirect_id\": \"3\",\n        \"button_url\": \"#\",\n        \"image_url\": \"/frontend/images/offer/2.png\"\n      },\n      {\n        \"title\": \"Ac cleaning service\",\n        \"description\": \"If you want to have stunning look of your house.\",\n        \"sale_tag\": \"Sale 60%\",\n        \"button_text\": \"Book Now\",\n        \"redirect_type\": \"external_url\",\n        \"button_url\": \"https://frozendeadguydays.com/\",\n        \"image_url\": \"/frontend/images/offer/3.png\"\n      }\n    ]\n  },\n  \"service_list_1\": {\n    \"title\": \"Featured Services\",\n    \"service_ids\": [\n      \"3\",\n      \"4\",\n      \"5\",\n      \"6\",\n      \"7\",\n      \"9\",\n      \"10\",\n      \"11\"\n    ],\n    \"status\": \"1\"\n  },\n  \"download\": {\n    \"status\": \"1\",\n    \"title\": \"FixitCustomer, Provider, Servicemen & Admin application for iOS & Android\",\n    \"description\": \"Buyers can discover local services in a click! through our Google Map integration which enhances top level buyer experiences using their GPS locations\",\n    \"image_url\": \"/frontend/images/gif/app-gif.gif\"\n  },\n  \"providers_list\": {\n    \"title\": \"Expert provider by rating\",\n    \"provider_ids\": [\n      \"3\",\n      \"20\",\n      \"21\",\n      \"22\",\n      \"23\"\n    ],\n    \"status\": \"1\"\n  },\n  \"service_packages_list\": {\n    \"title\": \"Expert provider by rating\",\n    \"service_packages_ids\": [\n      \"1\",\n      \"2\",\n      \"3\",\n      \"4\",\n      \"5\",\n      \"6\"\n    ],\n    \"status\": \"1\"\n  },\n  \"blogs_list\": {\n    \"title\": \"Latest blog\",\n    \"description\": null,\n    \"blog_ids\": [\n      \"1\",\n      \"2\",\n      \"3\",\n      \"4\",\n      \"5\"\n    ],\n    \"status\": \"1\"\n  },\n  \"become_a_provider\": {\n    \"status\": \"1\",\n    \"title\": \"Earn more and deliver your service to worldwide by become a Service Provider\",\n    \"description\": \"Buyers can discover local services in a click! through our Google Map integration which.\",\n    \"button_text\": \"Become a Provider\",\n    \"button_url\": \"#\",\n    \"image_url\": \"/frontend/images/girl.png\",\n    \"float_image_1_url\": \"/frontend/images/chart.png\",\n    \"float_image_2_url\": \"/frontend/images/avatars.png\"\n  },\n  \"testimonial\": {\n    \"title\": \"Testimonials\",\n    \"status\": \"1\"\n  },\n  \"news_letter\": {\n    \"title\": \"SUBSCRIBE TO OUR NEWSLETTER\",\n    \"sub_title\": \"We promise not to spam you.\",\n    \"button_text\": \"Subscribe Now\",\n    \"status\": \"1\",\n    \"bg_image_url\": \"\"\n  }\n}' WHERE `home_pages`.`id` = 1;

INSERT INTO `theme_options` (`id`, `options`, `created_at`, `updated_at`) VALUES (NULL, '{\r\n    \"seo\": {\r\n        \"og_image\": null,\r\n        \"og_title\": \"Fixit Marketplace: Uniting Vendors for Shopping Excellence\",\r\n        \"meta_tags\": \"Fixit Marketplace: Where Vendors Shine Together\",\r\n        \"meta_title\": \"Online Marketplace, Vendor Collaboration, E-commerce Platform\",\r\n        \"og_description\": \"Experience a unique shopping journey at Fixit Marketplace, where vendors collaborate to provide a vast array of products. Explore, shop, and connect in one convenient destination.\",\r\n        \"meta_description\": \"Discover Fixit Marketplace – a vibrant online platform where vendors unite to showcase their products, creating a diverse shopping experience. Explore a wide range of offerings and connect with sellers on a single platform.\"\r\n    },\r\n    \"footer\": {\r\n        \"pages\": [\r\n            {\r\n                \"name\": \"Privacy Policy\",\r\n                \"slug\": \"privacy-policy\"\r\n            },\r\n            {\r\n                \"name\": \"Terms & Conditions\",\r\n                \"slug\": \"terms-conditions\"\r\n            },\r\n            {\r\n                \"name\": \"Contact Us\",\r\n                \"slug\": \"contact-us\"\r\n            },\r\n            {\r\n                \"name\": \"About Us\",\r\n                \"slug\": \"about-us\"\r\n            }\r\n        ],\r\n        \"others\": [\r\n            {\r\n                \"name\": \"My Account\",\r\n                \"slug\": \"profile\"\r\n            },\r\n            {\r\n                \"name\": \"Favorite\",\r\n                \"slug\": \"favorite\"\r\n            },\r\n            {\r\n                \"name\": \"Bookings\",\r\n                \"slug\": \"booking\"\r\n            },\r\n            {\r\n                \"name\": \"Providers\",\r\n                \"slug\": \"providers\"\r\n            },\r\n            {\r\n                \"name\": \"Services\",\r\n                \"slug\": \"service\"\r\n            }\r\n        ],\r\n        \"useful_link\": [\r\n            {\r\n                \"name\": \"Home\",\r\n                \"slug\": \"/\"\r\n            },\r\n            {\r\n                \"name\": \"Categories\",\r\n                \"slug\": \"category\"\r\n            },\r\n            {\r\n                \"name\": \"Services\",\r\n                \"slug\": \"service\"\r\n            },\r\n            {\r\n                \"name\": \"Providers\",\r\n                \"slug\": \"providers\"\r\n            }\r\n        ],\r\n        \"footer_copyright\": \"©2024 Fixit All rights reserved\",\r\n        \"become_a_provider\": {\r\n            \"description\": \"Earn more and deliver your service to worldwide.\",\r\n            \"become_a_provider_enable\": true\r\n        }\r\n    },\r\n    \"header\": {\r\n        \"home\": true,\r\n        \"blogs\": true,\r\n        \"bookings\": true,\r\n        \"services\": true,\r\n        \"categories\": true\r\n    },\r\n    \"general\": {\r\n        \"site_title\": \"Fixit\",\r\n        \"footer_logo\": \"/frontend/images/logo/dark-logo.png\",\r\n        \"header_logo\": \"/frontend/images/logo/dark-logo.png\",\r\n        \"favicon_icon\": \"/frontend/images/logo/favicon-icon.png\",\r\n        \"site_tagline\": \"Your One-Stop Solution for for your home services\",\r\n        \"app_store_url\": \"https://www.apple.com/in/app-store/\",\r\n        \"google_play_store_url\": \"https://play.google.com/store/apps/\",\r\n        \"breadcrumb_description\": \"Select a service from the below category list that correlates with your needs. It includes 15+ categories with 560+ different services in various sector.\"\r\n    },\r\n    \"about_us\": {\r\n        \"title\": \"Our Mission\",\r\n        \"status\": true,\r\n        \"banners\": [\r\n            {\r\n                \"count\": \"3.5\",\r\n                \"title\": \"Years Experience\"\r\n            },\r\n            {\r\n                \"count\": \"520\",\r\n                \"title\": \"Positive Reviews\"\r\n            },\r\n            {\r\n                \"count\": \"10000\",\r\n                \"title\": \"Trusted Client\"\r\n            },\r\n            {\r\n                \"count\": \"60\",\r\n                \"title\": \"Team Member\"\r\n            }\r\n        ],\r\n        \"sub_title1\": \"Delivering Excellence:\",\r\n        \"sub_title2\": \"Empowering Our Clients:\",\r\n        \"sub_title3\": \"Fostering Innovation:\",\r\n        \"sub_title4\": \"Building Strong Relationships:\",\r\n        \"sub_title5\": \"Contributing to the Community:\",\r\n        \"description\": \"At Fixit, our mission is to be more than just a service provider—we aim to be a trusted partner in your journey to success. We are committed to:\",\r\n        \"description1\": \"We deliver quality with precision, consistently exceeding expectations.\",\r\n        \"description2\": \"We provide tools and insights to empower your success now and in the future.\",\r\n        \"description3\": \"We embrace innovation, continuously improving to keep our clients ahead\",\r\n        \"description4\": \"We view clients as partners, building trust through open communication and collaboration.\",\r\n        \"description5\": \"Committed to community impact, we believe our success is linked to the well-being of those we serve.\",\r\n        \"provider_ids\": [],\r\n        \"banner_status\": true,\r\n        \"provider_title\": \"Expert provider by rating\",\r\n        \"provider_status\": true,\r\n        \"left_bg_image_url\": \"/frontend/images/categories/electrician/7.jpg\",\r\n        \"testimonial_title\": \"What our user say about us.\",\r\n        \"right_bg_image_url\": \"/frontend/images/categories/painter/6.jpg\",\r\n        \"testimonial_status\": true\r\n    },\r\n    \"contact_us\": {\r\n        \"email\": \"4nvg3@navalcadets.com\",\r\n        \"title\": \"Get In Touch\",\r\n        \"contact\": \"0123456789\",\r\n        \"location\": \"4 Askern Rd, Doncaster, South Yorkshire, United Kingdom.\",\r\n        \"description\": \"We improve and grow because of your ideas, queries, and criticism. We are available to listen, whether you have a recommendation, are having a problem, or simply want to talk about your experience.Use the form below or any of the other available contact options to get in touch with us.\",\r\n        \"header_title\": \"Contact Us\",\r\n        \"google_map_embed_url\": \"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d303910.6327655508!2d-1.6735875209114677!3d53.48093683253613!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4878e2a8b277ed2f%3A0x3a10679c640c8f99!2sSouth%20Yorkshire%2C%20UK!5e0!3m2!1sen!2sin!4v1720436526214!5m2!1sen!2sin\"\r\n    },\r\n    \"pagination\": {\r\n        \"blog_per_page\": 10,\r\n        \"service_per_page\": 10,\r\n        \"provider_per_page\": 10,\r\n        \"categories_per_page\": 10,\r\n        \"service_list_per_page\": 10,\r\n        \"provider_list_per_page\": 10,\r\n        \"service_package_per_page\": 10\r\n    },\r\n    \"authentication\": {\r\n        \"title\": \"Welcome to Fixit\",\r\n        \"auth_images\": \"/frontend/images/auth/girl.png\",\r\n        \"description\": \"Simply touch and pick to have all of your products and services delivered to your door.\",\r\n        \"header_logo\": \"/frontend/images/logo/dark-logo.png\",\r\n        \"app_store_url\": \"https: //www.apple.com/in/app-store/\",\r\n        \"google_play_store_url\": \"https: //play.google.com/store/apps/\"\r\n    }\r\n}', NULL, NULL);


UPDATE `currencies` SET `symbol` = '₹' WHERE `currencies`.`id` = 2;
UPDATE `currencies` SET `symbol` = '£' WHERE `currencies`.`id` = 3;
UPDATE `currencies` SET `symbol` = '€' WHERE `currencies`.`id` = 4;


UPDATE `users` SET `slug` = 'thomas-tayloar' WHERE `users`.`id` = 2;
UPDATE `users` SET `slug` = 'robert-davis' WHERE `users`.`id` = 3;
UPDATE `users` SET `slug` = 'michael-smith' WHERE `users`.`id` = 4;
UPDATE `users` SET `slug` = 'alice-brown' WHERE `users`.`id` = 8;
UPDATE `users` SET `slug` = 'jane-smith' WHERE `users`.`id` = 6;
UPDATE `users` SET `slug` = 'charlie-davis' WHERE `users`.`id` = 9;
UPDATE `users` SET `slug` = 'michael-scott' WHERE `users`.`id` = 11;
UPDATE `users` SET `slug` = 'emily-clark' WHERE `users`.`id` = 10;
UPDATE `users` SET `slug` = 'olivia-martinez' WHERE `users`.`id` = 12;
UPDATE `users` SET `slug` = 'lucas-walker' WHERE `users`.`id` = 13;
UPDATE `users` SET `slug` = 'lsabella-green' WHERE `users`.`id` = 14;
UPDATE `users` SET `slug` = 'wiliam-lee' WHERE `users`.`id` = 15;
UPDATE `users` SET `slug` = 'sophia-kim' WHERE `users`.`id` = 16;
UPDATE `users` SET `slug` = 'mia-rossi' WHERE `users`.`id` = 17;
UPDATE `users` SET `slug` = 'jack-wilson' WHERE `users`.`id` = 18;
UPDATE `users` SET `slug` = 'ava-murphy' WHERE `users`.`id` = 19;
UPDATE `users` SET `slug` = 'sarah-johnson' WHERE `users`.`id` = 20;
UPDATE `users` SET `slug` = 'john-smith' WHERE `users`.`id` = 21;
UPDATE `users` SET `slug` = 'carlos-ramirez' WHERE `users`.`id` = 22;
UPDATE `users` SET `slug` = 'emma-thompson' WHERE `users`.`id` = 23;
UPDATE `users` SET `slug` = 'priya-kapoor' WHERE `users`.`id` = 24;
UPDATE `users` SET `slug` = 'juan-garcia' WHERE `users`.`id` = 25;
UPDATE `users` SET `slug` = 'maria-silva' WHERE `users`.`id` = 26;
UPDATE `users` SET `slug` = 'hans-schmidt' WHERE `users`.`id` = 27;
UPDATE `users` SET `slug` = 'mei-ling' WHERE `users`.`id` = 28;
UPDATE `users` SET `slug` = 'ahmed-hassan' WHERE `users`.`id` = 29;
UPDATE `users` SET `slug` = 'emily-turner' WHERE `users`.`id` = 30;
UPDATE `users` SET `slug` = 'maria-garcia' WHERE `users`.`id` = 34;
UPDATE `users` SET `slug` = 'john-doe' WHERE `users`.`id` = 31;
UPDATE `users` SET `slug` = 'emily-johnson' WHERE `users`.`id` = 32;
UPDATE `users` SET `slug` = 'mark-wilson' WHERE `users`.`id` = 33;
UPDATE `users` SET `slug` = 'alice-smith' WHERE `users`.`id` = 35;
UPDATE `users` SET `slug` = 'bob-johnson' WHERE `users`.`id` = 36;
UPDATE `users` SET `slug` = 'elena-rodriguez' WHERE `users`.`id` = 37;
UPDATE `users` SET `slug` = 'david-brown' WHERE `users`.`id` = 38;
UPDATE `users` SET `slug` = 'sophia-lee' WHERE `users`.`id` = 39;
UPDATE `users` SET `slug` = 'mohammed-patel' WHERE `users`.`id` = 40;


UPDATE `services` SET `content` = '<p>Our Window Air Conditioner Repair service ensures your AC runs efficiently, keeping your space cool and comfortable. If your unit isn’t cooling properly, making strange noises, or having other issues, our skilled technicians are here to diagnose and fix the problem quickly.</p> <p>We offer a full range of services, including diagnosing refrigerant leaks, cleaning or replacing filters, and refilling coolant. If key components like the compressor or fan are damaged, we’ll repair or replace them to restore your AC’s performance.</p> <p>In addition to repairs, we provide routine maintenance to prevent breakdowns and extend your unit’s lifespan. This includes cleaning coils, checking seals, and ensuring all parts are functioning properly, so your AC continues to run smoothly year after year.</p> <p>Choose us for fast, reliable, and affordable air conditioner repair. Our experienced team will get your unit back in top condition, ensuring you stay cool and comfortable. Contact us today to schedule your appointment!</p>' WHERE `services`.`id` = 3;

UPDATE `services` SET `content` = '<p>Our Office Cleaning Service is designed to maintain a clean, professional, and productive workspace for both your employees and clients. We understand that a clean office environment is crucial for productivity, employee health, and making a positive impression on visitors. Whether it\'s your workstations, common areas, or restrooms, our team ensures every part of your office is cleaned to the highest standards.</p> <p>We offer a comprehensive range of cleaning services, including desk and surface cleaning, floor care, restroom sanitation, and window washing. Our team will thoroughly clean and disinfect your office space, from removing dust and grime on desks and chairs to sanitizing high-touch surfaces in restrooms. We also handle trash removal and recycling to keep your office eco-friendly and organized.</p> <p>Our team is trained in professional office cleaning techniques, using eco-friendly products that are safe for both your employees and the environment. We offer flexible scheduling options to suit your office’s needs, whether you require daily, weekly, or monthly cleaning services. Our goal is to provide a clean and welcoming environment that promotes a positive and productive atmosphere.</p> <p>Choose our Office Cleaning Service for reliable, high-quality cleaning that enhances your office’s appearance and hygiene. Contact us today to discuss your cleaning needs and schedule your first visit. We’re here to help create a cleaner, healthier workplace for your team.</p>' WHERE `services`.`id` = 4;

UPDATE `services` SET `content` = '<p>Our Haircut and Styling Service is designed to help you achieve your ideal look, whether you\'re preparing for a special occasion or just want a fresh style. Our expert stylists use advanced cutting and styling techniques to ensure you leave the salon looking and feeling your best.</p> <p>We offer a wide range of services including professional haircuts, from trendy pixie cuts to sleek long layers, as well as expert styling advice to help you maintain your new look at home. For added nourishment and shine, we also provide hair treatments like deep conditioning and keratin treatments. After your haircut, our blow-dry styling gives your hair a polished, salon-quality finish.</p> <p>Our team of experienced stylists is passionate about creating personalized haircuts tailored to your unique preferences. We use only top-quality hair care products to keep your hair healthy and vibrant, ensuring that your style looks great and lasts. Whether you know exactly what you want or need guidance, we’re here to bring your vision to life.</p> <p>Book your appointment today and let our experts help you achieve the hairstyle that perfectly suits your personality. We’re dedicated to giving you the best hair experience possible, leaving you with a look that’s both flattering and easy to maintain.</p>' WHERE `services`.`id` = 61;

UPDATE `services` SET `content` = '<p>Prevent slow drains, clogs, and water backups with our professional Drain Cleaning Service. Our experienced plumbers use advanced tools and techniques to clear blocked drains, restore proper water flow, and prevent future clogs. With our thorough service, you can enjoy a hassle-free plumbing system that works efficiently year-round.</p> <p>Our service begins with a detailed inspection of your drains to identify any blockages or buildup. We then use specialized equipment like drain snakes and hydro-jetting to remove debris and clear the pipes. After cleaning, we perform a post-cleaning check to ensure your drains are fully clear and functioning properly, so you won’t have to worry about recurring issues.</p> <p>Choosing us means you\'re not only getting a quick fix, but you\'re also helping to prevent future plumbing problems. Our eco-friendly methods are designed to be both safe and effective, ensuring that your drains remain clear while protecting the environment. We also provide helpful tips on how to maintain clean drains and avoid future blockages, saving you time and money in the long run.</p> <p>With our Drain Cleaning Service, you can rest assured that your plumbing will be in top condition. Contact us today to schedule an appointment and experience the peace of mind that comes with having clean, well-maintained drains.</p>' WHERE `services`.`id` = 59;

UPDATE `services` SET `content` = '<p>Our Custom Furniture Making service specializes in creating high-quality, handcrafted furniture tailored to your exact specifications. Whether you\'re looking to complete a room or need custom-built pieces for a specific space, our expert craftsmen are here to bring your vision to life with precision and attention to detail.</p> <p>We work closely with you to design furniture that perfectly fits your space and reflects your personal style. Using only the finest materials, including wood, metals, and upholstery, we create durable and functional pieces that enhance any room. From sofas and beds to dining tables and bookshelves, we combine aesthetics with practicality to craft furniture that’s both beautiful and functional.</p> <p>Our skilled artisans ensure each piece is meticulously handcrafted to the highest standards. We offer a wide range of customization options, including design, size, finishes, and colors, allowing you to create furniture that meets your exact needs. With our personalized service, we guide you through every step of the process, ensuring your vision is realized with care and expertise.</p> <p>Choose our Custom Furniture Making service for stylish, durable furniture that’s built to last. Contact us today to start designing the perfect piece for your home or office space, and transform your environment with a unique, handcrafted creation.</p>' WHERE `services`.`id` = 5;

UPDATE `services` SET `content` = '<p>Our Electrical Wiring and Installation service ensures that your electrical systems are installed safely, efficiently, and in compliance with all safety standards and regulations. Whether you\'re building a new home, remodeling, or upgrading your current electrical setup, our certified electricians provide reliable, high-quality services to meet your needs.</p> <p>We offer complete electrical wiring installations for new constructions and remodels, as well as upgrades and replacements for outdated systems like wiring, fuse boxes, and electrical panels. Additionally, we specialize in lighting installations, from recessed and chandeliers to outdoor lighting, and can upgrade power outlets and switches for improved safety and convenience throughout your home or office.</p> <p>Our team also provides thorough electrical inspections to ensure your systems are safe, properly grounded, and free of hazards. We take pride in adhering to the latest safety standards, ensuring all installations meet the highest level of quality and safety compliance.</p> <p>Choose our certified electricians for your electrical wiring and installation needs. We offer timely, efficient service with minimal disruption to your home or business. Contact us today to schedule an assessment and begin your electrical project with confidence!</p>' WHERE `services`.`id` = 6;

UPDATE `services` SET `content` = '<p>Our Interior House Painting service is the perfect way to refresh and transform your home with a new coat of paint. Whether you\'re updating a single room or giving your entire house a makeover, our skilled painters deliver high-quality, precise results that enhance the beauty of your living spaces.</p> <p>We offer color consultations to help you choose the perfect palette that complements your style and décor. Our team also handles all aspects of wall preparation, from filling cracks and sanding surfaces to priming for a flawless finish. We use non-toxic, eco-friendly paints that are safe for your family and the environment, ensuring a clean, healthy atmosphere in your home.</p> <p>With years of experience, our professional painters apply a smooth and even coat, delivering a detailed, polished look that lasts. We pride ourselves on efficient service, minimizing disruption to your daily routine while completing your project on time. The result is a durable, long-lasting finish that enhances the aesthetics and value of your home.</p> <p>Give your home a fresh, vibrant look with our Interior House Painting service. Contact us today to schedule a consultation and start your home makeover!</p>' WHERE `services`.`id` = 7;

UPDATE `services` SET `content` = '<p>Our Stress Relief Therapy is designed to help you relax, de-stress, and rejuvenate. Through a combination of personalized techniques, we work to reduce tension, improve your well-being, and leave you feeling calm, centered, and revitalized. Whether you\'re dealing with physical stress or emotional strain, our therapy is tailored to meet your needs.</p> <p>We offer a variety of services including relaxation massages, aromatherapy, mindfulness techniques, and Reiki healing. Our therapists use gentle strokes and pressure points to alleviate tension, while essential oils and guided relaxation enhance your mood and calm your mind. Reiki energy healing also helps clear blockages and restore balance, promoting deep relaxation and overall well-being.</p> <p>Our experienced, certified therapists specialize in stress management techniques that address both the physical and emotional aspects of stress. We create a peaceful, serene environment where you can fully unwind and experience the full benefits of our holistic therapies.</p> <p>Relieve stress and restore balance with our Stress Relief Therapy. Contact us today to schedule your appointment and experience tranquility like never before!</p>' WHERE `services`.`id` = 8;

UPDATE `services` SET `content` = '<p>Our Pain Relief Therapy is designed to offer effective relief from chronic pain, muscle tension, and injuries. By using a combination of therapeutic techniques, we address the root causes of your pain to promote long-lasting recovery and comfort.</p> <p>We provide deep tissue massages to target muscle stiffness, trigger point therapy to release tight areas, and heat and cold therapy to reduce inflammation and muscle spasms. Additionally, we incorporate stretching and mobility exercises to improve flexibility and reduce tension in muscles and joints.</p> <p>Our licensed therapists specialize in pain management and create personalized treatment plans tailored to your specific needs. We take a holistic approach, addressing both the symptoms and the underlying causes of pain, ensuring you receive lasting relief and comfort.</p> <p>Start your journey toward a pain-free life with our Pain Relief Therapy. Contact us today to schedule your session and experience the benefits of personalized, effective pain management.</p>' WHERE `services`.`id` = 9;

UPDATE `services` SET `content` = '<p>Our Natural Skincare Treatment offers a holistic approach to skincare, using only organic, plant-based ingredients to nourish and rejuvenate your skin. Designed to address concerns like dryness, acne, and signs of aging, this treatment leaves your skin feeling radiant, hydrated, and healthy.</p> <p>We offer a range of treatments, including organic face masks made from natural ingredients like honey and aloe vera, gentle exfoliation to promote a glowing complexion, and deep hydration with organic oils and creams. Our anti-aging care stimulates collagen production to reduce fine lines and wrinkles, leaving your skin smooth and youthful.</p> <p>We use 100% natural products, ensuring a safe and effective skincare experience. Each treatment is customizable to meet your unique needs, whether you\'re seeking hydration, acne relief, or anti-aging benefits. Our relaxing facials provide a rejuvenating experience that improves the health of your skin while helping you unwind.</p> <p>Give your skin the care it deserves with our Natural Skincare Treatment. Book an appointment today and enjoy the benefits of nature’s touch!</p>' WHERE `services`.`id` = 10;

UPDATE `services` SET `content` = '<p>Our Sofa & Carpet Deep Cleaning service restores your upholstery and carpets to their original cleanliness by removing dirt, stains, and allergens. Using advanced techniques and eco-friendly cleaning solutions, we ensure your furniture and carpets are thoroughly sanitized, fresh, and revitalized.</p> <p>We specialize in deep stain removal, treating stubborn spots on sofas and carpets with specialized cleaning agents. Our process also eliminates allergens like dust mites and pet dander, while deodorizing treatments remove unpleasant odors. Additionally, we restore the softness and vibrancy of fabrics, making them look and feel like new.</p> <p>Our team uses professional equipment and environmentally friendly cleaning products to deliver the best results. With experienced technicians trained to handle a variety of fabrics, we ensure careful, effective cleaning. We pride ourselves on providing quick and efficient services with minimal disruption to your routine.</p> <p>Refresh your home and restore your furniture with our Sofa & Carpet Deep Cleaning service. Contact us today to schedule your appointment!</p>' WHERE `services`.`id` = 11;

UPDATE `services` SET `content` = '<p>Our Bathroom & Kitchen Deep Cleaning service ensures that your most-used spaces are spotless, hygienic, and fresh. We provide a thorough cleaning for every surface, appliance, and fixture, so your kitchen and bathroom are always ready for use and free from dirt, grime, and germs.</p> <p>Our service includes deep cleaning of all surfaces, including countertops, sinks, and backsplashes, as well as sanitizing toilets, showers, and bathtubs. We also clean kitchen appliances like stoves, fridges, and ovens, and restore the look of your tiles and grout with specialized tools and techniques.</p> <p>We take pride in our attention to detail, ensuring every corner of your kitchen and bathroom is thoroughly cleaned. Our experienced cleaners use only safe, eco-friendly products, so you can enjoy a healthier home and environment while getting top-quality results.</p> <p>For a sparkling clean kitchen and bathroom, trust our Bathroom & Kitchen Deep Cleaning service. Contact us today to schedule your cleaning appointment!</p>' WHERE `services`.`id` = 12;


INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES ('5', '4'), ('6', '4'), ('7', '4');