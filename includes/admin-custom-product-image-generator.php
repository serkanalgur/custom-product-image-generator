<?php
if (! defined('ABSPATH')) {
    exit;
}

function cpig_render_admin_page()
{
    ?>
<div class="wrap">
    <h1><?php esc_html_e('Custom Product Image Generator', 'cpig'); ?> <small>by Serkan Algur</small></h1>
    <div id="tab-container" class="tab-container">
        <ul class='etabs'>
            <li class='tab'><a href="#tabs1-html">Generator</a></li>
            <li class='tab'><a href="#tabs1-js">Templates</a></li>
        </ul>
        <div class="panel-container">

            <div id="tabs1-html">

                <div class="cpig-admin-container">

                    <div class="cpig-sidebar">
                        <button id="cpig-select-image"
                            class="button button-primary"><?php esc_html_e('Select Base Image', 'cpig'); ?></button>
                        <button id="cpig-add-text"
                            class="button"><?php esc_html_e('Add Text Field', 'cpig'); ?></button>

                        <div id="cpig-text-list" class="cpig-text-list"></div>

                        <div class="cpig-logo-item">
                            <label><?php esc_html_e('Upload Logo 1:', 'cpig'); ?></label>
                            <button id="cpig-add-logo1"
                                class="button"><?php esc_html_e('Add Logo 1', 'cpig'); ?></button>
                        </div>

                        <div class="cpig-logo-item">
                            <label><?php esc_html_e('Upload Logo 2:', 'cpig'); ?></label>
                            <button id="cpig-add-logo2"
                                class="button"><?php esc_html_e('Add Logo 2', 'cpig'); ?></button>
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
            </div>
        </div>
    </div>
</div>
    <?php
}
