<?php

/**
 * Custom Product Image Generator Admin Page
 *
 * @package Custom_Product_Image_Generator
 */

// phpcs:disable
if (!defined('ABSPATH')) {
    exit;
}
// phpcs:enable

function cpig_render_admin_page()
{
    ?>
<div class="wrap">
    <h1><?php esc_html_e('Custom Product Image Generator', 'cpig'); ?> <small>by Serkan Algur</small></h1>
    <div id="tab-container" class="tab-container">
        <ul class='etabs'>
            <li class='tab'><a href="#tabs1-html" id="tab1">Generator</a></li>
            <li class='tab'><a href="#tabs1-js" id="tab2">Templates</a></li>
        </ul>
        <div class="panel-container">
            <div id="tabs1-html">
                <div class="cpig-admin-container">
                    <div class="cpig-sidebar">
                        <button id="cpig-select-image"
                            class="button button-primary"><?php esc_html_e('Select Base Image', 'cpig'); ?></button>
                        <input type="hidden" id="cpig-base-image-id" />
                        <button id="cpig-add-text"
                            class="button"><?php esc_html_e('Add Text Field', 'cpig'); ?></button>
                        <div id="cpig-text-list" class="cpig-text-list"></div>
                        <div class="cpig-logo-item">
                            <label><?php esc_html_e('Upload Logo 1:', 'cpig'); ?></label>
                            <button id="cpig-add-logo1"
                                class="button"><?php esc_html_e('Add Logo 1', 'cpig'); ?></button>
                            <input type="hidden" id="cpig-logo1-image-id" />
                            <img src="" id="cpig-logo-1-preview" style="max-width:100px; max-height:100px;"
                                data-idx="" />
                        </div>
                        <div class="cpig-logo-item">
                            <label><?php esc_html_e('Upload Logo 2:', 'cpig'); ?></label>
                            <button id="cpig-add-logo2"
                                class="button"><?php esc_html_e('Add Logo 2', 'cpig'); ?></button>
                            <input type="hidden" id="cpig-logo2-image-id" />
                            <img src="" id="cpig-logo-2-preview" style="max-width:100px; max-height:100px;"
                                data-idx="" />
                        </div>
                        <p>
                            <label><?php esc_html_e('Choose Product:', 'cpig'); ?><br>
                                <select id="cpig-product-select" style="width:100%;"></select>
                            </label>
                        </p>
                        <button id="cpig-generate" class="button button-primary"><span
                                class="dashicons dashicons-update mika dnon"></span>
                            <?php esc_html_e('Generate & Save', 'cpig'); ?></button>
                        <button id="cpig-save-as-template" class="button"><span
                                class="dashicons dashicons-update mika dnon"></span>
                            <?php esc_html_e('Save as Template', 'cpig'); ?></button>
                        <input type="hidden" id="cpig-template-title" />
                        <input type="hidden" id="cpig-template-id" />
                        <input type="hidden" id="cpig-current-action" />
                    </div>
                    <div class="cpig-canvas-wrapper">
                        <canvas id="cpig-canvas"></canvas>
                        <div class="cpig-ruler">
                            <div class="cpig-ruler-h"></div>
                            <div class="cpig-ruler-v"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="tabs1-js">
                <table class="wp-list-table widefat fixed striped table-view-list" id="cpig-templates-list">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'cpig'); ?></th>
                            <th><?php esc_html_e('Name', 'cpig'); ?></th>
                            <th><?php esc_html_e('JSON', 'cpig'); ?></th>
                            <th><?php esc_html_e('Actions', 'cpig'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
}
