 <!-- Request Location Modal -->
<x-lw.modal id="lwRequestLocationModal" :header="__tr('Request Location')" :hasForm="true"
    data-pre-callback="clearModelContainer">

    <div id="lwRequestLocationModalBody" class="lw-form-modal-body"></div>
    <script type="text/template" id="lwRequestLocationModalBody-template">
        <!--  Request Location Form -->
        <x-lw.form id="lwRequestLocationModalForm" :action="route('vendor.location_request.send.process')"
            :data-callback-params="['modalId' => '#lwRequestLocationModal', 'datatableId' => '#lwContactList']"
            data-callback="appFuncs.modelSuccessCallback">
                <!-- form body -->
                <!-- form fields form fields -->
            <input type="hidden" name="contactUid" :value="contact?._uid">
            <div class="modal-body" x-data="{
                message: '',
                maxLength: 1024,
            }">
                <label for="lwLocationRequestText">{{  __tr('Message') }}</label>
                <textarea name="location_request_message" id="lwLocationRequestText" x-model="message" x-ref="message" class="form-control" rows="3" required="true"></textarea>

                <fieldset>
                    <legend>{{  __tr('Message Preview') }}</legend>
                    <div class="lw-whatsapp-template-create-preview">
                        <div class="lw-whatsapp-preview-container">
                            <img class="lw-whatsapp-preview-bg" src="{{ asset('imgs/wa-message-bg.png') }}" alt="">
                            <div class="lw-whatsapp-preview">
                                <div class="card">
                                    <div class="lw-whatsapp-body lw-ws-pre-line" x-html="message"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>            

            <!-- form footer --> 
            <div class="modal-footer">
                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">{{ __tr('Send') }}</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
            </div>
        </x-lw.form>
        <!--/  Request Location Form -->
    </script>
</x-lw.modal>
<!--/ Request Location Modal -->