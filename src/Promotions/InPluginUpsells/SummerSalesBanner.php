<?php

namespace Give\Promotions\InPluginUpsells;

use Give\Framework\Shims\Shim;

/**
 * @unrleased
 */
class SummerSalesBanner extends SaleBanners
{
    /**
     * @unrleased
     */
    public function getBanners(): array
    {
        return [
            [
                'id' => 'bfgt2023',
                'accessibleLabel' => __('Black Friday/Giving Tuesday Sale', 'give'),
                'leadHeader' => __('Make it stellar.', 'give'),
                'leadText' => __('Save 30% on all StellarWP products.', 'give'),
                'contentText' => __(
                    'Purchase any StellarWP product during the sale and get 100% off WP Business Reviews and take 40% off all other brands.',
                    'give'
                ),
                'actionText' => __('Shop Now', 'give'),
                'alternateActionText' => __('View all StellarWP Deals', 'give'),
                'actionURL' => 'https://go.givewp.com/ss23give',
                'alternateActionURL' => 'https://go.givewp.com/ss23stellar',
                'startDate' => '2023-07-16 00:00',
                'endDate' => '2023-07-19 23:59',
            ],
            [
                'id' => 'bfgt2023',
                'accessibleLabel' => __('Black Friday/Giving Tuesday Sale', 'give'),
                'leadHeader' => __('Make it yours.', 'give'),
                'leadText' => __('Save 30% on all GiveWP Pricing Plans.', 'give'),
                'contentText' => __(
                    'Purchase any StellarWP product during the sale and get 100% off WP Business Reviews and take 40% off all other brands.',
                    'give'
                ),
                'actionText' => __('Shop Now', 'give'),
                'alternateActionText' => __('View all StellarWP Deals', 'give'),
                'actionURL' => 'https://go.givewp.com/ss23give',
                'alternateActionURL' => 'https://go.givewp.com/ss23stellar',
                'startDate' => '2023-07-16 00:00',
                'endDate' => '2023-07-19 23:59',
            ],
        ];
    }

    /**
     * @unrleased
     */
    public function loadScripts(): void
    {
        wp_enqueue_script(
            'give-in-plugin-upsells-sale-banners',
            GIVE_PLUGIN_URL . 'assets/dist/js/admin-upsell-sale-banner.js',
            [],
            GIVE_VERSION,
            true
        );

        wp_localize_script(
            'give-in-plugin-upsells-sale-banners',
            'GiveSaleBanners',
            [
                'apiRoot' => esc_url_raw(rest_url('give-api/v2/sale-banner')),
                'apiNonce' => wp_create_nonce('wp_rest'),
            ]
        );

        wp_enqueue_style(
            'give-in-plugin-upsells-summer-sales-banner',
            GIVE_PLUGIN_URL . 'assets/dist/css/admin-summer-sales-banner.css',
            '1.0.0',
        );

        wp_enqueue_style('givewp-admin-fonts');
    }

    /**
     * @unreleased
     */
    public function render(): void
    {
        $banners = $this->getVisibleBanners();

        if ( ! empty($banners)) {
            include __DIR__ . '/resources/views/summer-sales-banner.php';
        }
    }


    /**
     * @unreleased
     */
    public static function isShowing(): bool
    {
        global $pagenow;

        return $pagenow === 'plugins.php';
    }
}
