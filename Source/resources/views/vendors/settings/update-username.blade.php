<x-lw.modal id="lwUpdateUSername" :header="__tr('Update Username')" :hasForm="true">
    <!--  Edit Contact Form -->
    <x-lw.form id="lwUpdateUSernameForm" :action="route('vendor.whatsapp.business_username.write')"
        :data-callback-params="['modalId' => '#lwUpdateUSername']" data-callback="appFuncs.modelSuccessCallback">
        <!-- form body -->
        <div id="lwUpdateUSernameBody" class="lw-form-modal-body"></div>
        <script type="text/template" id="lwUpdateUSernameBody-template">
            
            <fieldset>
                <legend for="">{{  __tr('Status') }}</legend>
                <h3>
                    {{ __tr('Current Status') }}: <%= __tData.currentUsernames?.status || 'N/A' %>
                </h3>

                <% if(!_.isEmpty(__tData.reservedUserNames)) { %>
                    <hr class="my-2">
                    <h3>{{ __tr('Reserved Usernames') }}</h3>
                    <%- __tData.reservedUserNames.join(', ') %>
                <% } %>
            </fieldset>

            <input type="hidden" name="phoneNumberId" value="<%- __tData.phoneNumberId %>" />
            <!-- form fields -->
            <x-lw.input-field type="text" id="lwUsernameField" data-form-group-class="" :label="__tr('Username')" value="<%- __tData.currentUsernames?.username %>" name="username" required/>

            <a href="https://developers.facebook.com/documentation/business-messaging/whatsapp/business-scoped-user-ids/#business-usernames"target="_blank" class="float-right">{!! __tr('Username formatting Help') !!}<i class="fas fa-external-link-alt"></i></a>
        </script>
        <!-- form footer -->
        <div class="modal-footer">
            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">{{ __tr('Update') }}</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __tr('Close') }}</button>
        </div>
    </x-lw.form>
    <!--/  Edit Contact Form -->
</x-lw.modal>