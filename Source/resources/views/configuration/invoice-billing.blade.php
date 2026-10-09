@php
    $isExtendedLicence = (getAppSettings('product_registration', 'licence') === 'dee257a8c3a2656b7d7fbe9a91dd8c7c41d90dc9');
@endphp
<!-- Page Heading -->
<h1><?= __tr('Invoice & Billing Settings') ?></h1>
<!-- /Page Heading -->
<hr>

@if($isExtendedLicence)
    <!-- Invoice & Billing Settings Form -->
    <form class="lw-ajax-form lw-form" method="post" action="<?= route('manage.configuration.write', ['pageType' => request()->pageType]) ?>">
        <div class="row">
            <div class="col-lg-12">
                <!-- Allow Vendor to Download Invoice -->
                <div class="form-group">
                    <x-lw.checkbox :offValue="0" id="lwAllowVendorToDownloadInvoice" name="allow_vendor_to_download_invoice" data-lw-plugin="lwSwitchery" :checked="getAppSettings('allow_vendor_to_download_invoice')" :label="__tr('Allow Vendor to Download Invoice')" value="1" />
                </div>

                <!-- Tax Label -->
                <div class="form-group">
                    <label for="lwTaxLabel"><?= __tr('Tax Label') ?></label>
                    <input type="text" class="form-control form-control-user" name="tax_label" id="lwTaxLabel" value="<?= getAppSettings('tax_label') ?>" placeholder="<?= __tr('GST, VAT, Sales Tax, etc.') ?>">
                </div>

                <!-- Tax Rate -->
                <div class="form-group">
                    <label for="lwTaxRate"><?= __tr('Tax Rate') ?></label>
                    <input type="number" step="any" min="0" class="form-control form-control-user" name="tax_rate" id="lwTaxRate" value="<?= getAppSettings('tax_rate') ?>" placeholder="<?= __tr('e.g. 5, 12, 18, 28') ?>">
                </div>

                <!-- Additional Info -->
                <div class="form-group">
                    <label for="lwInvoiceAdditionalInfo"><?= __tr('Additional Info') ?></label>
                    <textarea name="invoice_additional_info" id="lwInvoiceAdditionalInfo" class="form-control" rows="4" placeholder="<?= __tr('Company registration, payment instructions, terms, legal disclaimers, etc.') ?>"><?= getAppSettings('invoice_additional_info') ?></textarea>
                </div>

                <!-- Invoice Logo -->
                <div class="form-group mt-4">
                    <label for="lwUploadInvoiceLogo"><?= __tr('Invoice Logo') ?></label>
                    <div class="alert alert-default">
                        {{  __tr('Upload will be processed automatically on valid selection.') }}
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <input type="file" data-lw-plugin="lwUploader" data-label-idle="{{ __tr('Select New Invoice Logo') }}" data-allow-revert="true" data-instant-upload="true" data-action="<?= route('media.upload_invoice_logo') ?>" id="lwUploadInvoiceLogo" data-callback="afterUploadedInvoiceLogo" data-default-image-url="<?= getAppSettings('invoice_logo_image_url') ?>">
                        </div>
                    </div>
                </div>

                <!-- Brand Name -->
                <div class="form-group mt-3">
                    <label for="lwInvoiceBrandName"><?= __tr('Brand Name') ?></label>
                    <input type="text" class="form-control form-control-user" name="invoice_brand_name" id="lwInvoiceBrandName" value="<?= getAppSettings('invoice_brand_name') ?>" placeholder="<?= __tr('e.g. Acme Corp') ?>">
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="lwInvoiceAddress"><?= __tr('Address') ?></label>
                    <textarea name="invoice_address" id="lwInvoiceAddress" class="form-control" rows="3" placeholder="<?= __tr('Full billing address') ?>"><?= getAppSettings('invoice_address') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Update Button -->
        <div class="mt-4">
            <a href class="lw-ajax-form-submit-action btn btn-primary btn-user lw-btn-block-mobile">
                <?= __tr('Save') ?>
            </a>
        </div>
    </form>
    <!-- /Invoice & Billing Settings Form -->
@else
    <div class="alert alert-warning my-3">
        <strong title="Extended Licence Required"><?= __tr('Extended Licence Required') ?></strong> <br>
        <?= __tr('Extended licence is required to access Billing & Invoice Settings.') ?>
    </div>
@endif

@push('appScripts')
<script>
    (function($) {
        'use strict';
        // After invoice logo file successfully uploaded then this function is called
        window.afterUploadedInvoiceLogo = function (responseData) {
            // Handled automatically by lwUploader preview
        }
    })(jQuery);
</script>
@endpush
