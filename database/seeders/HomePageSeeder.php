<?php

namespace Database\Seeders;

use App\Models\HomePage;
use Illuminate\Database\Seeder;

class HomePageSeeder extends Seeder
{
    protected $baseURL;
    protected $baseName;

    public function __construct()
    {
        $this->baseURL = config('app.url');
        $this->baseName = config('app.name');
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $homePage = [
            'content' => [
                'home_banner' => [
                    'status' => true,
                    'title' => 'One-Stop Solution For Your ',
                    'animate_text' => 'home services',
                    'description' => "We connect you with trusted servicemen for all your home and business needs! 🏠💼 From repairs to installations, we’ve got you covered. 🔧✅ Easy booking, clear pricing, and stress-free service! 😊.",
                    'search_enable' => true,
                    'service_ids' => [],
                ],
                'categories_icon_list' => [
                    'title' => 'Top Categories',
                    'status' => true,
                    'category_ids' => []
                ],
                'value_banners' => [
                    'title' => 'Best Valuable Deals',
                    'status' => true,
                    'banners' => [
                        [
                            'title' => 'Electrical service',
                            'description' =>  'If you want to have stunning look of your house.',
                            'image_url' => '/frontend/images/offer/1.png',
                            'sale_tag' => 'Sale 40%',
                            'button_text' => 'Book Now',
                            'redirect_type' => 'service-page',
                            'status' => true,
                        ],
                        [
                            'title' => 'Furniture service',
                            'description' => 'If you want to have stunning look of your house.',
                            'button_text' => 'Book Now',
                            'image_url' => '/frontend/images/offer/2.png',
                            'status' => true,
                            'redirect_type' => 'category-page',
                            'sale_tag' => 'Sale 50%',
                        ],
                        [
                            'title' => 'Ac cleaning service',
                            'description' => 'If you want to have stunning look of your house.',
                            'button_text' => 'Book Now',
                            'image_url' => '/frontend/images/offer/3.png',
                            'status' => true,
                            'redirect_type' => 'service-package-page',
                            'sale_tag' => 'Sale 60%',
                        ]
                    ],
                ],
                'service_list_1' => [
                    'title' => 'Featured Services',
                    'service_ids' => [],
                    'status' => true
                ],
                'download' => [
                    'status' => true,
                    'image_url' => '/frontend/images/gif/app-gif.gif',
                    'title' =>  $this->baseName . 'Customer, Provider, Servicemen & Admin application for iOS & Android',
                    'description' => 'Buyers can discover local services in a click! through our Google Map integration which enhances top level buyer experiences using their GPS locations',
                    'points' => [
                        'Buyers can discover local services in a click.',
                        'Buyers can discover local.',
                        'Buyers can discover local services.'
                    ],
                ],
                'providers_list' => [
                    'status' => true,
                    'title' => 'Expert provider by rating',
                    'provider_ids' => []
                ],
                'special_offers_section' => [
                    'banner_section_title' => 'Today special offers',
                    'service_section_title' => 'Today special offers'
                ],
                'service_packages_list' => [
                    'status' => true,
                    'title' => 'Service Packages',
                    'service_packages_ids' => []
                ],
                'blogs_list' => [
                    'title' => 'Latest blog',
                    'description' => '',
                    'status' => true,
                    'blog_ids' => []
                ],
                'custom_job' => [
                    'status' => true,
                    'image_url' => '/frontend/images/job-request-img.png',
                    'title' => 'Can\'t Find the Right Service? Post a Custom Job Request!',
                    'button_text' => '+ Post New Job Request'
                ],
                'become_a_provider' => [
                    'status' => true,
                    'image_url' => '/frontend/images/girl.png',
                    'float_image_1_url' => '/frontend/images/chart.png',
                    'float_image_2_url' => '/frontend/images/avatars.png',
                    'title' => 'Earn more and deliver your service to worldwide by become a Service Provider',
                    'description' => 'Buyers can discover local services in a click! through our Google Map integration which.',
                    'points' => [
                        'Buyers can discover local services in a click.',
                        'Buyers can discover local.',
                        'Buyers can discover local services.'
                    ],
                    'button_text' => 'Register now',
                    'button_url' => $this->baseURL.'/backend/become-provider',
                ],
                'testimonial' => [
                    'status' => true,
                    'title' => 'What our user have to say about us ?',
                ],
                'news_letter' => [
                    'status' => true,
                    'title' => 'SUBSCRIBE TO OUR NEWSLETTER',
                    'sub_title' => 'We promise not to spam you.',
                    'button_text' => 'Subscribe Now',
                    'image_url' => '/frontend/images/man.png',
                ],
            ],
            'slug' => 'default',
            'status' => 1
        ];

        if (!HomePage::where('slug', $homePage['slug'])?->first()) {
            HomePage::updateOrCreate([
                'content' =>  $homePage['content'],
                'status' =>  $homePage['status'],
                'slug' => $homePage['slug'],
            ]);
        }
    }
}