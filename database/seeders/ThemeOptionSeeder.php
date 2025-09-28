<?php

namespace Database\Seeders;

use App\Models\ThemeOption;
use Illuminate\Database\Seeder;

class ThemeOptionSeeder extends Seeder
{
    protected $baseName;

    public function __construct()
    {
        $this->baseName = config('app.name');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = $this->getThemeOptions();
        ThemeOption::updateOrCreate(['options' => $options]);
    }

    public function getThemeOptions()
    {
        return [
            'general' => [
                'header_logo' => '/frontend/images/logo/dark-logo.png',
                'favicon_icon' => '/frontend/images/logo/favicon-icon.png',
                'footer_logo' => '/frontend/images/logo/dark-logo.png',
                'site_title' => $this->baseName,
                'site_tagline' => "Your One-Stop Solution for for your home services",
                'breadcrumb_description' => 'Select a service from the below category list that correlates with your needs. It includes 15+ categories with 560+ different services in various sector.',
                'app_store_url' => 'https://www.apple.com/in/app-store/',
                'google_play_store_url' => 'https://play.google.com/store/apps/'
            ],
            'header' => [
                'home' => true,
                'categories' => true,
                'services' => true,
                'booking' => true,
                'blogs' => true,
            ],
            'footer' => [
                'footer_copyright' => '©2024 ' . $this->baseName . ' All rights reserved',
                'useful_link' =>
                [
                    [

                        'slug' => '/',
                        'name' => 'Home',
                    ],
                    [

                        'slug' => 'category',
                        'name' => 'Categories',
                    ],
                    [

                        'slug' => 'service',
                        'name' => 'Services',
                    ],
                    [

                        'slug' => 'providers',
                        'name' => 'Providers',
                    ],
                ],
                'pages' =>
                [
                    [
                        'slug' => 'privacy-policy',
                        'name' => 'Privacy Policy',
                    ],
                    [
                        'slug' => 'terms-conditions',
                        'name' => 'Terms & Conditions',
                    ],
                    [
                        'slug' => 'contact-us',
                        'name' => 'Contact Us',
                    ],
                    [
                        'slug' => 'about-us',
                        'name' => 'About Us',
                    ],
                ],
                'others' => [
                    [
                        'slug' => 'account/profile',
                        'name' => 'My Account',
                    ],
                    [

                        'slug' => 'wishlist',
                        'name' => 'Wishlist',
                    ],
                    [

                        'slug' => 'booking',
                        'name' => 'Bookings',
                    ],
                    [

                        'slug' => 'providers',
                        'name' => 'Providers',
                    ],
                    [

                        'slug' => 'service',
                        'name' => 'Services',
                    ],
                ],
                'become_a_provider' => [
                    'become_a_provider_enable' => true,
                    'description' => 'Earn more and deliver your service to worldwide.',
                ],
            ],
            'contact_us' => [
                'header_title' => 'Contact Us',
                'title' => 'Get In Touch',
                'description' => 'We improve and grow because of your ideas, queries, and criticism. We are available to listen, whether you have a recommendation, are having a problem, or simply want to talk about your experience.Use the form below or any of the other available contact options to get in touch with us.',
                'email' => '4nvg3@navalcadets.com',
                'contact' => '0123456789',
                'location' => '4 Askern Rd, Doncaster, South Yorkshire, United Kingdom.',
                'google_map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d303910.6327655508!2d-1.6735875209114677!3d53.48093683253613!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4878e2a8b277ed2f%3A0x3a10679c640c8f99!2sSouth%20Yorkshire%2C%20UK!5e0!3m2!1sen!2sin!4v1720436526214!5m2!1sen!2sin',
            ],
            'pagination' => [ 
                'provider_per_page' => 12,
                'blog_per_page' => 12,
                'service_per_page' => 12,
                'service_list_per_page' => 12,
                'service_package_per_page' => 12,
                'categories_per_page' => 12,
                'provider_list_per_page' => 12,
            ],
            'about_us' => [
                'status' => true,
                'left_bg_image_url' => '/frontend/images/categories/electrician/7.jpg',
                'right_bg_image_url' => '/frontend/images/categories/painter/6.jpg',
                'title' => 'Our Mission',
                'description' => "At $this->baseName, our mission is to be more than just a service provider—we aim to be a trusted partner in your journey to success. We are committed to:",
                'sub_title1' => 'Delivering Excellence:',
                'description1' => 'We deliver quality with precision, consistently exceeding expectations.',
                'sub_title2' => 'Empowering Our Clients:',
                'description2' => 'We provide tools and insights to empower your success now and in the future.',
                'sub_title3' => 'Fostering Innovation:',
                'description3' => 'We embrace innovation, continuously improving to keep our clients ahead',
                'sub_title4' => 'Building Strong Relationships:',
                'description4' => 'We view clients as partners, building trust through open communication and collaboration.',
                'sub_title5' => 'Contributing to the Community:',
                'description5' => 'Committed to community impact, we believe our success is linked to the well-being of those we serve.',
                'provider_status' => true,
                'provider_title' => 'Expert provider by rating',
                'provider_ids' => [],
                'testimonial_status' => true,
                'testimonial_title' => 'What our user say about us.',
                'banner_status' => true,
                'banners' => [
                    [
                        'title' => 'Years Experience',
                        'count' => '3.5',
                    ],
                    [
                        'title' => 'Positive Reviews',
                        'count' => '520',
                    ],
                    [
                        'title' => 'Trusted Client',
                        'count' => '10000',
                    ],
                    [
                        'title' => 'Team Member',
                        'count' => '60',
                    ],
                ],

            ],
            'seo' => [
                'meta_tags' => $this->baseName . ' Marketplace: Where Vendors Shine Together',
                "meta_title" => "Online Marketplace, Vendor Collaboration, E-commerce Platform",
                "meta_description" => "Discover " . $this->baseName . " Marketplace – a vibrant online platform where vendors unite to showcase their products, creating a diverse shopping experience. Explore a wide range of offerings and connect with sellers on a single platform.",
                "og_title" => $this->baseName . " Marketplace: Uniting Vendors for Shopping Excellence",
                "og_description" => "Experience a unique shopping journey at " . $this->baseName . " Marketplace, where vendors collaborate to provide a vast array of products. Explore, shop, and connect in one convenient destination.",
                'og_image' => null,
            ],
            'authentication' => [
                'header_logo' => '/frontend/images/logo/light-logo.png',
                'auth_images' => '/frontend/images/auth/girl.png',
                'title' => 'Welcome to '.$this->baseName,
                'description' => 'Simply touch and pick to have all of your products and services delivered to your door.',
                'app_store_url' => 'https: //www.apple.com/in/app-store/',
                'google_play_store_url' => 'https: //play.google.com/store/apps/',
            ],
            'privacy_policy'=> [
                'banners' => [
                    
                ],
            ],
            'terms_and_conditions' =>[
                'banners' => [
                    
                ],
            ],
        ];
    }
}   