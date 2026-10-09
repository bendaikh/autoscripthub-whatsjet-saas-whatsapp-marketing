<!-- Refund Modal -->
<x-lw.modal id="lwPaymentRefundModal" :header="__tr('Refund')" :hasForm="true">    
        <x-lw.form id="lwAddNewUserForm" :action="route('central.transaction.write.process_refund')"
            :data-callback-params="['modalId' => '#lwPaymentRefundModal', 'datatableId' => '#lwTransactionsList']"
            data-callback="appFuncs.modelSuccessCallback">

            <div id="lwPaymentRefundBody" class="lw-form-modal-body"></div>
            <script type="text/template" id="lwPaymentRefundBody-template">

                <input type="hidden" name="transaction_id" value="<%- __tData.transactionDetails.transaction._id %>">

                <ol class="list-group list-group-numbered">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                        <div class="fw-bold">{{ __tr('Vendor Title') }}</div>
                        </div>
                        <span class="text-bg-primary">
                            <%- __tData.transactionDetails?.transaction?.vendor_title %>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                        <div class="fw-bold">{{ __tr('Plan Title') }}</div>
                        </div>
                        <span class="text-bg-primary">
                            <%- __tData.transactionDetails?.planTitle %>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                        <div class="fw-bold">{{ __tr('Plan Frequency') }}</div>
                        </div>
                        <span class="text-bg-primary">
                            <%- __tData.transactionDetails?.planFrequency %>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                        <div class="fw-bold">{{ __tr('Total Amount') }}</div>
                        </div>
                        <span class="text-bg-primary">
                            <%- __tData.transactionDetails?.formattedAmount %>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                        <div class="fw-bold">{{ __tr('Expire On') }}</div>
                        </div>
                        <span class="text-bg-primary">
                            <%- __tData.transactionDetails?.expiryDate %>
                        </span>
                    </li>
                </ol>

                <x-lw.input-field type="text"
                    inputmode="decimal"
                    id="lwRefundAmount"
                    name="refund_amount" :label="__tr('Amount')"
                    min="0" placeholder="{{ __tr('Enter Refund Amount') }}"
                    required="true" max="<%= __tData.refundAmount %>" 
                    value="<%= __tData.refundAmount %>">
                    <x-slot name="prependText">
                        {{ getCurrencySymbol() }}
                    </x-slot>
                    <x-slot name="appendText">
                        {{ getCurrency() }}
                    </x-slot>
                </x-lw.input-field>

                <x-lw.input-field type="text" id="lwReferenceId" data-form-group-class="" :label="__tr('Reference Id')" name="reference_id" required="true" />

                <div class="form-group">
                    <label for="lwNotesField"><?= __tr('Notes') ?></label>
                    <textarea cols="10" rows="3" id="lwNotesField"  class="lw-form-field form-control" placeholder="<?= __tr('Notes') ?>" name="notes"></textarea>
                </div>

            </script>
            <div class="modal-footer">
                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">{{ __tr('Submit') }}</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
            </div>
            
        </x-lw.form>
    
</x-lw.modal>
<!-- /Refund Modal -->